<?php
App::uses('MongoModel', 'Model');
/**
 * Program Model
 *
 */
class Schedule extends MongoModel
{

    var $specific = true;
    var $useDbConfig = 'mongo';
    
    public $findMethods = array(
        'soon' => true,
        'count' => true,
        'summary' => true
        );
     
     protected function _findSoon($state, $query, $results = array())
     {
        if ($state == 'before') {
            $query['order']['datetime'] = 'asc';
            $query['limit'] = 10;
            return $query;
        }
        return $results;
    }


    public function summary()
    {
        $scriptQuery = array(
            'key' => array(
                'dialogue-id' => true,                
                'interaction-id' => true,
                'datetime' => true,
                ),
            'initial' => array('csum' => 0),
            'reduce' => 'function(obj, prev){prev.csum+=1;}',
            );

        $tmp = $this->getDataSource()->group($this, $scriptQuery);

        $scriptResults = array_filter(
        	$tmp['retval'], 
        	array($this, "_interaction")
        	);

        $unattachedQuery = array(
            'key' => array(
                'unattach-id' => true,
                'datetime' => true,
                ),
            'initial' => array('csum' => 0),
            'reduce' => 'function(obj, prev){prev.csum+=1;}',
            );
        
        
        $tmp = $this->getDataSource()->group($this, $unattachedQuery);

        $unattachedResults = array_filter(
        	$tmp['retval'], 
        	array($this, "_unattached")
        	);

        $summary = array_merge($scriptResults, $unattachedResults);
        uasort($summary, array($this, '_compareSchedule'));
        return $summary;
        
    }

    private function _compareSchedule($a, $b)
    {
        if ($a['datetime'] == $b['datetime'])
    	    return 0;
        return ($a['datetime']<$b['datetime']) ? -1 : 1;
    }


    private function _interaction($var) 
    {
    	    return (isset($var['dialogue-id']) && $var['dialogue-id']!=null &&
    	    	    isset($var['interaction-id']) && $var['interaction-id']!=null);
    }


    private function _unattached($var)
    {
        return (isset($var['unattach-id']) && $var['unattach-id']!=null);
    }


}
