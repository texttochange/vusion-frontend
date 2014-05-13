<?php
App::uses('MongoModel', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('FilterException', 'Lib');
App::uses('VusionConst', 'Lib');


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
        return '4';
    }
    
    
    // TODO refactor validation to have a more cakelike model description
    function getRequiredFields($object)
    {
        $fields = array(
            'timestamp',
            'participant-phone',
            'participant-session-id');   
        
        $MESSAGE_FIELDS = array(
            'message-content',
            'message-direction',
            'message-credits');
        
        $SPECIFIC_DIRECTION_FIELDS = array(
            'outgoing' => array('message-id', 'message-status'),
            'incoming' => array('matching-answer'));
        
        $SPECIFIC_STATUS_FIELDS = array(
            'failed' => array('failure-reason'),
            'pending' => array(),
            'delivered' => array(),
            'ack' => array(),
            'nack' => array(),
            'no-credit' => array(),
            'no-credit-timeframe' => array(),
            'missing-data' => array('missing-data'),
            'received' => array(),
            'forwarded' => array('forwards'));
        
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
            if (!in_array($field, $toCheck)) {
                unset($object[$field]);
            }
        }
        
        foreach ($toCheck as $field) {
            if (!isset($object[$field])) {
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

        $this->Behaviors->load('CachingCount', array(
            'redis' => Configure::read('vusion.redis'),
            'redisPrefix' => Configure::read('vusion.redisPrefix'),
            'cacheCountExpire' => Configure::read('vusion.cacheCountExpire')));
    }


    //Patch the missing callback for deleteAll in Behavior
    public function deleteAll($conditions, $cascade = true, $callback = false)
    {
        parent::deleteAll($conditions, $cascade, $callback);
        $this->flushCached();
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
    
    
    public function paginateCount($conditions, $recursive, $extra)
    {
        try{
            if (isset($extra['maxLimit'])) {
                $maxPaginationCount = 40;
            } else {
                $maxPaginationCount = $extra['maxLimit'];
            }
            
            $result = $this->count($conditions, $maxPaginationCount);
            if ($result == $maxPaginationCount) {
                return 'many';
            } else {
                return $result; 
            }            
        } catch (MongoCursorTimeoutException $e) {
            return 'many';
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
    
    
    //Filter variables and functions
    public $filterFields = array(
        'message-direction' => array( 
            'label' => 'message direction',
            'operators' => array(
                'is' => array(
                    'parameter-type' => 'message-direction'),
                'not-is' => array(
                    'parameter-type' => 'message-direction'))),
        'message-status' => array(
            'label' => 'message status',
            'operators' => array(
                'is' => array(
                    'parameter-type' => 'message-status'),
                'not-is' => array(
                    'parameter-type' => 'message-status'))),
        'date' => array(
            'label' => 'date',
            'operators' => array(
                'from' => array(
                    'parameter-type' => 'date'),
                'to' => array(
                    'parameter-type' => 'date'))),
        'participant-phone' => array(
            'label' => 'participant phone',
            'operators' => array(
                'start-with' => array(
                    'parameter-type' => 'text'),
                'equal-to' => array(
                    'parameter-type' => 'text'),
                'start-with-any' => array(
                    'parameter-type' => 'text'))),
        'separate-message' => array(
            'label' => 'separate message',
            'operators' => array(
                'equal-to' => array(
                    'parameter-type' => 'unattach-message'),                
                )),
        'message-content' => array(
            'label' => 'message content',
            'operators' => array(
                'equal-to' => array(
                    'parameter-type' => 'text'),
                'contain' => array(
                    'parameter-type' => 'text'),
                'has-keyword' => array(
                    'parameter-type' => 'text'),
                'has-keyword-any' => array(
                    'parameter-type' => 'text',
                    'parameter-validate' => VusionConst::KEYWORD_REGEX)
                )),
        'dialogue-source' => array(
            'label' => 'dialogue source',
            'operators' => array(
                'is' => array(
                    'parameter-type' => 'dialogue'),
                'not-is' => array(
                    'parameter-type' => 'dialogue'),
                'is-any' => array(
                    'parameter-type' => 'none'),
                'not-is-any' => array(
                    'parameter-type' => 'none'),
                )), 
        'interaction-source' => array(
            'label' => 'interaction source',
            'operators' => array(
                'is' => array(
                    'parameter-type' => 'interaction'),
                'not-is' => array(
                    'parameter-type' => 'interaction'),
                'is-any' => array(
                    'parameter-type' => 'none'),
                'not-is-any' => array(
                    'parameter-type' => 'none'),
                )),
        'request-source' => array(
            'label' => 'request source',
            'operators' => array(
                'is' => array(
                    'parameter-type' => 'request'),
                'not-is' => array(
                    'parameter-type' => 'request'),
                'is-any' => array(
                    'parameter-type' => 'none'),
                'not-is-any' => array(
                    'parameter-type' => 'none'),
                )),
        'answer' => array(
            'label' => 'answer',
            'operators' => array(
                'matching' => array(
                    'parameter-type' => 'none'),
                'not-matching' => array(
                    'label' => 'not matching any question',
                    'parameter-type' => 'none')
                )), 
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
        'ack' => 'ack',
        'forwarded' => 'forwarded',
        'received' => 'received',
        'no-credit' => 'no-credit',
        'no-credit-timeframe' => 'no-credit-timeframe',
        'missing-data' => 'missing-data',
        'received' => 'received',
        'forwarded' => 'forward'
        );
    
    
    public function validateFilter($filterParam)
    {
        if (!isset($filterParam[1])) {
            throw new FilterException(__("The filter's field is missing."));
        }
        
        if (!isset($this->filterFields[$filterParam[1]])) {
            throw new FilterException(__("The filter's field '%s' is not supported.", $filterParam[1]));
        }
        
        if (!isset($filterParam[2])) {
            throw new FilterException(__("The filter's operator is missing for field '%s'.", $filterParam[1]));
        }
        
        if (!isset($this->filterFields[$filterParam[1]]['operators'][$filterParam[2]])) {
            throw new FilterException(__("The filter's operator '%s' not supported for field '%s'.", $filterParam[2], $filterParam[1]));
        }
        
        $operator = $this->filterFields[$filterParam[1]]['operators'][$filterParam[2]];
        
        if ($operator['parameter-type'] != 'none' && !isset($filterParam[3])) {
            throw new FilterException(__("The filter's parameter is missing for field '%s'.", $filterParam[1]));
        }
        
        if (isset($operator['parameter-validate'])) {
            if (!preg_match($operator['parameter-validate'], $filterParam[3])) {
                throw new FilterException(__("The filter's parameter value '%s' is not valid.", $filterParam[3]));
            }
        }
    }
    
    
    public function fromFilterToQueryConditions($filter, $conditions = array()) {
        
        foreach ($filter['filter_param'] as $filterParam) {
            
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
                    $condition['timestamp']['$gt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
                } elseif ($filterParam[2] == 'to') {
                    $condition['timestamp']['$lt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
                }
            } elseif ($filterParam[1] == 'participant-phone') {
                if ($filterParam[2] == 'equal-to') {
                    $condition['participant-phone'] = $filterParam[3];                   
                } elseif ($filterParam[2] == 'start-with') {
                    $condition['participant-phone'] = new MongoRegex("/^\\".$filterParam[3]."/");
                } elseif ($filterParam[2] == 'start-with-any') {
                    $phoneNumbers = explode(",", str_replace(" ", "", $filterParam[3]));
                    $condition = History::_createOrRegexQuery('participant-phone', $phoneNumbers, "\\", "/"); 
                }
            } elseif ($filterParam[1] == 'message-content') {
                if ($filterParam[2] == 'equal-to') {
                    $condition['message-content'] = $filterParam[3];
                } elseif ($filterParam[2] == 'contain') {
                    $condition['message-content'] = new MongoRegex("/".$filterParam[3]."/i");
                } elseif ($filterParam[2] == 'has-keyword') {
                    $condition['message-content'] = new MongoRegex("/^".$filterParam[3]."($| )/i");
                } elseif ($filterParam[2] == 'has-keyword-any') {
                    $keywords  = explode(",", str_replace(" ", "", $filterParam[3]));
                    $condition = History::_createOrRegexQuery('message-content', $keywords, null, "($| )/i");
                }
            } elseif ($filterParam[1] == 'separate-message') {
                if ($filterParam[2] == 'equal-to') {
                    $condition['unattach-id'] = $filterParam[3];    
                }
            } elseif ($filterParam[1] == 'dialogue-source') {
                if ($filterParam[2] == 'is') {
                    $condition['dialogue-id'] = $filterParam[3];    
                } elseif ($filterParam[2] == 'not-is') {
                    $condition['dialogue-id'] = array('$ne' => $filterParam[3]);
                } elseif ($filterParam[2] == 'is-any') {
                    $condition['dialogue-id'] = array('$exists' => true);    
                } elseif ($filterParam[2] == 'not-is-any') {
                    $condition['dialogue-id'] = array('$exists' => false);
                }
            } elseif ($filterParam[1] == 'interaction-source') {
                if ($filterParam[2] == 'is') {
                    $condition['interaction-id'] = $filterParam[3];
                } elseif ($filterParam[2] == 'not-is') {
                    $condition['interaction-id'] = array('$ne' => $filterParam[3]);
                } elseif ($filterParam[2] == 'is-any') {
                    $condition['interaction-id'] = array('$exists' => true);    
                } elseif ($filterParam[2] == 'not-is-any') {
                    $condition['interaction-id'] = array('$exists' => false);
                }
            } elseif ($filterParam[1] == 'request-source') {
                if ($filterParam[2] == 'is') {
                    $condition['request-id'] = new MongoId($filterParam[3]);
                } elseif ($filterParam[2] == 'not-is') {
                    $condition['request-id'] = array('$ne' => new MongoId($filterParam[3]));
                } elseif ($filterParam[2] == 'is-any') {
                    $condition['request-id'] = array('$exists' => true);    
                } elseif ($filterParam[2] == 'not-is-any') {
                    $condition['request-id'] = array('$exists' => false);
                }
            } elseif ($filterParam[1] == 'answer') {
                if ($filterParam[2] == 'matching') {
                    $condition['message-direction'] =   'incoming';
                    $condition['matching-answer']   =   array('$ne' => null);                    
                } elseif ($filterParam[2] == 'not-matching') {
                    $condition['message-direction'] = 'incoming';
                    $condition['matching-answer']   = null;                    
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
            } elseif ($filter['filter_operator'] == "any") {
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
    
    
    protected function _createOrRegexQuery($field, $choices, $prefix=null, $suffix=null) 
    {
        $query = array();
        if (count($choices) > 1) {
            $or = array();
            foreach ($choices as $choice) {
                $regex = new MongoRegex("/^".$prefix.$choice.$suffix);
                $or[]  = array($field => $regex);
            }
            $query['$or'] = $or;
        } else {
            $query[$field] = new MongoRegex("/^".$prefix.$choices[0].$suffix);
        }
        return $query;
    }
    
    
    public function countUnattachedMessages($unattachId, $messageStatus = null)
    {
        $conditions = array(
            'message-direction' => 'outgoing',
            'unattach-id' => $unattachId);
        if ($messageStatus != null) {
            if (is_array($messageStatus)) {
                $statusConditions = array('message-status' => array('$in' => $messageStatus));
            } else {
                $statusConditions = array('message-status' => $messageStatus);
            }
            $conditions = array_merge($conditions, $statusConditions);
        } 
        $historyCount = $this->find('count', array('conditions' => $conditions));
        return $historyCount;
    }
    
    /*
    public function getCreditsFromHistory($conditions=array())
    {

        $defaultConditions = array(
                array('object-type'=> array(
                    '$in'=> $this->messageType,
                    )),
                array('message-status' => array(
                    '$nin' => array(
                        'missing-data','no-credit','no-credit-timeframe')
                    )),				            
                ); 
        
        if (!empty($conditions)) {
            $conditions = array('$and' => 
                array_merge($defaultConditions, $conditions));
        } else {
            $conditions = array('$and' => $defaultConditions);
        }

        $reduce = new MongoCode(
            "function(obj, prev){ 
                if (!obj['message-credits']) {
		            prev.credits += 1;
			    } else { 
				    prev.credits += obj['message-credits'];
			    }
			}");

        $query = array(
				'key' => array('message-direction' => true ),
				'initial' => array('credits' => 0),
				'reduce' => $reduce,
				'options' => array(
				    'condition' => $conditions
				    )
				);
		$mongo = $this->getDataSource();
		$resultGroup = $mongo->group($this, $query);
		$result = array(
		    'incoming' => 0,
		    'outgoing' => 0);
		if (isset($resultGroup['retval'])) {
		    foreach($resultGroup['retval'] as $messageCount) {
		        $result[$messageCount['message-direction']] = $messageCount['credits']; 
		    }
		}
		return $result;  
    }


    public static function isConditionTimeframeOneMonth($conditions, $now) 
    {
        if (!isset($conditions['date-from'])) {
            return false;
        } 
        $dateFrom = DialogueHelper::fromVusionDateToPhpDate($conditions['date-from']);

        if (!isset($conditions['date-to'])) {
            $dateTo = $now;
        } else {
            $dateTo = DialogueHelper::fromVusionDateToPhpDate($conditions['date-to']);
        }
        print_r($dateFrom);
        $diff = $dateFrom->diff($dateTo);

        $month = $diff->format('%m');
        print_r($month);
        return ($month < 1);
        
    }
    */
    
}
