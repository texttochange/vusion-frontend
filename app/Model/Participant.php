<?php
App::uses('MongoModel', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Dialogue', 'Model');
App::uses('DialogueHelper', 'Lib');

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

   
    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        
        $options              = array('database'=>$id['database']);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->Dialogue       = new Dialogue($options);
        $this->DialogueHelper = new DialogueHelper();
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
            $this->_editTags();
            
            $this->_editProfile();
            
            $this->_editEnrolls();            
        }

        return true;
    }

    public function getDistinctTagsAndLabels()
    {
        $results = $this->getDistinctTags();

        $distinctLabels = $this->getDistinctLabels();

        return array_merge($results, $distinctLabels);
    }

    public function getDistinctTags()
    {
        $tagsQuery = array(
            'distinct'=>'participants',
            'key'=> 'tags');
        $distinctTags = $this->query($tagsQuery);
        return $distinctTags['values'];
    }

    public function getDistinctLabels($conditions = null)
    {
        $results = array();
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
            'out' => 'map_reduce_participantLabels');
        
        if (isset($conditions)) {
            $labelsQuery['query'] = $conditions;
        }

        $mongo = $this->getDataSource();
        $cursor = $mongo->mapReduce($labelsQuery);
        foreach($cursor as $distinctLabel) {
            $results[] = $distinctLabel['_id'];
        }
        return $results;  
    }

    public function getExportHeaders($conditions = null)
    {
        $headers = array(
            "phone",
            //"last-optin-date",
            //"last-optout-date",
            "tags");

        $distinctLabels = $this->getDistinctLabels($conditions);
        foreach($distinctLabels as $distinctLabel) {
            $label = explode(':', $distinctLabel);
            if (!in_array($label[0], $headers))
                $headers[] = $label[0];
        }
        return $headers;
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
    
    
    protected function _editTags()
    {
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
        return $this->data['Participant']['tags'];
    }
    
    
    protected function _editProfile()
    {
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
        return $this->data['Participant']['profile'];
    }
    
    
    protected function _editEnrolls()
    {
        $participantUpdateData = $this->data;
        
        $originalParticipantData = $this->read(); // $this->read() deletes already processed info and
                                                  // and they must all be re-initialized.
        
        // ******** re-initialize already processed information *********/////
        $this->data['Participant'] = $participantUpdateData['Participant'];
        // ******************************************************************////
        
        $programNow = $this->ProgramSetting->getProgramTimeNow();
        
        if(!isset($participantUpdateData['Participant']['enrolled']) or 
            !is_array($participantUpdateData['Participant']['enrolled'])) {
            $this->data['Participant']['enrolled'] = array();
            return; 
        }
        
        if (isset($participantUpdateData['Participant']['enrolled'])
            and $participantUpdateData['Participant']['enrolled'] == array()) {
            $this->data['Participant']['enrolled'] = array();
            return;
        }
        
        $this->data['Participant']['enrolled'] = array();
        foreach ($participantUpdateData['Participant']['enrolled'] as $key => $value) {
            $dialogueId = (is_array($value)) ? $value['dialogue-id'] : $value;
            $enrollTime = (is_array($value)) ? $value['date-time'] : $programNow->format("Y-m-d\TH:i:s");

            if ($originalParticipantData['Participant']['enrolled'] == array()) {
                $this->data['Participant']['enrolled'][] = array(
                            'dialogue-id' => $dialogueId,
                            'date-time' => $enrollTime
                            );
                continue;
            }
            foreach ($originalParticipantData['Participant']['enrolled'] as $orignalEnroll) {
                if ($this->_alreadyInArray($dialogueId, $this->data['Participant']['enrolled']))
                    continue;
               
                if ($dialogueId == $orignalEnroll['dialogue-id']) {
                    $this->data['Participant']['enrolled'][] = $orignalEnroll;
                } else {
                    $dateTime = $programNow->format("Y-m-d\TH:i:s");                            
                    if ($this->_alreadyInArray($dialogueId, $originalParticipantData['Participant']['enrolled'])) {
                        $index = $this->_getDialogueIndex($dialogueId,$originalParticipantData['Participant']['enrolled']);
                        if ($index) {
                            $dateTime = $originalParticipantData['Participant']['enrolled'][$index]['date-time'];
                        }
                    }
                    $this->data['Participant']['enrolled'][] = array(
                        'dialogue-id' => $dialogueId,
                        'date-time' => $dateTime
                        );
                    break;
                }
            }
        }
    }
    
    
    protected function _alreadyInArray($param, $check)
    {
        foreach ($check as $checked) {
            if (in_array($param, $checked))
                return true;
        }
        return false;
    }
    
    
    protected function _getDialogueIndex($param, $check)
    {
        foreach ($check as $key => $value) {
            if ($param == $value['dialogue-id'])
                return $key;
        }
        return false;
    }
    
    
    public function reset($check)
    {
        $check['enrolled'] = null;
        $this->save($check);
                
        $programNow = $this->ProgramSetting->getProgramTimeNow();
        
        $check['session-id'] = $this->gen_uuid();
        $check['last-optin-date'] = $programNow->format("Y-m-d\TH:i:s");
        $check['last-optout-date'] = null;
        $check['tags'] = array();
        $check['profile'] = array();
        
        return $check;
    }

    #Filter variables and functions
    public $filterFields = array(
        'phone' => array(
            'label' => 'phone',
            'operators'=> array(
                'start-with' => array(
                    'label' => 'start with',
                    'parameter-type' => 'text'),
                'equal-to' => array(
                    'label' => 'equal to',
                    'parameter-type' => 'text'),
                'start-with-any' => array(
                    'label' => 'start with any of',
                    'parameter-type' => 'text'))),
        'optin' => array(
            'label' => 'optin',
            'operators' => array(
                'now' => array(
                    'label' => 'now',
                    'parameter-type' => 'none'),
                'date-from' => array(
                    'label' => 'date from',
                    'parameter-type' => 'date'),
                'date-to' => array(
                    'label' => 'date to',
                    'parameter-type' => 'date'))),
        'optout' => array(
            'label' => 'optout',
            'operators' => array(
                'now' =>array(
                    'label' => 'now',
                    'parameter-type' => 'none'),
                'date-from' => array(
                    'label' => 'date from',
                    'parameter-type' => 'date'),
                'date-to' => array(
                    'label' => 'date to',
                    'parameter-type' => 'date'))),
        'enrolled' => array(
            'label' => 'enrolled',
            'operators' => array(
                'in' => array(
                    'label' => 'in',
                    'parameter-type' => 'dialogue'),
                'not-in' =>  array(
                    'label' => 'not in',
                    'parameter-type' => 'dialogue'))),
        'tagged' => array(
            'label' => 'tagged',
            'operators' => array(
                'in' =>  array(
                    'label' => 'with',
                    'parameter-type' => 'tag'),
                'not-in' =>  array(
                    'label' => 'not with',
                    'parameter-type' => 'tag'))),
        'labelled' => array(
            'label' => 'labelled',
            'operators' => array(
                'in' =>  array(
                    'label' => 'with',
                    'parameter-type' => 'label'),
                'not-in' =>  array(
                    'label' => 'not with',
                    'parameter-type' => 'label')))
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
    

    public function fromFilterToQueryConditions($filterOperator, $filterParams) {

        $conditions = array();

        foreach($filterParams['filter_param'] as $filterParam) {
        
            $condition = null;

            $this->validateFilter($filterParam);
       
            if ($filterParam[1] == 'enrolled') {
                if ($filterParam[2] == 'in') {
                    $condition['enrolled.dialogue-id'] = $filterParam[3];
                } elseif ($filterParam[2] == 'not-in') {
                    $condition['enrolled.dialogue-id'] = array('$ne'=> $filterParam[3]);
                } 
            } elseif ($filterParam[1] == 'optin') {
                if ($filterParam[2] == 'now') {
                    $condition['session-id'] = array('$ne' => null);
                } elseif ($filterParam[2] == 'date-from') {
                    $condition['last-optin-date']['$gt'] = $this->DialogueHelper->ConvertDateFormat($filterParam[3]);
                } elseif ($filterParam[2] == 'date-to') {
                    $condition['last-optin-date']['$lt'] = $this->DialogueHelper->ConvertDateFormat($filterParam[3]);
                }
            } elseif ($filterParam[1] == 'optout') {
                if ($filterParam[2] == 'now') { 
                    $condition['session-id'] = null;
                } elseif ($filterParam[2] =='date-from') {
                    $condition['last-optout-date']['$gt'] = $this->DialogueHelper->ConvertDateFormat($filterParam[3]);
                } elseif ($filterParam[2] =='date-to') {
                    $condition['last-optout-date']['$lt'] = $this->DialogueHelper->ConvertDateFormat($filterParam[3]);
                }
            } elseif ($filterParam[1] == 'phone') {
                if ($filterParam[2] == 'start-with-any') {
                    $phoneNumbers = explode(",", str_replace(" ", "", $filterParam[3]));
                    if ($phoneNumbers) {
                        if (count($phoneNumbers) > 1) {
                            $or = array();
                            foreach ($phoneNumbers as $phoneNumber) {
                                $regex = new MongoRegex("/^\\".$phoneNumber."/");
                                $or[] = array('phone' => $regex);
                            }
                            $condition['$or'] = $or;
                        } else {
                            $condition['phone'] = new MongoRegex("/^\\".$phoneNumbers[0]."/");
                        }
                    } 
                } elseif ($filterParam[2] == 'start-with') {
                    $condition['phone'] = new MongoRegex("/^\\".$filterParam[3]."/"); 
                } elseif ($filterParam[2] == 'equal-to') {
                    $condition['phone'] = $filterParam[3];        
                }
            } elseif ($filterParam[1]=='tagged') {
                if ($filterParam[2] == 'in') {
                    $condition['tags'] = $filterParam[3];
                } elseif ($filterParam[2] == 'not-in') {
                    $condition['tags'] = array('$ne' => $filterParam[3]);
                }
            } elseif ($filterParam[1] == 'labelled') {
                $label = explode(":", $filterParam[3]);   
                if ($filterParam[2] == 'in') {
                    $condition['profile'] = array(
                        '$elemMatch' => array(
                            'label' => $label[0],
                            'value' => $label[1])
                        );
                } elseif (($filterParam[2] == 'not-in')) {
                    $condition['profile'] = array(
                        '$elemMatch' => array(
                            '$or' => array(
                                array('label' => array('$ne' => $label[0])),
                                array('value' => array('$ne' => $label[1]))
                                )
                            )
                        );
                }
            }
            
            if ($filterOperator=="all") {
                if (count($conditions) == 0) {
                    $conditions = $condition;
                } elseif (!isset($conditions['$and'])) {
                    $conditions = array('$and' => array($conditions, $condition));
                } else {
                    array_push($conditions['$and'], $condition);
                }
            }  elseif ($filterOperator=="any") {
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
