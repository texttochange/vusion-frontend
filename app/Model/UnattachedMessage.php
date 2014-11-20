<?php
App::uses('MongoModel', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('VirtualModel', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('VusionConst', 'Lib');
App::uses('Participant', 'Model');


class UnattachedMessage extends MongoModel
{
    
    var $specific    = true;
    var $name        = 'UnattachedMessage';
    var $useDbConfig = 'mongo';
    var $useTable    = 'unattached_messages';
    
    var $mongoNoSetOperator = true;
    
    
    function getModelVersion()
    {
        return '4';
    }
    
    
    function getRequiredFields($objectType=null)
    {
        return array(
            'name',
            'send-to-type',
            'content',           
            'type-schedule',
            'fixed-time',
            'created-by'
            );
    }
    
    
    var $matchFields = array(
        'send-to-match-operator',
        'send-to-match-conditions'
        );
    
    var $phoneFields = array(
        'send-to-phone'
        );
    

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
            'validContentVariable' => array(
                'rule' => 'validContentVariable',
                'message' => 'noMessage'
                ),
            ),
        'type-schedule' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please choose a type of schedule for this message.'
                ),
            'validValue' => array(
                'rule' => array('inList', array('immediately', 'fixed-time', 'none')),
                'message' => 'This type of schedule is not allowed.'
                )
            ),
        'fixed-time' => array(
            'notempty' => array(
                'rule' => array('notEmptyIfNoSchedule'),
                'message' => 'Please enter a fixed time for this message.'
                ),
            'isNotPast' => array(
                'rule' => 'validateIsNotPast',
                'message' => 'Fixed time cannot be in the past.'
                )
            ),
        'created-by' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Message must be created by a user.'
                )
            )
        );
    
    var $findMethods = array(
        'all' => true,
        'first' => true,
        'count' => true,
        'scheduled' => true,
        'drafted' => true,
        'sent' => true);

    public function addFindTypeCondition($findType, $conditions=array()) {
        switch ($findType) {
            case 'all':
                break;
            case 'scheduled':
                $programNow = $this->ProgramSetting->getProgramTimeNow();
                if ($programNow) {
                    $conditions['fixed-time'] = array('$gt' => $programNow->format("Y-m-d\TH:i:s"));
                }
                break;
            case 'sent':
                $programNow = $this->ProgramSetting->getProgramTimeNow();
                if ($programNow) {
                    $conditions['fixed-time'] = array('$lt' => $programNow->format("Y-m-d\TH:i:s"));
                }
                break;
            case 'drafted':
                $conditions['type-schedule'] = 'none';
                break;
            default:
                throw new Exception(__('FindType not supported: %s', $findType));
        }
        return $conditions;
    }

    
    protected function _findScheduled($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions'] = $this->addFindTypeCondition('scheduled', $query['conditions']);
            return $query;
        }
        return $results;
    }


    protected function _findSent($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions']= $this->addFindTypeCondition('sent', $query['conditions']);
            return $query;
        }
        return $results;
    }


    protected function _findDrafted($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions'] = $this->addFindTypeCondition('drafted', $query['conditions']);
            return $query;
        }
        return $results;
    }

    
    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
     
        //Seems to be necessary API loading Vs Web loading
        if (isset($id['id'])) {
            $options = array('database' => $id['id']['database']);
        } else {
            $options = array('database' => $id['database']);
        }
        $this->ProgramSetting = new ProgramSetting($options);
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
    
    private function _generateName() 
    {
        $now = $this->ProgramSetting->getProgramTimeNow();
        $content = $this->data['UnattachedMessage']['content'];
        return $now->format("d/m/Y H:i:s") . ' ' . implode(' ', array_slice(explode(' ', $content), 0, 2));
    }
    

    public function beforeValidate()
    {
        parent::beforeValidate();

        $this->_setDefault('content', null);
        $this->_setDefault('name', $this->_generateName());

        if (isset($this->data['UnattachedMessage']['type-schedule'])) {
            if ($this->data['UnattachedMessage']['type-schedule'] == 'none') {
                $this->data['UnattachedMessage']['fixed-time'] = null;
            } else if ($this->data['UnattachedMessage']['type-schedule'] == 'immediately') {
                $now = $this->ProgramSetting->getProgramTimeNow();
                if (isset($now)) {
                    $this->data['UnattachedMessage']['fixed-time'] = $now->format("Y-m-d\TH:i:s");
                }            
            } else if (isset($this->data['UnattachedMessage']['fixed-time'])) {
                $this->data['UnattachedMessage']['fixed-time'] = DialogueHelper::convertDateFormat($this->data['UnattachedMessage']['fixed-time']);
            } 
        }

        if (isset($this->data['UnattachedMessage']['send-to-phone'])) {
            foreach ($this->data['UnattachedMessage']['send-to-phone'] as &$phone) {
                $phone = Participant::cleanPhone($phone);
            }
        }
        return true;           	
    }    
    
    public function notEmptyIfNoSchedule($check)
    {
        if ($this->data['UnattachedMessage']['type-schedule'] === 'none') {
            return true;
        }
        if (!isset($check['fixed-time'])) {
            return false;    
        }
        return !in_array($check['fixed-time'], array(null, ''));   
    }
    

    public function isNotPast($check)
    {
        $programTimezone = $this->ProgramSetting->find('getProgramSetting', array('key' => 'timezone'));
        $fixedTimeDate = new DateTime($check['fixed-time'], timezone_open($programTimezone));
        return $this->ProgramSetting->isNotPast($fixedTimeDate);
    }
    

    public function validateIsNotPast($check)
    {
        if ($this->data['UnattachedMessage']['type-schedule'] === 'none') {
            return true;
        }
        return $this->isNotPast($check);
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
            if (!preg_match(VusionConst::PHONE_REGEX, $participantPhone)) {
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
    
    public function validContentVariable($check)
    {
        return VusionValidation::validContentVariable($check);
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
    
    
    public function getNameById($id)
    {
        $unattachedMessage = $this->find('first', array(
            'conditions' => array('_id' => $id)));
        return $unattachedMessage['UnattachedMessage'];
    }
    
    
}
