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
        'ParticipantStats',
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
    }

    /*
    public function aggregate()
    {
        $requestSuccess = true;
        $schedules = $this->Schedule->aggregate();
        $this->set(compact('schedules', 'requestSuccess'));
    }


    public function aggregateNvd3()
    {
        $requestSuccess = true;
        $time = $this->ProgramSetting->getProgramTimeNow(); 
        $time->modify('+1 week');
        $schedules = $this->Schedule->aggregateNvd3(DialogueHelper::fromPhpDateToVusionDate($time));
        $this->set(compact('schedules', 'requestSuccess'));
        $this->render('aggregate');
    }*/
    
    
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
