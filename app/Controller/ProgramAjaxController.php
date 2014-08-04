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
    
    
    public function getStats()
    { 
        if (!$this->_isAjax()) {
            return;
        }

        $programUrl      = $this->params['program'];
        $programDatabase = $this->Session->read($programUrl."_db");        
        $programStats    = $this->Stats->getProgramStats($programDatabase);
        
        $this->set('ajaxResult',array(
            'status' => 'ok',
            'programStats' => $programStats,
            'programUrl' => $programUrl));
    }


}
