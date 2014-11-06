<?php
App::uses('AppController', 'Controller');
App::uses('Participant', 'Model');
App::uses('Schedule', 'Model');
App::uses('History', 'Model');

class ProgramAjaxController extends AppController
{
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')),
        'Stats');
    
    
    public function getStats()
    { 
        $requestSuccess  = true;
        if (!$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        
        $programUrl      = $this->params['program'];
        $programDatabase = $this->Session->read($programUrl."_db");        
        $programStats    = $this->Stats->getProgramStats($programDatabase);
        $this->set(compact('requestSuccess', 'programStats'));
    }
    
    
}
