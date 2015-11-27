<?php
App::uses('Component', 'Controller');

class RedisComponent extends Component
{    
    var $redis = null;
    var $redisProgramPrefix = "vusion:programs";
    var $redisTicketPrefix  = "vusion:tickets"; 
    var $redisExportPrefix  = "vusion:exports";
    
    
    public function redisConnect()
    {
        print_r('hello iam in');
        print_r('********');
        if (!isset($this->redis)) {
            $this->redis = new Redis();
            $redisConfig = Configure::read('vusion.redis');
            $redisHost   = (isset($redisConfig['host']) ? $redisConfig['host'] : '127.0.0.1');
            $redisPort   = (isset($redisConfig['port']) ? $redisConfig['port'] : '6379');
            $this->redis->connect($redisHost, $redisPort);
        }        
        return $this->redis;
    }
    
    
    public function getProgramPrefix()
    {
        return $this->redisProgramPrefix;
    }
    
    
    public function getTicketPrefix($hash)
    {
        return $this->redisTicketPrefix.':'.$hash;
    }
    
    
}
