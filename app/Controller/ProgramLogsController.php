<?php

App::uses('AppController','Controller');

class ProgramLogsController extends AppController
{
	
    var $components = array(
        'RequestHandler', 
        'BackendLog');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Time');
    
    
    function constructClasses()
    {
        parent::constructClasses();
    }
    
    
    function beforeFilter() 
    {
        parent::beforeFilter();        
    }
    
    
    public function index()
    {
        $programUrl = $this->params['program'];
        $databaseName = $this->Session->read($programUrl."_db");
        $programLogs = $this->BackendLog->getLogs($databaseName, 200);
        $this->set(compact('programLogs'));
    } 
    
    
    public function getBackendNotifications()
    {
        $requestSuccess = true;
        
        if (!$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }

        $programUrl = $this->params['program'];
        $databaseName = $this->Session->read($programUrl."_db");
        $programLogs = $this->BackendLog->getLogs($databaseName, 5);
        $this->set(compact('requestSuccess', 'programLogs'));
    }
    
    
}
