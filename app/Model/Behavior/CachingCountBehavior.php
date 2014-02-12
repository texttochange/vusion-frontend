<?php


class CachingCountBehavior extends ModelBehavior {

    public function setup($model, $settings = array())
    {
         if (!isset($this->settings[$model->alias])) {
			$this->settings[$model->alias] = array(
			    'redis' => array('host' => '127.0.0.1', 'port' => '6379'), 
			    'redisPrefix' => array('base' => 'vusion', 'programs' => 'programs'),
			    'cacheCountExpire' => array(
			        1 => 1,           #1sec cache 30sec
			        5 => 240          #5sec cache  4min
			        ));
		}
		$this->settings[$model->alias]['redis'] = array_merge($this->settings[$model->alias]['redis'], $settings['redis']);
		$this->settings[$model->alias]['redisPrefix'] = array_merge($this->settings[$model->alias]['redisPrefix'], $settings['redisPrefix']);
		$this->settings[$model->alias]['cacheCountExpire'] = $settings['cacheCountExpire'];

		$this->redis = new Redis();
		$this->redis->connect(
		    $this->settings[$model->alias]['redis']['host'],		   
		    $this->settings[$model->alias]['redis']['port']);
    }


    protected function _getCachedCountKey($model, $conditions)
    {
        $str = $this->_sortAndDiggest($conditions);
        return $this->settings[$model->alias]['redisPrefix']['base'] . ':' . $this->settings[$model->alias]['redisPrefix']['programs'] .':'. $model->databaseName . ':cachedcounts:' . $model->alias . ':' . $str;
    }

 
    protected function _getCachedCountKeys($model)
    {
        return $this->settings[$model->alias]['redisPrefix']['base'] . ':' . $this->settings[$model->alias]['redisPrefix']['programs'] .':'. $model->databaseName . ':cachedcounts:' . $model->alias . ':*';      
    }
    

    protected function _getExpiringTime($model, $duration)
    {
        foreach ($this->settings[$model->alias]['cacheCountExpire'] as $computationDuration => $cacheDuration) {
            if ($duration <= $computationDuration) {
                return $cacheDuration;
            }
        }
        return end($this->settings[$model->alias]['cacheCountExpire']);
    }


    public function count($model, $conditions = true, $limit = null, $timeout = 30000) 
    {
        $cachedCountKey = $this->_getCachedCountKey($model, $conditions);
        $cachedCount = $this->redis->get($cachedCountKey);
        if ($cachedCount) {
            return  (int) $cachedCount;
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

    public function flushCached($model) 
    {
        $keys = $this->redis->keys($this->_getCachedCountKeys($model));
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }

    //In case one element in the collection is remove, all cached count are deleted.
    public function afterDelete($model)
    {
        $this->flushCached($model);
    }


    protected function _recur_ksort(&$array) 
    {
        if (!is_array($array)) {
            return;
        }
        foreach ($array as &$value) {
            if (is_array($value)) { 
                $this->_recur_ksort($value);
            }
        }
        return ksort($array);
    }


    protected function _sortAndDiggest($conditions)
    {
        $this->_recur_ksort($conditions);
        $conditions = serialize($conditions);
        $hashedConditions = md5($conditions);
        return $hashedConditions;
    }

}