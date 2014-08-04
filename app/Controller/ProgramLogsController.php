<?php

App::uses('AppController','Controller');

class ProgramLogsController extends AppController
{
	
    var $components = array(
        'RequestHandler', 
        'LogManager');
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
        $programLogs = $this->LogManager->getLogs($databaseName, 200);
        $this->set(compact('programLogs'));
    } 
    
    
    public function getBackendNotifications()
    {
        if (!$this->_isAjax()) {
            return;
        }

        $programUrl = $this->params['program'];
        $databaseName = $this->Session->read($programUrl."_db");
        $programLogs = $this->LogManager->getLogs($databaseName, 5);
        $this->set('ajaxResult', array(
            'status' => 'ok',
            'programLogs' => $programLogs));
    }
    
    
}
