<?php
App::uses('Component', 'Controller');

class BackendLogComponent extends Component
{

    var $components          = array('Redis');
    //public $Controller         = null;
    //public $redis              = null;
    //public $redisProgramPrefix = null;


    public function initialize(Controller $controller) 
    {  
        //$this->Controller = $controller;
        //if (!isset($this->Controller->redis) || $this->Controller->redisProgramPrefix == null) {
        //     throw new InternalErrorException("The BackendLog need a redis instance from his controller.");
        // }
        // $this->redis = $this->Controller->redis;
        // $this->redisProgramPrefix = $this->Controller->redisProgramPrefix;
    }
    
    
    protected function getLogsKey($programDatabase)
    {
        $redisProgramPrefix = $this->Redis->getProgramPrefix();
        return $redisProgramPrefix . ":" . $programDatabase . ":logs"; 
    }


    public function getLogs($programDatabase, $limit=200)
    {  
        $redis = $this->Redis->redisConnect();
        $programLogs = array();

        $limit = -1 * $limit;

        $logs = $redis->zRange($this->getLogsKey($programDatabase), $limit, -1, true);
        foreach ($logs as $key => $value) {
            $programLogs[] = $key;
        }
        return array_reverse($programLogs);
    }


}
