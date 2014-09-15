<?php

App::uses('AppController','Controller');
App::uses('UnattachedMessage', 'Model');
App::uses('DialogueHelper', 'Helper');
App::uses('Dialogue', 'Model');
App::uses('Participant', 'Model');
App::uses('History', 'Model');
App::uses('Schedule', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramHomeController extends AppController
{
    
    var $components = array(
        'RequestHandler');
    
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Time');
    
    
    function constructClasses()
    {
        parent::constructClasses();
    }
    
    
    function beforeFilter()
    {
        parent::beforeFilter();
        
        $options = array('database' => ($this->Session->read($this->params['program']."_db")));
        
        $this->Participant       = new Participant($options);
        $this->History           = new History($options);
        $this->Schedule          = new Schedule($options);
        $this->Dialogue          = new Dialogue($options);
        $this->UnattachedMessage = new UnattachedMessage($options);
        $this->ProgramSetting   = new ProgramSetting($options);
        
        $this->DialogueHelper = new DialogueHelper();
        
        $this->_instanciateVumiRabbitMQ();
    }
    
    
    protected function _instanciateVumiRabbitMQ()
    {
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }
    
    
    public function index()
    {
        
        $isParticipantAdd = $this->Acl->check(array(
            'User' => array(
                'id' => $this->Session->read('Auth.User.id')
                ),
            ), 'controllers/ProgramParticipants/add');
        $participantCount = $this->Participant->find('count');
        $statusCount      = $this->History->find('count');
        //$schedules        = $this->Schedule->find('soon');
        
        $activeInteractions = $this->Dialogue->getActiveInteractions();
        
        $timeNow = $this->ProgramSetting->getProgramTimeNow(); 
        
        if (isset($timeNow)) 
            $timeNow->modify('+1 day');
        $schedules = $this->Schedule->generateSchedule(
            $this->Schedule->summary($timeNow),
            $activeInteractions
            );
        $this->set(compact(
            'hasScriptActive', 
            'hasScriptDraft',
            'isScriptEdit', 
            'isParticipantAdd', 
            'participantCount',
            'statusCount',
            'schedules',
            'workerStatus'));
    }
    
    
    public function restartWorker()
    {
        $requestSuccess = true;
        if (!$this->request->is('get') || !$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }

        $programUrl   = $this->params['program'];
        $databaseName = $this->Session->read($programUrl.'_db');
        
        $this->_startBackendWorker(
            $programUrl,
            $databaseName);
        $this->Session->setFlash(__('Worker is starting.'));
        $this->set(compact('requestSuccess'));
    }   
    
    
    protected function _startBackendWorker($workerName, $databaseName)
    {
        $this->VumiRabbitMQ->sendMessageToCreateWorker($workerName,$databaseName);    	 
    }
    
    
}
