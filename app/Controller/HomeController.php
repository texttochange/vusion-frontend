<?php

App::uses('AppController','Controller');
App::uses('Script','Model');
App::uses('Participant','Model');
App::uses('ParticipantsState','Model');
App::uses('Schedule','Model');
App::uses('VumiSupervisord','Lib');


class HomeController extends AppController 
{

    var $components = array('RequestHandler');
    var $helpers    = array('Js' => array('Jquery'));


    public function index()
    {
        $programName      = $this->Session->read($this->params['program'].'_name');
        $programUrl       = $this->params['program'];
        $hasScriptActive  = count($this->Script->find('countActive'));
        $hasScriptDraft   = count($this->Script->find('countDraft'));
        $isScriptEdit     = $this->Acl->check(array(
                'User' => array(
                    'id' => $this->Session->read('Auth.User.id')
                ),
            ), 'controllers/Scripts');
        $isParticipantAdd = $this->Acl->check(array(
                'User' => array(
                    'id' => $this->Session->read('Auth.User.id')
                ),
            ), 'controllers/Participants/add');
        $participantCount = $this->Participant->find('count');
        $statusCount      = $this->ParticipantsState->find('count');
        $schedules        = $this->Schedule->find('soon');
        
        //$workerStatus = $this->VumiSupervisord->getWorkerInfo($programUrl);

        $this->set(compact('programName',
            'programUrl',
            'hasScriptActive', 
            'hasScriptDraft',
            'isScriptEdit', 
            'isParticipantAdd', 
            'participantCount',
            'statusCount',
            'schedules',
            'workerStatus'));
    }


    function constructClasses()
    {
        parent::constructClasses();

        $options = array('database' => ($this->Session->read($this->params['program']."_db")));
        
        $this->Script            = new Script($options);
        $this->Participant       = new Participant($options);
        $this->ParticipantsState = new ParticipantsState($options);
        $this->Schedule          = new Schedule($options);

        $this->VumiSupervisord = new VumiSupervisord();
    }


}
