<?php
App::uses('Component', 'Controller');

class RedisComponent extends Component
{    
    var $redis = null;
    var $redisProgramPrefix = "vusion:programs";
    
    
    public function redisConnect()
    {
        print_r('hello iam in');
        print_r('***************************');
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
        $redisPrefix = Configure::read('vusion.redisPrefix');
        if (is_array($redisPrefix)) { 
            $this->redisProgramPrefix = $redisPrefix['base'] . ':' . $redisPrefix['programs'];
        }
        return $this->redisProgramPrefix;
    }
    
    
}
