<?php
App::uses('BaseProgramSpecificController', 'Controller');
App::uses('Participant', 'Model');
App::uses('Schedule', 'Model');
App::uses('History', 'Model');

class ProgramAjaxController extends BaseProgramSpecificController
{
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')),
        'ProgramAuth',
        'Stats');
    
    
    public function getStats()
    { 
        $requestSuccess  = true;
        if (!$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        $dbName = $this->programDetails['database'];
        $programStats = $this->Stats->getProgramStats($dbName);
        $this->set(compact('requestSuccess', 'programStats'));
    }
    
    
}
