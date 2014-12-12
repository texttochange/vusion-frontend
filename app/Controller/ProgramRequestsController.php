<?php
App::uses('BaseProgramSpecificController', 'Controller');
App::uses('Request', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Dialogue', 'Model');
App::uses('DialogueHelper', 'Helper');
App::uses('Participant', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramRequestsController extends BaseProgramSpecificController
{
    var $uses = array(
        'Request',
        'Dialogue',
        'ProgramSetting',
        'Participant');
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')), 
        'LocalizeUtils', 
        'Utils',
        'Keyword',
        'DynamicForm',
        'ProgramAuth',
        'ArchivedProgram');
    
    
    function constructClasses()
    {
        parent::constructClasses();
    }
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->RequestHandler->accepts('json');
        $this->RequestHandler->addInputType('json', array('json_decode'));

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
    }
    
    
    public function edit()
    {
        $this->set('conditionalActionOptions', $this->_getConditionalActionOptions());
        
        $programUrl = $this->params['program'];
        $programDb  = $this->Session->read($programUrl."_db");
        $id         = $this->params['id'];
        
        if ($this->request->is('get')) {
            $this->Request->id = $id;
            if (!$this->Request->exists()) {
                throw new NotFoundException(__('Invalide Request') . $id);
            }
            
            $this->set('request', $this->Request->read(null, $id));
        }
    }
    
    
    public function save()
    {
        $programUrl     = $this->params['program'];
        $programDb      = $this->Session->read($programUrl."_db");
        $programName    = $this->Session->read($programUrl."_name");
        $requestSuccess = false;
        
        if (!$this->request->is('post') || !$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        
        if (!$this->ProgramSetting->hasRequired()) {
            $this->Session->setFlash(__('Please set the program settings then try again.'));
            $this->set(compact('requestSuccess')); 
            return;
        }
        
        $shortCode     = $this->ProgramSetting->find('getProgramSetting', array('key' => 'shortcode'));
        $request       = DialogueHelper::objectToArray($this->request->data);
        $id            = Request::getRequestId($request);
        $keywords      = Request::getRequestKeyphrases($request);
        $foundKeywords = $this->Keyword->areUsedKeywords($programDb, $shortCode, $keywords, 'Request', $id);
        if ($savedRequest = $this->Request->saveRequest($request,  $foundKeywords)) {
            $this->_notifyReloadRequest($programUrl, $savedRequest['Request']['_id']."");
            $requestSuccess = true;
            
            $this->Session->setFlash(__('Request saved.'));
            $this->set(compact('savedRequest'));
        } else {
            $this->Session->setFlash(__('This request has a validation error, please correct it and save again.'));
            $this->Request->validationErrors = $this->Utils->fillNonAssociativeArray($this->Request->validationErrors);
        }
        $this->set(compact('requestSuccess'));
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
            $this->_notifyReloadRequest($programUrl, $id);
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
        $programUrl     = $this->params['program'];
        $programDb      = $this->Session->read($programUrl."_db");
        $usedKeywords   = $this->request->data['keyword'];
        $requestId      = $this->request->data['object-id'];
        $requestSuccess = true;
        
        if (!$this->request->is('post') || !$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        
        if (!$this->ProgramSetting->hasRequired()) {
            $this->Session->setFlash(__('Please set the program settings then try again.'));
            return;
        }
        
        $shortCode = $this->ProgramSetting->find('getProgramSetting', array('key' => 'shortcode'));
        $foundKeywords = $this->Keyword->areUsedKeywords($programDb, $shortCode, $usedKeywords, 'Request', $requestId); 
        if ($foundKeywords) {
            $foundMessage = $this->Keyword->foundKeywordsToMessage($programDb, $foundKeywords);
            $requestSuccess = false;
            $this->set(compact('requestSuccess', 'foundMessage'));
            return;
        }
        $this->set(compact('requestSuccess'));
    }
    
    
    protected function _notifyReloadRequest($workerName, $requestId)
    {
        $this->VumiRabbitMQ->sendMessageToReloadRequest($workerName, $requestId);
    }
    
    
}
