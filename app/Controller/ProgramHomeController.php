<?php

App::uses('AppController','Controller');
App::uses('UnattachedMessage','Model');
App::uses('Script','Model');
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
        
        $this->Script         = new Script($options);
        $this->Participant    = new Participant($options);
        $this->History        = new History($options);
        $this->Schedule       = new Schedule($options);
        $this->UnattachedMessage = new UnattachedMessage($options);

        $this->VumiSupervisord = new VumiSupervisord();
        
        $this->ScriptHelper = new ScriptHelper();
        
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1');
    }


    public function index()
    {

        $hasScriptActive  = count($this->Script->find('countActive'));
        $hasScriptDraft   = count($this->Script->find('countDraft'));
        $hasProgramLogs   = $this->_hasProgramLogs();
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
        
        $programLogs = $this->_processProgramLogs();
                
        $this->set(compact(
            'programLogs',
            'hasScriptActive', 
            'hasScriptDraft',
            'hasProgramLogs',
            'isScriptEdit', 
            'isParticipantAdd', 
            'participantCount',
            'statusCount',
            'schedules',
            'workerStatus'));
    }
    
    
    protected function _hasProgramLogs()
    {
        if (count($this->redis->zRange($this->params['program'].':logs', -5, -1, true)) > 0)
            return true;
        return false;
    }
    
    
    protected function _processProgramLogs()
    {
        if ($this->_hasProgramLogs()) {
            $programLogs = array();
        
            $logs = $this->redis->zRange($this->params['program'].':logs', -5, -1, true);
            foreach ($logs as $key => $value) {
                $programLogs[] = $key;
            }
            return array_reverse($programLogs);
        }
        return array();    	    	    
    }


}
