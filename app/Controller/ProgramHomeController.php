<?php

App::uses('AppController','Controller');
App::uses('UnattachedMessage','Model');
App::uses('ScriptHelper', 'Helper');
App::uses('Participant','Model');
App::uses('History','Model');
App::uses('Schedule','Model');
App::uses('VumiSupervisord','Lib');


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
        
        $this->Participant    = new Participant($options);
        $this->History        = new History($options);
        $this->Schedule       = new Schedule($options);
        $this->UnattachedMessage = new UnattachedMessage($options);

        $this->VumiSupervisord = new VumiSupervisord();
        
        $this->ScriptHelper = new ScriptHelper();
        
    }


    public function index()
    {

        $hasScriptActive  = count($this->Script->find('countActive'));
        $hasScriptDraft   = count($this->Script->find('countDraft'));
        $isScriptEdit     = $this->Acl->check(array(
                'User' => array(
                    'id' => $this->Session->read('Auth.User.id')
                ),
            ), 'controllers/ProgramScripts');
        $isParticipantAdd = $this->Acl->check(array(
                'User' => array(
                    'id' => $this->Session->read('Auth.User.id')
                ),
            ), 'controllers/ProgramParticipants/add');
        $participantCount = $this->Participant->find('count');
        $statusCount      = $this->History->find('count');
        $schedules        = $this->Schedule->find('soon');
        
        //$workerStatus = $this->VumiSupervisord->getWorkerInfo($programUrl);
        
        $schedules        = $this->Schedule->summary();

        foreach ($schedules as &$schedule) {
            if (isset($schedule['interaction-id'])) {
                $interaction = $this->ScriptHelper->getInteraction(
                    $this->Script->find('active'),
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
        

}
