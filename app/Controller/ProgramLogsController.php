<?php
App::uses('BaseProgramSpecificController','Controller');


class ProgramLogsController extends BaseProgramSpecificController
{
    
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')), 
        'LogManager',
        'ProgramAuth',
        'ArchivedProgram');
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
        $requestSuccess = true;
        
        if (!$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        
        $programUrl = $this->params['program'];
        $databaseName = $this->Session->read($programUrl."_db");
        $programLogs = $this->LogManager->getLogs($databaseName, 5);
        $this->set(compact('requestSuccess', 'programLogs'));
    }
    
    
}
