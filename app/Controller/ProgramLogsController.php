<?php

App::uses('AppController','Controller');

class ProgramLogsController extends AppController
{
    var $components = array('RequestHandler');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Time'
        );
    
    
    function constructClasses()
    {
        parent::constructClasses();
        
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1');
    }
    
    
    public function _getRedisZRange($startValue,$endValue)
    {
        $redisKey = $this->params['program'].':logs';
        return $this->redis->zRange($redisKey, $startValue, $endValue, true);
    }
    
    
    public function index()
    {
        $programLogs = $this->_getRedisZRange(0,-1);
        $programLogs = array_reverse($programLogs);
        $this->set(compact('programLogs'));
    } 
        
    
    public function getBackendNotifications()
    {
        $programLogs = array();
        
        $logs = $this->_getRedisZRange(-5,-1);
        foreach ($logs as $key => $value) {
            $programLogs[] = $key;
        }
        $this->set(compact('programLogs'));
    }
    
    
}
