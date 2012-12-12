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
        return '3';
    }

    
    function getRequiredFields($objectType=null)
    {
        return array(
            'phone',
            'session-id',
            'last-optin-date',
            'last-optout-date',
            'enrolled',
            'tags',
            'profile',
            );
    }

    public $fieldFilters = array(
        'phone' => 'phone',
        'optin' => 'optin',
        'optin-date-from' => 'optin date from',
        'optin-date-to' => 'optin date to',
        'optout' => 'optout',
        //require to modify the model to add the last optout date
        'optout-date-from' => 'optout date from',
        'optout-date-to' => 'optout date to',
        'enrolled' => 'enrolled',
        'not-enrolled' => 'not enrolled',
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
            ),
        'profile' => array(
            'rule' => 'validateProfile',
            'message' => 'Invalid format. Must be label:value, label:value, ... e.g gender:male, ..'
            ),
        'tags' => array(
            'rule' => 'validateTags',
            'message' => 'Only letters and numbers. Must be tag, tag, ... e.g cool, nice, ...'
            )
        );

    public function validateTags($check)
    {
        $regex = '/^[a-z0-9A-Z\s]+$/';
        foreach ($check['tags'] as $tag) {
            if (!preg_match($regex,$tag)) {
                return false;
            }
        }
        return true;
    }
    
    
    public function validateProfile($check)
    {
        $regex = '/^[a-zA-Z0-9\s]+:[a-zA-Z0-9\s]+$/';
        foreach ($check['profile'] as $profile) {
            foreach ($profile as $key => $value) {
                $result = $profile['label'].":".$profile['value'];
                if (!preg_match($regex,$result)) {
                    return false;
                }
            }
        }
        return true;
    }
    
        
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
            $this->data['Participant']['last-optout-date'] = null;
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
        } else {
            if(!isset($this->data['Participant']['tags']))
                $this->data['Participant']['tags'] = array();
            else if (isset($this->data['Participant']['tags']) and !is_array($this->data['Participant']['tags'])) {
                $tags = trim(stripcslashes($this->data['Participant']['tags']));
                $tags = array_filter(explode(",", $tags));
                $cleanTags = array();
                foreach ($tags as $tag) {
                    $cleanTags[] = trim($tag);
                }
                $this->data['Participant']['tags'] = $cleanTags;
            }
            
            if(!isset($this->data['Participant']['profile']))
                $this->data['Participant']['profile'] = array();
            else if (isset($this->data['Participant']['profile']) and !is_array($this->data['Participant']['profile'])) {
                $profiles = trim(stripcslashes($this->data['Participant']['profile']));
                $profiles = array_filter(explode(",", $profiles));
                $profileList = array();
                foreach ($profiles as $profile) {
                    list($label,$value) = explode(":", $profile);
                    $newProfile = array();
                    $newProfile['label'] = $label;
                    $newProfile['value'] = $value;
                    $newProfile['raw'] = null;
                    $profileList[] = $newProfile;
                }
                $this->data['Participant']['profile'] = $profileList;
            }
        }

        return true;
    }

    public function getDistinctTagsAndLabels()
    {
        $tagsQuery = array(
            'distinct'=>'participants',
            'key'=> 'tags');
        $distinctTags = $this->query($tagsQuery);
        
        $map = new MongoCode("function() { 
            for(var i = 0; i < this.profile.length; i++) {
                emit([this.profile[i].label,this.profile[i].value].join(':'), 1);
                }
            }");
        $reduce = new MongoCode("function(k, vals) { 
            return vals.length; }");
        $labelsQuery = array(
            'mapreduce' => 'participants',
            'map'=> $map,
            'reduce' => $reduce,
            'query' => array(),
            'out' => 'map_reduce_tagsandlabels');

        $mongo = $this->getDataSource();
        $cusor = $mongo->mapReduce($labelsQuery);
        foreach($cusor as $distinctLabel) {
            $distinctLabels[] = $distinctLabel['_id'];    
        }
        
        return array_merge($distinctTags['values'], $distinctLabels);
        
    }


    function gen_uuid() 
    {
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
