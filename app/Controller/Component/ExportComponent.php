<?php
App::uses('Component', 'Controller');


class ExportComponent extends Component
{
    var $redis;
    var $redisExportPrefix;


    public function initialize(Controller $controller)
    {

        if (isset($controller->redis)) {
            $this->redis = $controller->redis;
        } else { 
            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1');
        }
        
        if (isset($controller->redisExportPrefix)) {
            $this->redisExportPrefix = $controller->redisExportPrefix;
        } else {
            throw new InternalErrorException("The Export Component needs a redis prefix.");
        }
    }


    protected function _getExportKey($programUrl, $collection)
    {
        return $this->redisExportPrefix . ':' . $programUrl . ':' . $collection;
    }


    public function startAnExport($programUrl, $collection)
    {
        $key = $this->_getExportKey($programUrl, $collection);
        $this->redis->incr($key);
        return $key;
    }


    public function hasExports($programUrl, $collection)
    {
        $counter = $this->redis->get($this->_getExportKey($programUrl, $collection));
        if ($counter == null) {
            return false;
        }
        return ($counter > 0);
    }


}