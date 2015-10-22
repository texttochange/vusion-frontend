<?php
App::uses('ProgramSpecificMongoModel', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('FilterException', 'Lib');
App::uses('VusionConst', 'Lib');
App::uses('UnattachedMessage', 'Model');
App::uses('Participant','Model');


class History extends ProgramSpecificMongoModel
{
    
    var $name     = 'History';  
    var $useTable = 'history';
    
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
            'nack' => array('failure-reason'),
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
        
        if ($object['object-type'] == 'datepassed-marker-history') {
            $fields = array_merge($fields, array('message-status', 'unattach-id'));
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
        
        if ($object['object-type'] == 'datepassed-marker-history') {
            $object = array_merge($object, array('message-status' => 'datepassed-marker'));
        }
        
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
        $this->Behaviors->load('FilterMongo');
    }
    
    public function initializeDynamicTable($forceNew=false)
    {
        parent::initializeDynamicTable();
        $this->UnattachedMessage = ProgramSpecificMongoModel::init(
            'UnattachedMessage', $this->databaseName, $forceNew);
        $this->Participant = ProgramSpecificMongoModel::init(
            'Participant', $this->databaseName, $forceNew);
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
            $query['conditions']['participant-phone'] = $query['phone'];
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
    
    
    public function getParticipantHistory($phone, $dialoguesInteractionsContent, $from=null)
    {
        $conditions = array('phone' => $phone);
        if (isset($from)) {
            $conditions['conditions'] = array('timestamp' => array('$gte' => $from));
        }
        $histories = $this->find('participant', $conditions);
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
                if (isset($history['History']['unattach-id'])) {
                    $separateMessageName = $this->UnattachedMessage->getNameById($history['History']['unattach-id']);
                    $history['History']['details'] = $separateMessageName;
                } else if (isset($dialoguesInteractionsContent[$history['History']['dialogue-id']]['interactions'][$history['History']['interaction-id']])) {
                    $history['History']['details'] = $dialoguesInteractionsContent[$history['History']['dialogue-id']]['interactions'][$history['History']['interaction-id']];
                } else {
                    $history['History']['details'] = 'unknown interaction';
                }
            }
        }
        return $histories;
    }
    
    public function _aggregate($since, $x, $y) 
    {
        $aggregates = array();
        $pipeline = array(
            array('$match' =>array(
                'timestamp' => array('$gte' => $since),
                'message-direction' => array('$exists' => 1))),
            array('$group' => array(
                '_id' => array(
                    'year' =>  array('$substr' => array('$timestamp', 0, 4)),
                    'month' => array('$substr' => array('$timestamp', 5, 2)),
                    'day' => array('$substr' => array('$timestamp', 8, 2)),
                    'direction' => '$message-direction'),
                $y => array('$sum' => 1))),
            array('$project' => array(
                '_id'=> 0,
                $x => array('$concat' => array('$_id.year', '-', '$_id.month', '-', '$_id.day')),
                $y => 1,
                'direction' => '$_id.direction')),
            array(
                '$sort' => array($x => 1))
            );
        $mongo = $this->getDataSource();
        $cursor = $mongo->aggregateCursor($this, $pipeline);
        foreach($cursor as $aggregate) {
            $aggregates[] = $aggregate;
        }
        return $aggregates;
    }

    public function aggregateMg($since) 
    {
        $aggregates = $this->_aggregate($since, 'date', 'value');
        $incoming = array_values(array_filter($aggregates, function($el) { return ($el['direction'] == 'incoming');}));
        $outgoing = array_Values(array_filter($aggregates, function($el) { return ($el['direction'] == 'outgoing');}));
        return $aggregates = array($incoming, $outgoing);        
    }


    public function aggregateNvd3($since) 
    {
        $aggregates = $this->_aggregate($since, 'x', 'y');
        $incoming = array_values(array_filter($aggregates, function($el) { return ($el['direction'] == 'incoming');}));
        $outgoing = array_Values(array_filter($aggregates, function($el) { return ($el['direction'] == 'outgoing');}));
        return $aggregates = array(
            array(
                'key'=> __('received'),
                'values' => $incoming),
            array(
                'key'=> __('sent'),
                'values' => $outgoing));
    }
    

    public function getMostActive($since, $id, $x, $y)
    {
        $aggregates = array();
        $pipeline = array(
            array('$match' => array(
                'message-direction' => 'incoming',
                $id => array('$exists' => true))),
            array('$group' => array(
                '_id' => "$$id",
                $y => array('$sum' => 1))),
            array('$project' => array(
                '_id'=> 0,
                $x => '$_id',
                $y => 1)),
            array('$sort' => array(
                $y => -1))
            );
        if ($since != null) {
            $pipeline[0]['$match']['timestamp'] = array('$gte' => $since);
        }
        $mongo = $this->getDataSource();
        $cursor = $mongo->aggregateCursor($this, $pipeline);
        foreach($cursor as $aggregate) {
            $aggregates[] = $aggregate;
        }
        return $aggregates;   
    }

    //Filter variables and functions
    public $filterFields = array(
        'participant-phone' => array(
            'label' => 'participant phone',
            'operators' => array(
                'start-with' => array(
                    'parameter-type' => 'text'),
                'equal-to' => array(
                    'parameter-type' => 'text'),
                'start-with-any' => array(
                    'parameter-type' => 'text'),
                'simulated' => array(
                    'parameter-type' => 'none'))),
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
        'nack' => 'nack',
        'forwarded' => 'forwarded',
        'received' => 'received',
        'no-credit' => 'no-credit',
        'no-credit-timeframe' => 'no-credit-timeframe',
        'missing-data' => 'missing-data',
        'received' => 'received',
        'forwarded' => 'forward'
        );
    
    
    public function fromFilterToQueryCondition($filterParam) 
    {
        
        $condition = array();
        
        if ($filterParam[1] == 'message-direction' or $filterParam[1] == 'message-status') {
            if ($filterParam[3]) {
                if ($filterParam[2] == 'is') {
                    $condition[$filterParam[1]] = $filterParam[3];
                } elseif ($filterParam[2] == 'not-is') {
                    $condition[$filterParam[1]] = array('$ne' => $filterParam[3]);
                }
            } else {
                $condition[$filterParam[1]] = '';
            }
        } elseif ($filterParam[1] == 'date') {
            if ($filterParam[3]) {
                if ($filterParam[2] == 'from') { 
                    $condition['timestamp']['$gt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
                } elseif ($filterParam[2] == 'to') {
                    $condition['timestamp']['$lt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
                }
            } else {
                $condition['timestamp'] = '';
            }
        } elseif ($filterParam[1] == 'participant-phone') {
            if ($filterParam[2] == 'simulated') {
                $condition['participant-phone'] = array('$regex' => "^\#"); 
            } elseif ($filterParam[3]) {
                if ($filterParam[2] == 'equal-to') {
                    $condition['participant-phone'] = $filterParam[3];                   
                } elseif ($filterParam[2] == 'start-with') {
                    $condition['participant-phone'] = array('$regex' => "^\\".$filterParam[3]);
                } elseif ($filterParam[2] == 'start-with-any') {
                    $phoneNumbers = explode(",", str_replace(" ", "", $filterParam[3]));
                    $condition = $this->_createOrRegexQuery('participant-phone', $phoneNumbers, "\\", '', 'i'); 
                }
            } else {
                $condition['participant-phone'] = '';
            }
        } elseif ($filterParam[1] == 'message-content') {
            if ($filterParam[3]) {
                if ($filterParam[2] == 'equal-to') {
                    $condition['message-content'] = $filterParam[3];
                } elseif ($filterParam[2] == 'contain') {
                    $condition['message-content'] = array('$regex' => $filterParam[3], '$options' => 'i');
                } elseif ($filterParam[2] == 'has-keyword') {
                    $condition['message-content'] = array('$regex' => "^".$filterParam[3]."($| )", '$options' => 'i');
                } elseif ($filterParam[2] == 'has-keyword-any') {
                    $keywords  = explode(",", str_replace(" ", "", $filterParam[3]));
                    $condition = $this->_createOrRegexQuery('message-content', $keywords, null, '($| )', 'i');
                }
            } else {
                $condition['message-content'] = '';
            }
        } elseif ($filterParam[1] == 'separate-message') {
            if ($filterParam[3]) {
                if ($filterParam[2] == 'equal-to') {
                    $condition['unattach-id'] = $filterParam[3];    
                } 
            } else {
                $condition['unattach-id'] = '';
            }
        } elseif ($filterParam[1] == 'dialogue-source') {
            if ($filterParam[2] == 'is-any') {
                $condition['dialogue-id'] = array('$exists' => true);    
            } elseif ($filterParam[2] == 'not-is-any') {
                $condition['dialogue-id'] = array('$exists' => false);
            } elseif (isset($filterParam[3]) && $filterParam[3]) {
                if ($filterParam[2] == 'is') {
                    $condition['dialogue-id'] = $filterParam[3];    
                } elseif ($filterParam[2] == 'not-is') {
                    $condition['dialogue-id'] = array('$ne' => $filterParam[3]);
                }
            } else {
                $condition['dialogue-id'] = '';
            }
        } elseif ($filterParam[1] == 'interaction-source') {
            if ($filterParam[2] == 'is-any') {
                $condition['interaction-id'] = array('$exists' => true);    
            } elseif ($filterParam[2] == 'not-is-any') {
                $condition['interaction-id'] = array('$exists' => false);
            } elseif (isset($filterParam[3]) && $filterParam[3]) {
                if ($filterParam[2] == 'is') {
                    $condition['interaction-id'] = $filterParam[3];
                } elseif ($filterParam[2] == 'not-is') {
                    $condition['interaction-id'] = array('$ne' => $filterParam[3]);
                }
            } else {
                $condition['interaction-id'] = '';
            }
        } elseif ($filterParam[1] == 'request-source') {
            if ($filterParam[2] == 'is-any') {
                $condition['request-id'] = array('$exists' => true);    
            } elseif ($filterParam[2] == 'not-is-any') {
                $condition['request-id'] = array('$exists' => false);
            } elseif (isset($filterParam[3]) && $filterParam[3]) {
                if ($filterParam[2] == 'is') {
                    $condition['request-id'] = new MongoId($filterParam[3]);
                } elseif ($filterParam[2] == 'not-is') {
                    $condition['request-id'] = array('$ne' => new MongoId($filterParam[3]));
                }
            } else {
                $condition['request-id'] = '';
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
        
        
        return $condition;
    } 
    
    
    protected function _createOrRegexQuery($field, $choices, $prefix=null, $suffix=null, $options='') 
    {
        $query = array();
        if (count($choices) > 1) {
            $or = array();
            foreach ($choices as $choice) {
                $regex = array( '$regex' => '^'.$prefix.$choice.$suffix, '$options' => $options);
                $or[]  = array($field => $regex);
            }
            $query['$or'] = $or;
        } else {
            $query[$field] = array( '$regex' => '^'.$prefix.$choices[0].$suffix, '$options' => $options);
        }
        return $query;
    }
    
    
    public function countUnattachedMessages($unattachId, $messageStatus = null)
    {
        $conditions = array(
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
    
    
    public function getParticipantLabels($histories)
    {
        foreach ($histories as &$history) {
            $phone = $history['History']['participant-phone'];
            $participant = $this->Participant->find('first', array(
                'conditions' => array('phone' => $phone)));
            if ($participant) {
                $participantLabels = $participant['Participant']['profile'];
                $history['History']['participant-labels'] = $participantLabels;
            }
        }
        return $histories;
    }

    
}
