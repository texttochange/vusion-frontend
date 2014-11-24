<?php
App::uses('BaseProgramSpecificController','Controller');
App::uses('UnattachedMessage', 'Model');
App::uses('Dialogue', 'Model');
App::uses('Participant', 'Model');
App::uses('History', 'Model');
App::uses('Schedule', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramHomeController extends BaseProgramSpecificController
{
    var $uses = array(
        'Participant',
        'History',
        'Schedule',
        'Dialogue',
        'UnattachedMessage',
        'ProgramSetting');
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')),
        'ProgramAuth',
        'ArchivedProgram');
    
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
