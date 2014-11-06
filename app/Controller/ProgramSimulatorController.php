<?php

App::uses('AppController', 'Controller');
App::uses('Dialogue', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Participant', 'Model');
App::uses('History', 'Model');
App::uses('Schedule', 'Model');
App::uses('VumiRabbitMQ', 'Lib');

class ProgramSimulatorController extends AppController
{
    
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')));
    var $uses = array();
    
    
    public function constructClasses()
    {
        parent::constructClasses();
        $options              = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->Dialogue       = new Dialogue($options);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->Participant    = new Participant($options);
        $this->_instanciateVumiRabbitMQ();
    }
    
    
    protected function _instanciateVumiRabbitMQ()
    {
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
    }
    
    
    public function simulate()
    {
        $this->Dialogue->id = $this->params['id'];
        if (!$this->Dialogue->exists()) {
            $this->Session->setFlash(
                __('This Dialogue id is not in the database, please choose one available.'), 
                'default',
                array('class' => "message failure")                
                );
            return;
        }
        $this->_startSimulateScript();
        
    }
    
    
    protected function _startSimulateScript()
    {
        $this->VumiRabbitMQ->sendMessageToRemoveWorker('simulator', 'simulator');
        //$this->Script->id = $script_id;         
        $options = array('database' => 'simulator');
        
        /**Copy a version of the Dialogue to the database*/
        $dialogue = $this->Dialogue->read();
        $simulatorDialogueModel = new Dialogue($options);
        $simulatorDialogueModel->deleteAll(true, false);
        $simulatorDialogueModel->create();
        $simulatorDialogueModel->save($dialogue);
        $simulatorDialogueModel->makeActive($dialogue['Dialogue']['_id']);
        
        $programSettings = $this->ProgramSetting->find('all');
        $simulatorProgramSettingModel = new ProgramSetting($options);
        $simulatorProgramSettingModel->deleteAll(true, false);
        foreach ($programSettings as $programSetting) {
            $simulatorProgramSettingModel->create();
            $simulatorProgramSettingModel->save($programSetting);
        }
        
        $participants = $this->Participant->find('all');
        $simulatorParticipantModel = new Participant($options);
        $simulatorParticipantModel->deleteAll(true, false);
        foreach ($participants as $participant) {
            $simulatorParticipantModel->create();
            $simulatorParticipantModel->save($participant);
        }
        
        $simulatorHistoryModel = new History($options);
        $simulatorHistoryModel->deleteAll(true, false);
        
        $simulatorScheduleModel = new Schedule($options);
        $simulatorScheduleModel->deleteAll(true,false);
        
        /**Clearning the receiving queue*/
        while($this->VumiRabbitMQ->getMessageFrom('simulator.outbound'))
            continue;
        
        $this->VumiRabbitMQ->sendMessageToCreateWorker('simulator', 'simulator', 'simulator.disptacher', '10');
        $this->VumiRabbitMQ->sendMessageToUpdateSchedule('simulator');
    }
    
    
    public function send()
    {
        if ($this->request->is('post')) {
            $message = $this->request->data['message'];
            $from    = $this->request->data['participant-phone'];
            $this->VumiRabbitMQ->sendMessageToWorker('simulator', $from, $message);
        }
    }
    
    
    public function receive()
    {
        $message = $this->VumiRabbitMQ->getMessageFrom('simulator.outbound');
        $this->set(compact('message'));
    }
    
    
}
