<?php
App::uses('MongoModel', 'Model');
App::uses('Dialogue', 'Model');
App::uses('UnattachedMessage', 'Model');
App::uses('DialogueHelper', 'Lib');
/**
 * Program Model
 *
 */
class Schedule extends MongoModel
{

    var $specific = true;
    var $useDbConfig = 'mongo';
    
     function getModelVersion()
    {
        return '1';
    }

    function getRequiredFields($objectType='dialogue-schedule')
    {
        if ($objectType=='dialogue-schedule'){
            return array(
                'participant-phone',
                'dialogue-id',
                'interaction-id',
                'date-time'
                );
        } elseif ($objectType=='unattach-schedule'){
            return array(
                'participant-phone',
                'unattach-id',
                'date-time'
                );
        } elseif ($objectType=='feedback-schedule'){
            return array(
                'participant-phone',
                'content-type'
                );
        }
        throw new Exception("Object-type not supported:".$objectType);
        
    }

    public $findMethods = array(
        'soon' => true,
        'count' => true,
        'summary' => true
        );
    
    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        
        $options                 = array('database'=>$id['database']);
        //$this->Dialogue          = new Dialogue($options);
        $this->UnattachedMessage = new UnattachedMessage($options);
        $this->DialogueHelper    = new DialogueHelper();
    }
    
     
    protected function _findSoon($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['order']['date-time'] = 'asc';
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
                'date-time' => true,
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
                'date-time' => true,
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
        if ($a['date-time'] == $b['date-time'])
    	    return 0;
        return ($a['date-time']<$b['date-time']) ? -1 : 1;
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
    
    
    public function generateSchedule($schedules,$activeInteractions)
    {
        foreach ($schedules as &$schedule) {
            if (isset($schedule['interaction-id'])) {
                $interaction = $this->DialogueHelper->getInteraction(
                    $activeInteractions,
                    $schedule['interaction-id']
                    );
                if (isset($interaction['content']))
                    $schedule['content'] = $interaction['content'];
            }
            elseif (isset($schedule['unattach-id'])) {
                $unattachedMessage = $this->UnattachedMessage->read(null, $schedule['unattach-id']);
                if (isset($unattachedMessage['UnattachedMessage']['content']))
                    $schedule['content'] = $unattachedMessage['UnattachedMessage']['content'];
            }
        }
        return $schedules;
    }


}
