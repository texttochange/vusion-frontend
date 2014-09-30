<?php
App::uses('AppController','Controller');
App::uses('UserLog','Model');

class UserLogsController extends AppController
{
    var $components = array(
        'UserLogManager');
    
    public function constructClasses()
    {
        parent::constructClasses();
        
        if (!Configure::read("mongo_db")) {
            $options = array(
                'database' => 'vusion'
                );
        } else {
            $options = array(
                'database' => Configure::read("mongo_db")
                );
        }
        
        $this->UserLog = new UserLog($options);
    }
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
    }
    
    
    public function index()
    {
        $userLogs = $this->UserLog->getUserLogs();
        $this->set(compact('userLogs'));
    }

}
