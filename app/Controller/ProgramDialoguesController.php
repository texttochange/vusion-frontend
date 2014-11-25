<?php
App::uses('BaseProgramSpecificController','Controller');
App::uses('Dialogue', 'Model');
App::uses('Program', 'Model');
App::uses('Request', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Participant', 'Model');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('DialogueHelper', 'Lib');


class ProgramDialoguesController extends BaseProgramSpecificController
{
    var $uses = array(
        'Dialogue',
        'ProgramSetting',
        'Participant',
        'Request');
    var $components = array(
        'RequestHandler'=> array(
            'viewClassMap' => array(
                'json' => 'View')), 
        'LocalizeUtils', 
        'Utils',
        'Keyword',
        'DynamicForm',
        'ProgramAuth',
        'ArchivedProgram');
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->RequestHandler->accepts('json');
        $this->RequestHandler->addInputType('json', array('json_decode'));
        
        $this->_instanciateVumiRabbitMQ();
    }
    
    
    function constructClasses()
    {
        parent::constructClasses();
    }
    
    
    protected function _instanciateVumiRabbitMQ()
    {
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }
    
    
    public function index()
    {
        $this->set('dialogues', $this->Dialogue->getActiveAndDraft());
    }
    
    
    public function save()
    {
        $programUrl = $this->params['program'];
        $programDb  = $this->Session->read($this->params['program']."_db");
        $requestSuccess = false;
        
        if (!$this->request->is('post') || !$this->_isAjax()) {
            return;
        }
        
        if (!$this->ProgramSetting->hasRequired()) {
            $this->Session->setFlash(__('Please set the program settings then try again.'));
            $this->set(compact('requestSuccess'));
            return;
        }
        
        $shortCode     = $this->ProgramSetting->find('getProgramSetting', array('key' => 'shortcode'));
        $dialogue      = DialogueHelper::objectToArray($this->request->data);
        $id            = Dialogue::getDialogueId($dialogue);
        $keywords      = Dialogue::getDialogueKeywords($dialogue);
        $foundKeywords = $this->Keyword->areUsedKeywords($programDb, $shortCode, $keywords, 'Dialogue', $id);
        if ($this->Dialogue->saveDialogue($dialogue, $foundKeywords)) {
            $requestSuccess = true;
            $this->UserLogMonitor->userLogSessionWrite();
            $this->Session->setFlash(__('Dialogue saved as draft.'));
            $this->set('dialogueObjectId', $this->Dialogue->id);
        } else {
            $this->Session->setFlash(__('This dialogue has a validation error, please correct it and save again.'));
            $this->Dialogue->validationErrors = $this->Utils->fillNonAssociativeArray($this->Dialogue->validationErrors);
        }
        $this->set(compact('requestSuccess'));       
    }
    
    
    public function edit()
    {
        $this->set('conditionalActionOptions', $this->_getConditionalActionOptions());
        $this->set('dynamicOptions', $this->_getDynamicOptions());
        
        $id = $this->params['id'];
        if (!isset($id))
            return;
        $this->Dialogue->id = $id;
        if (!$this->Dialogue->exists()) {
            $this->Session->setFlash(__("Dialogue doesn't exist."), 
                'default', array('class' => "message failure"));
            return;
        }
        
        $dialogue = $this->Dialogue->read(null, $id);
        if ($dialogue['Dialogue']['activated'] == 2) {
            $currentDialogue = $this->Dialogue->getActiveDialogue($dialogue['Dialogue']['dialogue-id']);
            $link = Router::url(array(
                'program'=> $this->params['program'],
                'controller'=>'programDialogues', 
                'action' => 'edit',
                'id' => $currentDialogue['Dialogue']['_id'].''));
            $this->Session->setFlash(
                "<a href='".$link."'>".__("This is an old version of this dialogue. Click here to get the current version.")."</a>", 
                'default',
                array('class' => "message failure"));
        }
        $this->set('dialogue', $dialogue);
    }
    
    
    protected function _getConditionalActionOptions()
    {   
        return $this->LocalizeUtils->localizeLabelInArray(
            $this->Participant->getFilters('conditional-action'));
    }
    
    
    protected function _getDynamicOptions()
    {      
        return array();
    }
    
    
    //TODO need to check keyword before activate!
    public function activate()
    {
        $programUrl = $this->params['program'];
        $dialogueId = $this->params['id'];
        
        if (!$this->ProgramSetting->hasRequired()) {
            $this->Session->setFlash(__('Please set the program settings then try again.'), 
                'default',array('class' => "message failure"));
        } else {
            if ($savedDialogue = $this->Dialogue->makeActive($dialogueId)) {                
                $this->_notifyUpdateBackendWorker($programUrl, $savedDialogue['Dialogue']['dialogue-id']);
                $this->UserLogMonitor->userLogSessionWrite();
                $this->Session->setFlash(__('Dialogue activated.'), 
                    'default', 
                    array('class' => "message success"));
            } else {
                $this->Session->setFlash(__('Dialogue unknown reload the page and try again.'));
            }
        } 
        $this->redirect(array('program' => $programUrl, 'action' => 'index'));
    }
    
    
    public function validateName()
    {
        $dialogueName   = $this->request->data['name'];
        $dialogueId     = $this->request->data['dialogue-id'];
        $requestSuccess = false;
        $foundMessage   = null;
        
        if (!$this->request->is('post') || !$this->_isAjax()) {
            throw new BadRequestException();
        }
        
        if (!$this->Dialogue->isValidDialogueName($dialogueName,  $dialogueId)) {
            $foundMessage = __("'%s' Dialogue Name already exists in the program. Please choose another.", $dialogueName);
        } else {
            $requestSuccess = true;
        } 
        
        $this->set(compact('requestSuccess', 'foundMessage'));        
    }
    
    
    public function validateKeyword()
    {   
        $programUrl   = $this->params['program'];
        $programDb    = $this->Session->read($programUrl."_db");
        $usedKeywords = $this->request->data['keyword'];
        $dialogueId   = $this->request->data['dialogue-id'];
        
        $requestSuccess = false;
        $foundMessage   = null;
        
        if (!$this->request->is('post') || !$this->_isAjax()) {
            return;
        }
        
        if (!$this->ProgramSetting->hasRequired()) {
            $this->Session->setFlash(__('Please set the program settings then try again.'));
            $this->set(compact('requestSuccess'));
            return;
        }
        
        /**Is the keyword used by another program*/
        $shortCode = $this->ProgramSetting->find('getProgramSetting', array('key' => 'shortcode'));
        $foundKeywords = $this->Keyword->areUsedKeywords($programDb, $shortCode, $usedKeywords, 'Dialogue', $dialogueId);
        if ($foundKeywords) {
            $foundMessage = $this->Keyword->foundKeywordsToMessage($programDb, $foundKeywords);
        } else {
            $requestSuccess = true;
        } 
        
        $this->set(compact('requestSuccess', 'foundMessage')); 
    }
    
    //TODO check that the phone number is correct
    public function testSendAllMessages()
    {
        $programUrl = $this->params['program'];
        
        if (isset($this->params['id'])) {
            $objectId = $this->params['id'];
            $this->set(compact('objectId'));
        }
        
        if ($this->request->is('post')) {
            $phoneNumber = $this->request->data['SendAllMessages']['phone-number'];
            $dialogueId  = $this->request->data['SendAllMessages']['dialogue-obj-id'];
            $result      = $this->_notifySendAllMessagesBackendWorker($programUrl, $phoneNumber, $dialogueId);
            $this->Session->setFlash(__('Message(s) being sent, should arrive shortly...'), 
                'default', array('class' => "message success"));
        }
        
        $dialogues = $this->Dialogue->getActiveAndDraft();    
        $this->set(compact('dialogues'));         
    }
    
    
    protected function _notifySendAllMessagesBackendWorker($workerName, $phone, $scriptId)
    {
        return $this->VumiRabbitMQ->sendMessageToSendAllMessages($workerName, $phone, $scriptId);
    }
    
    
    protected function _notifyUpdateBackendWorker($workerName, $dialogueId)
    {
        return $this->VumiRabbitMQ->sendMessageToUpdateSchedule($workerName, 'dialogue', $dialogueId);
    }
    
    
    protected function _notifyUpdateRegisteredKeywords($workerName)
    {
        return $this->VumiRabbitMQ->sendMessageToUpdateRegisteredKeywords($workerName);
    }
    
    
    public function delete()
    {
        $programUrl = $this->params['program'];
        $dialogueId = $this->params['id'];
        
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        
        if ($this->Dialogue->deleteDialogue($dialogueId)) {
            $result = $this->_notifyUpdateRegisteredKeywords($programUrl);
            $this->UserLogMonitor->userLogSessionWrite();
            $this->Session->setFlash(
                __('Dialogue deleted.'),
                'default',
                array('class'=>'message success')
                );
            $this->redirect(array(
                'program' => $programUrl,
                'action' => 'index'
                ));
        }  
        $this->Session->setFlash(
            __('Delete dialogue failed.'));
        $this->redirect(
            array(
                'program' => $programUrl,
                'action' => 'index'
                )
            );
    }
    
    
}
