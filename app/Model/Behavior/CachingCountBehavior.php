<?php


class CachingCountBehavior extends ModelBehavior {

    public function setup($model, $settings = array())
    {
        if (!isset($this->settings[$model->alias])) {
			$this->settings[$model->alias] = array(
			    'redis' => array('host' => '127.0.0.1', 'port' => '6379'), 
			    'redisPrefix' => 'vusion:programs',
			    'cacheExpire' => array(
			        1 => 1,           #1sec cache 30sec
			        5 => 240          #5sec cache  4min
			        ));
		}
		$this->settings[$model->alias] = array_merge($this->settings[$model->alias], $settings);
		$this->redis = new Redis();
		$this->redis->connect(
		    $this->settings[$model->alias]['redis']['host'],		   
		    $this->settings[$model->alias]['redis']['port']);
    }


    protected function _getCachedCountKey($model, $conditions)
    {
        $str = serialize($conditions);
        return $this->settings[$model->alias]['redisPrefix']. ':' . $model->databaseName . ':cachedcounts:' . $str;
    }


    protected function _getExpiringTime($model, $duration)
    {
        foreach ($this->settings[$model->alias]['cacheExpire'] as $computationDuration => $cacheDuration) {
            if ($duration <= $computationDuration) {
                return $cacheDuration;
            }
        }
        return end($this->settings[$model->alias]['cacheExpire']);
    }


    public function count($model, $conditions = true, $limit = null, $timeout = 30000) 
    {
        $cachedCountKey = $this->_getCachedCountKey($model, $conditions);
        $cachedCount = $this->redis->get($cachedCountKey);
        if ($cachedCount) {
            echo "Hit the count cache $cachedCount<br/>";
            return $cachedCount;
        } 
        $command = array(
            'count' => $model->useTable,
            'query' => $conditions);
        if ($limit) {
            $command['limit'] = $limit;
        }
        $start = time();
        $result = $model->query($command, array('timeout' => $timeout));
        $end = time();
        if ($result['ok']) {
            $result = $result['n'];
        }
        if (!isset($limit)) {
            $duration = $end - $start;
            $expiringTime = $this->_getExpiringTime($model, $duration);
            $this->redis->setex(
                $cachedCountKey,
                $expiringTime * 1000,
                $result);
        }
        return $result;
        
    }

}