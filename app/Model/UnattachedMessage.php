<?php
App::uses('MongoModel', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('DialogueHelper', 'Lib');


class UnattachedMessage extends MongoModel
{
    
    var $specific    = true;
    var $name        = 'UnattachedMessage';
    var $useDbConfig = 'mongo';
    var $useTable    = 'unattached_messages';
    
    var $mongoNoSetOperator = true;

    function getModelVersion()
    {
        return '3';
    }
    
    function getRequiredFields($objectType=null)
    {
        return array(
            'name',
            'send-to-type',
            'content',           
            'type-schedule',
            'fixed-time'
            );
    }
    
    var $matchFields = array(
        'send-to-match-operator',
        'send-to-match-conditions'
        );

    var $phoneFields = array(
        'send-to-phone'
        );
    
    var $participantPhoneRegex = '/^\+[0-9]*$/';

    public $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter a name for this separate message.'
                ),
            'isVeryUnique' => array(
                'rule' => 'isVeryUnique',
                'message' => 'This name already exists. Please choose another.'
                )
            ),
        'send-to-type' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please select a Send To option.'
                ),
            'allowedChoice' => array(
                'rule' => array('inList', array('all', 'match', 'phone')),
                'message' => 'Send To option not allowed.'
                ),
            ),
        'send-to-match-operator' => array(
            'notempty'=> array(
                'rule' => array('notempty'),
                'message' => 'Please select a condition.'
                ),
            'allowedChoice' => array(
                'rule' => array('inList', array('all', 'any')),
                'message' => 'Match operator not allowed.'
                )
            ),
        'send-to-match-conditions' => array(
            'allowChoice' => array(
                'rule' => array('conditions')
                )
            ),
        'send-to-phone' => array(
            'phoneList'=> array(
                'rule' => 'phoneList',
                'message' => 'Please enter a list of participant phone.'
                )),
        'content' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter some content for this message.'
                ),
            'notForbiddenApostrophe' => array(
                'rule' => 'notForbiddenApostrophe',
                'message' => 'The apostrophe used in this message is not valid.'
                ),
            ),
        'type-schedule' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please choose a type of schedule for this message.'
                )
            ),
        'fixed-time' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter a fixed time for this message.'
                ),
            'isNotPast' => array(
                'rule' => 'isNotPast',
                'required' => true,
                'message' => 'Fixed time cannot be in the past.'
                )
            )
        );
    
    var $findMethods = array(
        'all' => true,
        'first' => true,
        'count' => true,
        'future' => true);
    
    protected function _findFuture($state, $query, $results = array())
    {
        if ($state == 'before') {
            $programNow = $this->ProgramSetting->getProgramTimeNow();
            if ($programNow) {
                $query['conditions']['fixed-time'] = array('$gt' => $programNow->format("Y-m-d\TH:i:s"));
            }
            return $query;
        }
        return $results;
    }
    
    
    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        
        $options              = array('database'=>$id['database']);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->DialogueHelper = new DialogueHelper();
    }
    
    
    public function checkFields($object)
    {
        if (isset($object['object-type']))
            $toCheck = array_merge($this->defaultFields, $this->getRequiredFields($object['object-type']));
        else
            $toCheck = array_merge($this->defaultFields, $this->getRequiredFields());
        
        if (isset($object['send-to-type']) && $object['send-to-type'] == 'match') {
            $toCheck = array_merge($toCheck, $this->matchFields);
        }
        
        if (isset($object['send-to-type']) && $object['send-to-type'] == 'phone') {
            $toCheck = array_merge($toCheck, $this->phoneFields);
        }

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
    
    
    public function beforeValidate()
    {
        parent::beforeValidate();
        
        if ($this->data['UnattachedMessage']['type-schedule'] == 'immediately') {
            $now = $this->ProgramSetting->getProgramTimeNow();
            if (isset($now))
                $this->data['UnattachedMessage']['fixed-time'] = $now->format("Y-m-d\TH:i:s");            
        } elseif (isset($this->data['UnattachedMessage']['fixed-time'])) {
            //Convert fixed-time to vusion format
            $this->data['UnattachedMessage']['fixed-time'] = $this->DialogueHelper->convertDateFormat($this->data['UnattachedMessage']['fixed-time']);
        }       
        return true;           	
    }    
    
    public function isNotPast($check)
    {
        $programTimezone = $this->ProgramSetting->find('getProgramSetting', array('key' => 'timezone'));
        $fixedTimeDate = new DateTime($check['fixed-time'], timezone_open($programTimezone));
        return $this->ProgramSetting->isNotPast($fixedTimeDate);
    }
    
    
    public function isVeryUnique($check)
    {
        if ($this->id) {
            $conditions = array('id'=>array('$ne'=> $this->id),'name' => $check['name']);
        } else {
            $conditions = array('name' => $check['name']);
        }
        $result = $this->find('count', array(
            'conditions' => $conditions
            ));
        return $result < 1;
    }
    
    public function conditions($check)
    {
        $regex = '/^[a-zA-Z0-9\s]+(:[a-zA-Z0-9\s]+)?$/';
        
        if (!is_array($check['send-to-match-conditions'])) {
            return "Select conditions.";
        }
        
        foreach($check['send-to-match-conditions'] as $selector) {
            if (preg_match($regex, $selector)) {
                continue;
            } else {
                return "Incorrect tag or label.";
            }
        }
        return true;
    }
    
    public function matchOperator($check) 
    {   
        if ($this->data['UnattachedMessage']['send-to-type'] == 'all') {
            return true;
        }
        if (!isset($check)) {
            return false;
        }
        return true;        
    }


    public function phoneList($check)
    {
        if (!is_array($check['send-to-phone'])) {
            return false;
        }
        foreach($check['send-to-phone'] as $participantPhone) {
            if (!preg_match($this->participantPhoneRegex, $participantPhone)) {
                return false;
            }
        }
        return true;

    }

    
    public function notForbiddenApostrophe($check)
    {
        if (preg_match('/.*[’`’‘]/', $check['content'])) {
            return false;
        }
        return true;
    }


    
    public function getNameIdForFilter()
    {
        $unattachedMessages = $this->find('all', array('fields' => array('_id','name') ) );
        $nameIds = null;
        foreach($unattachedMessages as $unattachedMessage) {
            $nameIds[$unattachedMessage['UnattachedMessage']['_id']] = $unattachedMessage['UnattachedMessage']['name']; 
        }     
        return  $nameIds;        
    }
    
    
}
