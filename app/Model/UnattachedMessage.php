<?php
App::uses('MongoModel', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('DialogueHelper', 'Lib');
/**
 * UnattachedMessage Model
 *
 */
class UnattachedMessage extends MongoModel
{

    var $specific    = true;
    var $name        = 'UnattachedMessage';
    var $useDbConfig = 'mongo';
    var $useTable    = 'unattached_messages';
    
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
        'to' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please select an option for Send To.'
                )
            ),
        'content' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter some content for this message.'
                )
            ),
        'schedule' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please choose a schedule for this message.'
                )
            ),
        'fixed-time' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter a fixed time for this message.'
                ),
            'isNotPast' => array(
                'rule' => 'isNotPast',
                //'required' => true
                )
            )
        );


    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        
        $options              = array('database'=>$id['database']);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->DialogueHelper = new DialogueHelper();
    }


    public function beforeValidate()
    {
            	
    }
    
    
    public function beforeSave()
    {    
        if (isset($this->data['UnattachedMessage']['fixed-time'])) {
            $validator = $this->validator();
            $validator['fixed-time']['isNotPast']->required = true;
        
            if ($this->DialogueHelper->validateDate($this->data['UnattachedMessage']['fixed-time']))
                return true;
            
            if (!$this->DialogueHelper->validateDateFromForm($this->data['UnattachedMessage']['fixed-time']))
                return false;
            
            $this->data['UnattachedMessage']['fixed-time'] = $this->DialogueHelper->convertDateFormat($this->data['UnattachedMessage']['fixed-time']);
            return true;
        } else {
            return true;
        }
    }
    
    
    public function isNotPast()
    {
        $now = new DateTime('now');
        $programSettings = $this->ProgramSetting->getProgramSettings();
        if (!isset($programSettings['timezone']) or ($programSettings['timezone'] == null))
            return __("The program settings are incomplete. Please specificy the Timezone.");
        
        $programTimezone = $programSettings['timezone'];
        date_timezone_set($now,timezone_open($programTimezone));        
        $dateFixedTime = $this->DialogueHelper->convertDateFormat($this->data['UnattachedMessage']['fixed-time']);
        $dateNow = $this->DialogueHelper->convertDateFormat($now->format('d/m/Y H:i'));
        if ($dateFixedTime < $dateNow)
            return __("Fixed time cannot be in the past.");
        return true;
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
    
    
}
