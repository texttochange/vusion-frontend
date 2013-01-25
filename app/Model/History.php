<?php
App::uses('MongoModel', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('FilterException', 'Lib');


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

    #Filter variables and functions
    public $filterFields = array(
        'message-direction' => array( 
            'label' => 'message direction',
            'operators' => array(
                'is' => array(
                    'label' => 'is',
                    'parameter-type' => 'message-direction'),
                'not-is' => array(
                    'label' => 'is not',
                    'parameter-type' => 'message-direction'))),
        'message-status' => array(
            'label' => 'message status',
            'operators' => array(
                'is' => array(
                    'label' => 'is',
                    'parameter-type' => 'message-status'),
                'not-is' => array(
                    'label' => 'is not',
                    'parameter-type' => 'message-status'))),
        'date' => array(
            'label' => 'date',
            'operators' => array(
                'from' => array(
                    'label' => 'since',
                    'parameter-type' => 'date'),
                'to' => array(
                    'label' => 'until',
                    'parameter-type' => 'date'))),
        'participant-phone' => array(
            'label' => 'participant phone',
            'operators' => array(
                'start-with' => array(
                    'label' => 'stats with',
                    'parameter-type' => 'text'),
                'equal-to' => array(
                    'label' => 'equal to',
                    'parameter-type' => 'text'),
                'start-with-any' => array(
                    'label' => 'starts with any of',
                    'parameter-type' => 'text'))),
        'message-content' => array(
            'label' => 'message content',
            'operators' => array(
                'equal-to' => array(
                    'label' => 'equals',
                    'parameter-type' => 'text'),
                'contain' => array(
                    'label' => 'contains',
                    'parameter-type' => 'text'),
                'has-keyword' => array(
                    'label' => 'has keyword',
                    'parameter-type' => 'text'))), 
        'dialogue-source' => array(
            'label' => 'dialogue source',
            'operators' => array(
                'is' => array(
                    'label' => 'is',
                    'parameter-type' => 'dialogue'))),
        'interaction-source' => array(
            'label' => 'interaction source',
            'operators' => array(
                'is' => array(
                    'label' => 'is',
                    'parameter-type' => 'interaction'))),
        'answer' => array(
            'label' => 'answers',
            'operators' => array(
                'matching' => array(
                    'label' => 'matching one question',
                    'parameter-type' => 'none'),
                'not-matching' => array(
                    'label' => 'not matching any question',
                    'parameter-type' => 'none')
                )) 
        );

    public $filterOperatorOptions = array(
        'all' => 'all',
        'any' => 'any'
        );

    public $filterMessageDirectionOptions = array(
        'incoming'=>'incoming',
        'outgoing'=>'outgoing',
        );

    
    public $filterMessageStatusOptions = array(
        'failed'=>'failed',
        'delivered'=>'delivered',
        'pending'=>'pending',
        'ack' => 'ack'
        );


    public function validateFilter($filterParam)
    {
        if (!isset($filterParam[1])) {
            throw new FilterException("Field is missing.");
        }

        if (!isset($this->filterFields[$filterParam[1]])) {
            throw new FilterException("Field '".$filterParam[1]."' is not supported.");
        }

        if (!isset($filterParam[2])) {
            throw new FilterException("Operator is missing for field '".$filterParam[1]."'.");
        }
        
        if (!isset($this->filterFields[$filterParam[1]]['operators'][$filterParam[2]])) {
            throw new FilterException("Operator '".$filterParam[2]."' not supported for field '".$filterParam[1]."'.");
        }

        if (!isset($this->filterFields[$filterParam[1]]['operators'][$filterParam[2]]['parameter-type'])) {
            throw new FilterException("Operator type missing '".$filterParam[2]."'.");
        }
        
        if ($this->filterFields[$filterParam[1]]['operators'][$filterParam[2]]['parameter-type'] != 'none' && !isset($filterParam[3])) {
            throw new FilterException("Parameter is missing for field '".$filterParam[1]."'.");
        }
    }


    public function fromFilterToQueryConditions($filter, $conditions = array()) {
        
        foreach($filter['filter_param'] as $filterParam) {
        
            $condition = null;
            
            $this->validateFilter($filterParam);
            
            if ($filterParam[1] == 'message-direction' or $filterParam[1] == 'message-status') {
                if ($filterParam[2] == 'is') {
                    $condition[$filterParam[1]] = $filterParam[3];
                } elseif ($filterParam[2] == 'not-is') {
                    $condition[$filterParam[1]] = array('$ne' => $filterParam[3]);
                }
            } elseif ($filterParam[1] == 'date') {
                if ($filterParam[2] == 'from') { 
                    $condition['timestamp']['$gt'] = $this->DialogueHelper->ConvertDateFormat($filterParam[3]);
                } elseif ($filterParam[2] == 'to') {
                    $condition['timestamp']['$lt'] = $this->DialogueHelper->ConvertDateFormat($filterParam[3]);
                }
            } elseif ($filterParam[1] == 'participant-phone') {
                if ($filterParam[2] == 'equal-to') {
                    $condition['participant-phone'] = $filterParam[3];                   
                } elseif ($filterParam[2] == 'start-with') {
                    $condition['participant-phone'] = new MongoRegex("/^\\".$filterParam[3]."/");
                } elseif ($filterParam[2] == 'start-with-any') {
                    $phoneNumbers = explode(",", str_replace(" ", "", $filterParam[3]));
                    if ($phoneNumbers) {
                        if (count($phoneNumbers) > 1) {
                            $or = array();
                            foreach ($phoneNumbers as $phoneNumber) {
                                $regex = new MongoRegex("/^\\".$phoneNumber."/");
                                $or[] = array('participant-phone' => $regex);
                            }
                            $condition['$or'] = $or;
                        } else {
                            $condition['participant-phone'] = new MongoRegex("/^\\".$phoneNumbers[0]."/");
                        }
                    }   
                }
            } elseif ($filterParam[1] == 'message-content') {
                if ($filterParam[2] == 'equal-to') {
                    $condition['message-content'] = $filterParam[3];
                } elseif ($filterParam[2] == 'contain') {
                     $condition['message-content'] = new MongoRegex("/".$filterParam[3]."/i");
                } elseif ($filterParam[2] == 'has-keyword') {
                    $condition['message-content'] = new MongoRegex("/^".$filterParam[3]."($| )/i");
                }
            } elseif ($filterParam[1] == 'dialogue-source') {
                if ($filterParam[2] == 'is') {
                    $condition['dialogue-id'] = $filterParam[3];    
                }
            } elseif ($filterParam[1] == 'interaction-source') {
                if ($filterParam[2] == 'is') {
                    $condition['interaction-id'] = $filterParam[3];    
                }
            } elseif ($filterParam[1] == 'answer') {
                if ($filterParam[2] == 'matching') {
                    $condition['message-direction'] = 'incoming';
                    $condition['matching-answer'] = array('$ne' => null);                    
                } elseif ($filterParam[2] == 'not-matching') {
                    $condition['message-direction'] = 'incoming';
                    $condition['matching-answer'] = null;                    
                }
            }
            
            if ($filter['filter_operator'] == "all") {
                if (count($conditions) == 0) {
                    $conditions = $condition;
                } elseif (!isset($conditions['$and'])) {
                    $conditions = array('$and' => array($conditions, $condition));
                } else {
                    array_push($conditions['$and'], $condition);
                }
            }  elseif ($filter['filter_operator'] == "any") {
                if (count($conditions) == 0) {
                    $conditions = $condition;
                } elseif (!isset($conditions['$or'])) {
                    $conditions = array('$or' => array($conditions, $condition));
                } else {
                    array_push($conditions['$or'], $condition);
                }
            }
        }
        
        return $conditions;
    }


}
