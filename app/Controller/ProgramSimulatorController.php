<?php

App::uses('AppController', 'Controller');
App::uses('Script', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Participant', 'Model');
App::uses('History', 'Model');
App::uses('Schedule', 'Model');
App::uses('VumiRabbitMQ', 'Lib');

class ProgramSimulatorController extends AppController
{

    public $uses    = array('Script');
    var $components = array('RequestHandler');


    public function constructClasses()
    {
        parent::constructClasses();
        $options              = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->Script         = new Script($options);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->Participant    = new Participant($options);
        $this->VumiRabbitMQ   = new VumiRabbitMQ();
    }


    public function beforeFilter()
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
    }


    public function simulate()
    {
        $this->Script->id = $this->params['id'];
        if (!$this->Script->exists()) {
            $this->Session->setFlash(
                __('The script id is not in the database, please choose one available.')
                );
            $scripts = array();
            if ($this->Script->find('draft'))
                $scripts['draft'] = $this->Script->find('draft');
            if ($this->Script->find('active'))
                $scripts['currently active'] = $this->Script->find('active');
            $this->set(compact('scripts'));
            return;
        }
        $this->_startSimulateScript($this->Script->id);
        
    }


    protected function _startSimulateScript()
    {
         $this->VumiRabbitMQ->sendMessageToRemoveWorker('simulator', 'simulator');
         //$this->Script->id = $script_id;
         $script = $this->Script->read();
         
         $options = array('database' => 'simulator');

         $simulatorScriptModel = new Script($options);
         $simulatorScriptModel->deleteAll(true, false);
         $simulatorScriptModel->create();
         $simulatorScriptModel->save($script);
         $simulatorScriptModel->makeDraftActive();
         
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

         while($this->VumiRabbitMQ->getMessageFrom('simulator.outbound'))
             continue;

         $this->VumiRabbitMQ->sendMessageToCreateWorker('simulator', 'simulator', 'simulator.disptacher');
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
