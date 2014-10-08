<?php
App::uses('AppController','Controller');
App::uses('UserLog','Model');

class UserLogsController extends AppController
{  
    
    
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
