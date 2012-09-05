<?php
App::uses('MongoModel', 'Model');
App::uses('DialogueHelper', 'Lib');
/**
 * Program Model
 *
 */
class History extends MongoModel
{

    var $specific = true;    

    
/**
 * Display field
 *
 * @var string
 */
    //var $name = 'ParticipantStat';
    var $useDbConfig = 'mongo';    
    var $useTable    = 'history';
    
    public $findMethods = array(
        'participant' => true,
        'count' => true,
        'scriptFilter' => true,
        'all' => true,
        );
    
    
    public $fieldFilters = array(
        'message-direction'=>'message direction',
        'message-status'=>'message status',
        'timestamp-from'=>'date from',
        'timestamp-to'=>'date to',
        'participant-phone'=>'participant phone',
        'message-content'=>'message content',
        'dialogue-id'=>'dialogue' 
        );
    
    public $typeConditionFilters = array(
        'incoming'=>'incoming',
        'outgoing'=>'outgoing',
        );
    
    public $statusConditionFilters = array(
        'failed'=>'failed',
        'delivered'=>'delivered',
        'pending'=>'pending',
        );
    
    
    public function __construct($id = false, $table = null, $ds = null)
    {
    	    parent::__construct($id, $table, $ds);
    	    
    	    $this->DialogueHelper = new DialogueHelper();
    }
    
    
    public function _findParticipant($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions'] = array('participant-phone' => $query['phone']);
            return $query;
        }
        return $results;
    }
    
    
    public function _findCount($state, $query, $results = array())
    {
        if ($state === 'before') {
            
            $db = $this->getDataSource();
            if (empty($query['fields'])) {
                $query['fields'] = $db->calculate($this, 'count');
            } elseif (is_string($query['fields'])  && !preg_match('/count/i', $query['fields'])) {
                $query['fields'] = $db->calculate($this, 'count', array(
                    $db->expression($query['fields']), 'count'
                    ));
            }
            $query['order'] = false;
            
            return $query;
        } elseif ($state === 'after') {
            foreach (array(0, $this->alias) as $key) {
               if (isset($results[0][$key]['count'])) {
                        if (($count = count($results)) > 1) {
                            return $count;
                        } else {
                            return intval($results[0][$key]['count']);
                        }
                    }
                }
            
            return false;
        }
    }    


    public function _findScriptFilter($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions'] = array(
            	    	    'message-direction' => 'incoming',
            	    	    'matching-answer' => null
            	    );
            return $query;
        }

        return $results;
    }
    

}
