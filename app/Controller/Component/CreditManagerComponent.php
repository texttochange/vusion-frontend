<?php
App::uses('Component', 'Controller');

class CreditManagerComponent extends Component
{

    var $components          = array('Redis');
    public $Controller         = null;
    //public $redis              = null;
    //public $redisProgramPrefix = null;


    public function initialize(Controller $controller)
    {
       /*$this->Controller = $controller;
        if (!isset($this->Controller->redis) || $this->Controller->redisProgramPrefix == null) {
            throw new InternalErrorException("The CreditManager need a redis instance from his controller.");
        }
        $this->redis = $this->Controller->redis;
        $this->redisProgramPrefix = $this->Controller->redisProgramPrefix;*/
    }


    protected function getStatusKey($programDatabase)
    {
        $this->redisProgramPrefix = $this->Redis->getProgramPrefix();
        return $this->redisProgramPrefix . ":" . $programDatabase . ":creditmanager:status"; 
    }


    protected function getCountKey($programDatabase)
    { 
        $this->redisProgramPrefix = $this->Redis->getProgramPrefix();
        return $this->redisProgramPrefix . ":" . $programDatabase . ":creditmanager:count"; 
    }


    public function getCount($programDatabase)
    {   
        $this->redis = $this->Redis->redisConnect();
        $countKey = $this->getCountKey($programDatabase);
        print_r($countKey);
        print_r('!!!!!!!!!!!!');
        $count    = $this->redis->get($countKey);
        if ($count == null || !isset($count)) {
            return null; 
        }
        return (int)$count;
    }


    public function getStatus($programDatabase)
    {
        $this->redis = $this->Redis->redisConnect();
        $statusKey = $this->getStatusKey($programDatabase);
        $statusRaw = $this->redis->get($statusKey);
        print_r($statusKey);
        print_r('%%%%%%%');
        if ($statusRaw == null || !isset($statusRaw)) {
            return null; 
        }
        return (array)json_decode($statusRaw);
    }


    public function getOverview($programDatabase)
    {
        return array(
            'count' => $this->getCount($programDatabase),
            'manager' => $this->getStatus($programDatabase));
    }


}