<?php
App::uses('BaseProgramSpecificController', 'Controller');
//App::uses('Participant', 'Model');
//App::uses('Schedule', 'Model');
//App::uses('History', 'Model');


class ProgramAjaxController extends BaseProgramSpecificController
{
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')),
        'ProgramAuth',
        'Stats');
    
    /*
    public function getStats()
    { 
        $requestSuccess  = true;
        if (!$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        $dbName = $this->programDetails['database'];
        $programStats = $this->Stats->getProgramStats($dbName);
        $this->set(compact('requestSuccess', 'programStats'));
    }*/

    var $statsTypeToView = array(
        'summary' => 'get_stats',
        'participants' => 'participants_nvd3',
        'history' => 'history_nvd3',
        'schedules' => 'schedules_nvd3',
        'top_dialogues_requests' => 'top_dialogues_requests' );

    public function getStats()
    {        
        $requestSuccess  = true;
        /*if (!$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }*/
        $dbName = $this->programDetails['database'];
        $statsType = $this->Stats->getStatsType();
        $stats = $this->Stats->getStats($dbName, $statsType);
        $this->set(compact('stats', 'requestSuccess'));
        $view = $this->statsTypeToView[$statsType];
        $this->render($view);
    }
    
    
}
