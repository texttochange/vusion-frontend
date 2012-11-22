<?php
App::uses('MongoModel', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Dialogue', 'Model');

class Participant extends MongoModel
{

    var $specific = true;    

    var $name        = 'Participant';
    var $useDbConfig = 'mongo';
    
    
    function getModelVersion()
    {
        return '2';
    }

    
    function getRequiredFields($objectType=null)
    {
        return array(
            'phone',
            'session-id',
            'last-optin-date',
            'enrolled',
            'tags',
            'profile',
            );
    }

    public $fieldFilters = array(
        'phone' => 'phone',
        'optin' => 'optin',
        'enrolled' => 'enrolled',
        'tag'=>'tag',
        'label'=>'label',
    );
    
    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        
        $options              = array('database'=>$id['database']);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->Dialogue       = new Dialogue($options);
    }

    public $validate = array(
        'phone' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter a phone number.'
                ),
            'hasPlus'=>array(
                'rule' => 'hasPlus',
                'message' => "A phone number must begin with a '+' sign and end with a serie of digits such as +3345678733.",
                'required' => true
                ),
            'isReallyUnique' => array(
                'rule' => 'isReallyUnique',
                'message' => 'This phone number already exists in the participant list.',
                'required' => true
                )
            ));

    
    public function isReallyUnique($check)
    {
        if ($this->id) {
            $conditions = array('id'=>array('$ne'=> $this->id),'phone' => $check['phone']);
        } else {
            $conditions = array('phone' => $check['phone']);
        }
        $result = $this->find('count', array(
            'conditions' => $conditions
            ));
        return $result < 1;            
    }
    
    
    public function hasPlus($check)
    {
        $regex = '/^\+[0-9]+/';
        return preg_match($regex, $check['phone']);
    }
    
    
    public function beforeValidate()
    {
        parent::beforeValidate();

        if (!isset($this->data['Participant']['phone']) or $this->data['Participant']['phone'] == "" )
            return false;

        $this->data['Participant']['phone'] = trim($this->data['Participant']['phone']);
        $this->data['Participant']['phone'] = preg_replace("/^(00|0)/", "+",$this->data['Participant']['phone']);    
        if (!preg_match('/^\+[0-9]+/', $this->data['Participant']['phone'])) 
            $this->data['Participant']['phone'] = "+".$this->data['Participant']['phone']; 

        $this->data['Participant']['phone'] = (string) $this->data['Participant']['phone'];

        //The time should be provide by the controller
        if (!$this->data['Participant']['_id']) {
            $programNow = $this->ProgramSetting->getProgramTimeNow();
            if ($programNow==null)
                return false;
            $lastOptinDate = (isset($this->data['Participant']['last-optin-date'])) ? $this->data['Participant']['last-optin-date'] : $programNow->format("Y-m-d\TH:i:s"); 
            $this->data['Participant']['last-optin-date'] = $lastOptinDate;
            $sessionId = (isset($this->data['Participant']['session-id'])) ? $this->data['Participant']['session-id'] : $this->gen_uuid();
            $this->data['Participant']['session-id'] = $sessionId;
            $tags = (isset($this->data['Participant']['tags'])) ? $this->data['Participant']['tags'] : array();
            $this->data['Participant']['tags'] = $tags;
            $condition = array('condition' => array('auto-enrollment'=>'all'));
            $autoEnrollDialogues = $this->Dialogue->getActiveDialogues($condition);
            if ($autoEnrollDialogues == null)
                $this->data['Participant']['enrolled'] = array();
            foreach ($autoEnrollDialogues as $autoEnroll) {
                $this->data['Participant']['enrolled'][] = array(
                    'dialogue-id' => $autoEnroll['dialogue-id'],
                    'date-time' => $programNow->format("Y-m-d\TH:i:s")
                    );
            }
            if (!isset($this->data['Participant']['profile']))
                $this->data['Participant']['profile'] = array();
        }

        return true;
    }

    function gen_uuid() {
        return sprintf( '%04x%04x%04x%04x%04x%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            
            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),
            
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,
            
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,
            
            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
            );
    }
    
    
    public function autoEnrollDialogue($dialogueId)
    {
        $programNow = $this->ProgramSetting->getProgramTimeNow();
        $updateData = array(
            '$push'=>array(
                'enrolled'=>array(
                    'dialogue-id'=>$dialogueId,
                    'date-time'=>$programNow->format("Y-m-d\TH:i:s")
                    )
                )
            );
        $conditions = array(
            'session-id' => array('$ne'=>null),
            'enrolled.dialogue-id' => array('$ne'=>$dialogueId)
            );
        $this->updateAll($updateData, $conditions);        
    }

    
}
