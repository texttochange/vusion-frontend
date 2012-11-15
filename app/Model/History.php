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

    //var $name = 'ParticipantStat';
    var $useDbConfig = 'mongo';    
    var $useTable    = 'history';

    var $messageType = array(
        'dialogue-history',
        'request-history',
        'unattach-history',
        'unmatching-history');
   
    var $markerType = array(
        'datepassed-marker-history',
        'datepassed-action-marker-history',
        'oneway-marker-history');

    function getModelVersion()
    {
        return '2';
    }

    // TODO fail to express that a incoming message for dialogue id has 'matching-answer' field
    function getRequiredFields($object)
    {
        $fields = array(
             'timestamp',
             'participant-phone',
             'participant-session-id');   

        $MESSAGE_FIELDS = array(
            'message-content',
            'message-direction');

        $SPECIFIC_DIRECTION_FIELDS = array(
            'outgoing' => array('message-id', 'message-status'),
            'incoming' => array('matching-answer'));

        $SPECIFIC_STATUS_FIELDS = array(
            'failed' => array('failure-reason'),
            'pending' => array(),
            'delivered' => array());

        $OBJECT_WITH_DIALOGUE_REF = array(
            'dialogue-history',
            'oneway-marker-history',
            'datepassed-marker-history',
            );

        if (in_array($object['object-type'], $this->messageType)) {
            $fields = array_merge($fields, $MESSAGE_FIELDS);
            $fields = array_merge($fields, $SPECIFIC_DIRECTION_FIELDS[$object['message-direction']]);
            if (in_array('message-status', $fields)) {
                $fields = array_merge($fields, $SPECIFIC_STATUS_FIELDS[$object['message-status']]);
            }            
        };

        if (in_array($object['object-type'], $OBJECT_WITH_DIALOGUE_REF)) {
            $fields = array_merge($fields, array('dialogue-id', 'interaction-id'));
        } elseif ($object['object-type'] == 'unattach-history') {
            $fields = array_merge($fields, array('unattach-id'));
        } elseif ($object['object-type'] == 'request-history') {
            $fields = array_merge($fields, array('request-id'));
        }
        return $fields;
    }

    public function checkFields($object)
    {       
        $toCheck = array_merge($this->defaultFields, $this->getRequiredFields($object));
        foreach ($object as $field => $value) {
            if (!in_array($field, $toCheck)){
                unset($object[$field]);
            }
        }

        foreach ($toCheck as $field) {
            if (!isset($object[$field])){
                $object[$field] = null;
            }
        };

        return $object;
    }

    
    public $findMethods = array(
        'participant' => true,
        'count' => true,
        'scriptFilter' => true,
        'all' => true,
        );
    
    
    public $fieldFilters = array(
        'message-direction'=>'message direction',
        'message-status'=>'message status',
        'date-from'=>'date from',
        'date-to'=>'date to',
        'participant-phone'=>'participant phone',
        'message-content'=>'message content',
        'dialogue'=>'dialogue',
        'non-matching-answers' => 'non matching answers'
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
            $query['order']['timestamp'] = 'asc';
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
    

    public function getParticipantHistory($phone, $dialoguesInteractionsContent) {
         $histories   = $this->find('participant', array('phone' => $phone));
         return $this->addDialogueContent($histories, $dialoguesInteractionsContent);
    }

    public function addDialogueContent($histories, $dialoguesInteractionsContent)
    {
        foreach ($histories as &$history) {
            // manage old history objects
            if (!isset($history['History']['object-type'])) {
                continue;
            }   
            if (in_array($history['History']['object-type'], array('oneway-marker-history', 'datepassed-marker-history'))) {
                if (isset($dialoguesInteractionsContent[$history['History']['dialogue-id']]['interactions'][$history['History']['interaction-id']])) {
                    $history['History']['details'] = $dialoguesInteractionsContent[$history['History']['dialogue-id']]['interactions'][$history['History']['interaction-id']];
                } else {
                    $history['History']['details'] = 'unknown interaction';
                }
            }
         }
         return $histories;
    }

}
