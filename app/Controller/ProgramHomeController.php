<?php

App::uses('AppController','Controller');
App::uses('Script','Model');
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


    public function index()
    {
    	/*$redis = new Redis();
    	$redis->connect('127.0.0.1');
    	print_r($redis->zRange('wiki:logs',1, -1, true));*/

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


    function constructClasses()
    {
        parent::constructClasses();

        $options = array('database' => ($this->Session->read($this->params['program']."_db")));
        
        $this->Script         = new Script($options);
        $this->Participant    = new Participant($options);
        $this->History        = new History($options);
        $this->Schedule       = new Schedule($options);

        $this->VumiSupervisord = new VumiSupervisord();
    }


}
