<?php
App::uses('AppController', 'Controller');
App::uses('Participant', 'Model');
App::uses('Schedule', 'Model');
App::uses('History', 'Model');

class ProgramAjaxController extends AppController
{
    var $components = array(
        'RequestHandler',
        'Stats');
    
    function constructClasses()
    {
        parent::constructClasses();
    }
    
    
    public function getStats()
    { 
        $programUrl = $this->params['program'];
        $programDatabase = $this->Session->read($programUrl."_db");        
        
        
       $programStats = array('status' =>'ok', 'programUrl' => $programUrl, 'programStats' => $this->Stats->getProgramStats($programDatabase));
        
        $this->set(compact('programStats'));
    }
}
