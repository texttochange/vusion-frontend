<?php

App::uses('AppController','Controller');
App::uses('UnattachedMessage', 'Model');
App::uses('DialogueHelper', 'Helper');
App::uses('Dialogue', 'Model');
App::uses('Participant', 'Model');
App::uses('History', 'Model');
App::uses('Schedule', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramHomeController extends AppController
{

    var $components = array('RequestHandler');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Time'
        );


    function constructClasses()
    {
        parent::constructClasses();

        $options = array('database' => ($this->Session->read($this->params['program']."_db")));
        
        $this->Participant       = new Participant($options);
        $this->History           = new History($options);
        $this->Schedule          = new Schedule($options);
        $this->Dialogue          = new Dialogue($options);
        $this->UnattachedMessage = new UnattachedMessage($options);

        $this->VumiRabbitMQ = new VumiRabbitMQ(
            Configure::read('vusion.rabbitmq')
            );
        
        $this->DialogueHelper = new DialogueHelper();
        
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
                
        $schedules        = $this->Schedule->summary();

        $activeInteractions = $this->Dialogue->getActiveInteractions();

        foreach ($schedules as &$schedule) {
            if (isset($schedule['interaction-id'])) {
                $interaction = $this->DialogueHelper->getInteraction(
                    $activeInteractions,
                    $schedule['interaction-id']
                    );
                if (isset($interaction['content']))
                    $schedule['content'] = $interaction['content'];
            }
            elseif (isset($schedule['unattach-id'])) {
                $unattachedMessage = $this->UnattachedMessage->read(null, $schedule['unattach-id']);
                if (isset($unattachedMessage['UnattachedMessage']['content']))
                    $schedule['content'] = $unattachedMessage['UnattachedMessage']['content'];
            } 
        }
                
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
        $programUrl   = $this->params['program'];
        $databaseName = $this->Session->read($programUrl.'_db');

        $this->_startBackendWorker(
            $programUrl,
            $databaseName
            );
        $this->set(
            'result',
            array('status'=>'ok','message'=> __('Worker is starting.'))
            );
    }   


    protected function _startBackendWorker($workerName, $databaseName)
    {
        $this->VumiRabbitMQ->sendMessageToCreateWorker($workerName,$databaseName);    	 
    }
        

}
