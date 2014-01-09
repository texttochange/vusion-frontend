<?php
App::uses('AppController', 'Controller');
App::uses('Request', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Dialogue', 'Model');
App::uses('DialogueHelper', 'Helper');
App::uses('Participant', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramRequestsController extends AppController
{
    
    var $components = array(
        'RequestHandler', 
        'LocalizeUtils', 
        'Utils',
        'Keyword');
    var $uses = array('Request');
    
    
    function constructClasses()
    {
        parent::constructClasses();
    }
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
        $this->RequestHandler->accepts('json');
        $this->RequestHandler->addInputType('json', array('json_decode'));
        $options              = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->Request        = new Request($options);
        $this->Dialogue       = new Dialogue($options);
        $this->DialogueHelper = new DialogueHelper();
        $this->ProgramSetting = new ProgramSetting($options);
        $this->Participant    = new Participant($options);
        $this->_instanciateVumiRabbitMQ();
    }
    
    
    protected function _instanciateVumiRabbitMQ(){
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }
    
    
    
    public function index()
    {
        $this->set('requests', $this->paginate());
    }
    
    
    public function add()
    {
        $this->set('conditionalActionOptions', $this->_getConditionalActionOptions());
        
        $programUrl = $this->params['program'];
        $programDb  = $this->Session->read($programUrl."_db");
        
        if ($this->request->is('post')) {
            $shortCode = $this->ProgramSetting->find('getProgramSetting', array('key' => 'shortcode'));
            $keywords = DialogueHelper::getRequestKeywords($this->request->data);
            $usedByOtherProgramResults = $this->Keyword->areKeywordsUsedByOtherPrograms($programDb, $shortCode, $keywords);
            $this->Request->create();
            if ($this->Request->saveRequest($this->request->data, $usedByOtherProgramResults)) {
                $this->_notifyUpdateRegisteredKeywords($programUrl);
                $this->set(
                    'result', array(
                        'status' => 'ok',
                        'request-id' => $this->Request->id,
                        'message' => __('Request created, wait for redirection.'))
                    );
            } else {
                $this->Request->validationErrors = $this->Utils->fillNonAssociativeArray($this->Request->validationErrors);
                $this->set(
                    'result', array(
                        'status' => 'fail',
                        'message' => array('Request' => $this->Request->validationErrors))
                    );
            }
        }
    }
    
    
    public function edit()
    {
        $this->set('conditionalActionOptions', $this->_getConditionalActionOptions());
        
        $programUrl = $this->params['program'];
        $programDb  = $this->Session->read($programUrl."_db");
        $id         = $this->params['id'];
        
        if ($this->request->is('post')) {
            $shortCode = $this->ProgramSetting->find('getProgramSetting', array('key' => 'shortcode'));
            $keywords = DialogueHelper::getRequestKeywords($this->request->data);
            $usedByOtherProgramResults = $this->Keyword->areKeywordsUsedByOtherPrograms($programDb, $shortCode, $keywords);

            $this->Request->create();
            $this->Request->id = $id;
            if ($this->Request->saveRequest($this->request->data, $usedByOtherProgramResults)) {
                $this->_notifyUpdateRegisteredKeywords($programUrl);
                $this->set(
                    'result', array(
                        'status' => 'ok',
                        'request-id' => $this->Request->id,
                        'message' => 'Request saved.')
                    );
            } else {
                $this->Request->validationErrors = $this->Utils->fillNonAssociativeArray($this->Request->validationErrors);
                $this->set(
                    'result', array(
                        'status' => 'fail',
                        'message' => array('Request' => $this->Request->validationErrors)
                        )
                    );
            }
        } else {
            $this->Request->id = $id;
            if (!$this->Request->exists()) {
                throw new NotFoundException(__('Invalide Request') . $id);
            }
            $this->set('request', $this->Request->read(null, $id));
        }
    }
    
    
    protected function _getConditionalActionOptions()
    {   
        return $this->LocalizeUtils->localizeLabelInArray(
            $this->Participant->getFilters('conditional-action'));
    }
    
    
    public function delete()
    {
        $programUrl = $this->params['program']; 
        $id         = $this->params['id']; 
        
        $this->Request->id = $id;
        if (!$this->Request->exists()) {
            throw new NotFoundException(__('Invalid request') . $id);
        }
        if ($this->Request->delete()) {
            $this->_notifyUpdateRegisteredKeywords($programUrl);
            $this->Session->setFlash(
                __('The request has been deleted.'),
                'default',
                array('class'=>'message success')
                );
        } else {
            $this->Session->setFlash(__('The request has not been deleted.'));
        }
        $this->redirect(
            array(
                'program' => $programUrl,
                'action' => 'index'
                )
            );
    }
    
    
    public function validateKeyword()
    {
        $programUrl        = $this->params['program'];
        $programDb         = $this->Session->read($programUrl."_db");
        $keywords          = $this->request->data['keyword'];
        $keywordToValidate = $this->DialogueHelper->getRequestKeywordToValidate($keywords);
        
        if (!$this->ProgramSetting->hasRequired()) {
            $this->set('result', array(
                'status' => 'fail', 
                'message' => __('Please set the program settings then try again.')));
            return;
        }

        /**Is the keyword used by another dialogue of the same program*/
        $dialogueUsingKeyword = $this->Dialogue->getActiveDialogueUseKeyword($keywordToValidate);
        if ($dialogueUsingKeyword) {
            $this->set('result', array(
                    'status'=>'fail', 
                    'message'=> __("'%s' already used in same program in the '%s' dialogue.", $keywordToValidate, $dialogueUsingKeyword['Dialogue']['name'])));
            return;
        }
        
        /**Is the keyword used by request in the same program*/
        if (isset($this->request->data['object-id'])) {
            $conditions = array(    
                'keywords'=> $keywords,
                'excludeRequest' => $this->request->data['object-id']);
        } else {
            $conditions = array('keywords'=> $keywords);
        }
        $foundKeyword = $this->Request->find('keyphrase', $conditions);
        if ($foundKeyword) {
            $this->set('result', array(
                    'status'=>'fail', 
                    'message'=> __("'%s' already used in the same program by a request.", $foundKeyword)));
            return;
        }
        
        /**Is the keyword used by another program*/
        $shortCode = $this->ProgramSetting->find('getProgramSetting', array('key' => 'shortcode'));
        $usedByOtherProgramResult = $this->Keyword->areKeywordsUsedByOtherPrograms(
            $programDb,
            $shortCode,
            array($keywordToValidate)); 
        if ($usedByOtherProgramResult != array()) {
            $this->set('result', array(
                'status' => 'fail', 
                'message' => $this->Keyword->validationToMessage($usedByOtherProgramResult)));
            return;
        }
        
        $this->set('result', array('status' => 'ok'));
    }
    
    
    protected function _notifyUpdateRegisteredKeywords($workerName)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateRegisteredKeywords($workerName);
    }
    
    
    
}
