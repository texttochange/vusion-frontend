<?php
App::uses('ProgramSpecificMongoModel', 'Model');
App::uses('Dialogue', 'Model');
App::uses('UnattachedMessage', 'Model');
App::uses('DialogueHelper', 'Lib');


class Schedule extends ProgramSpecificMongoModel
{
    
    var $name = 'Schedule';
    
    
    function getModelVersion()
    {
        return '1';
    }
    
    
    function getRequiredFields($objectType='dialogue-schedule')
    {
        if ($objectType=='dialogue-schedule' or $objectType =='reminder-schedule' or $objectType =='deadline-schedule'){
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
                'type-content',
                'date-time',
                'content',
                );
        } elseif ($objectType=='action-schedule'){
            return array(
                'participant-phone',
                'date-time',
                'action',
                );
        }
        throw new Exception("Object-type not supported:".$objectType);
        
    }
    
    
    public $findMethods = array(
        'soon' => true,
        'summary' => true,
        'count' => true);
    
    
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
    }
    
    
    //Patch the missing callback for deleteAll in Behavior
    public function deleteAll($conditions, $cascade = true, $callback = false)
    {
        parent::deleteAll($conditions, $cascade, $callback);
        $this->flushCached();
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
    
    
    public function getUniqueParticipantPhone($options=array())
    {
        $cursor = $this->getUniqueParticipantPhoneCursor();
        if (isset($options['cursor']) && $options['cursor']) {
            return $cursor;
        } 
        $results = array();
        foreach ($cursor as $item) {
            $results[] = $item['_id'];
        }
        return $results;
    }
    
    
    public function getUniqueParticipantPhoneCursor()
    {
        $pipeline = array(array('$group' => array('_id' => '$participant-phone')));
        $mongo = $this->getDataSource();
        return $mongo->aggregateCursor($this, $pipeline);
    }
    
    
    protected function getDialogueName($dialogueId, $activeDialogues)
    {
        foreach($activeDialogues as $activeDialogue) {
            if ($dialogueId == $activeDialogue['dialogue-id']) {
                return $activeDialogue['Dialogue']['name'];
            }
        }
        return __("Error: dialogue not found");
    }
    
    
    public function getParticipantSchedules($phone, $dialoguesInteractionsContent)
    {
        $schedules = $this->find('all', array(
            'conditions' => array('participant-phone' => $phone),
            'order' => array('date-time' => 'asc')
            ));
        
        
        foreach($schedules as &$schedule) {
            if (isset($schedule['Schedule']['interaction-id'])) {
                if (isset($dialoguesInteractionsContent[$schedule['Schedule']['dialogue-id']]['interactions'][$schedule['Schedule']['interaction-id']])) {
                    $schedule['Schedule']['content'] = $dialoguesInteractionsContent[$schedule['Schedule']['dialogue-id']]['interactions'][$schedule['Schedule']['interaction-id']];
                } else {
                    $schedule['Schedule']['content'] = 'unknown interaction';
                }
            }
            elseif (isset($schedule['Schedule']['unattach-id'])) {
                $unattachedMessage = $this->UnattachedMessage->read(null, $schedule['Schedule']['unattach-id']);
                if (isset($unattachedMessage['UnattachedMessage']['content']))
                    $schedule['Schedule']['content'] = '"'.$unattachedMessage['UnattachedMessage']['content'].'"';
            }
            if ($schedule['Schedule']['object-type']=='action-schedule') {
                $details = $schedule['Schedule']['action']['type-action'];
                
                if ($schedule['Schedule']['action']['type-action'] == 'enrolling') {
                    $details = $details." in ".$dialoguesInteractionsContent[$schedule['Schedule']['action']['enroll']]['name'];
                }
                $schedule['Schedule']['content'] = $details;
            }
        }
        return $schedules;
    }
    
    
    public function summary($dateTime)
    {
        $defaultDialogueConditions = array('object-type'=>'dialogue-schedule');
        
        $defaultUnattachConditions = array();
        
        if (isset($dateTime)) {
            $dateCondition = array('date-time' => array('$lt' => $dateTime->format(DateTime::ISO8601)));
            $defaultDialogueConditions += $dateCondition;
            $defaultUnattachConditions += $dateCondition;
        }
        
        $scriptQuery = array(
            'key' => array(
                'dialogue-id' => true,                
                'interaction-id' => true,
                'date-time' => true,
                'object-type'=>true,
                ),
            'initial' => array('csum' => 0),
            'reduce' => 'function(obj, prev){prev.csum+=1;}',
            'options' => array( 'condition'=> $defaultDialogueConditions),
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
            'options' => array( 'condition'=> $defaultUnattachConditions)
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
                $interaction = $activeInteractions[$schedule['interaction-id']];
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
    
    
    public function countScheduleFromUnattachedMessage($unattach_id)
    {
        $scheduleCount = $this->find('count', array(
            'conditions' => array(
                'unattach-id' => $unattach_id)));
        
        return $scheduleCount; 
    }
    
    public $filterFields = array();
    
}
