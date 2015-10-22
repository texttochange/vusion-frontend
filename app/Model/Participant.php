<?php
App::uses('ProgramSpecificMongoModel', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Dialogue', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('VusionConst', 'Lib');
App::uses('VusionValidation', 'Lib');
App::uses('ValidationHelper', 'Lib');


class Participant extends ProgramSpecificMongoModel
{
    var $name         = 'Participant';
    var $importErrors = array();
    
    
    function getModelVersion()
    {
        return '5';
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
            'transport_metadata',
            'simulate'
            );
    }
    
    
    public $findMethods = array(
        'all' => true,
        'allSafeJoin' => true,
        'first' => true,
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


    public static function getDefaultImportedTag() 
    {
        return array('imported');
    }

    
    public function exists() {
        if (parent::exists()) {
            return true;
        } elseif ($this->find('count', array('conditions' => array('phone' => $this->id))) > 0) {
            return true;
        } else {
            return false;
        }
    }


    public function read($fields=null, $id=null) {
        if ($participant = parent::read($fields, $id)) {
            return $participant;
        } 
        return $this->find(
            'first', 
            array(
                'conditions' => array('phone' => $id),
                'fields' => $fields));
    }


    public function initializeDynamicTable($forceNew=false) 
    {
        parent::initializeDynamicTable();
        $this->ProgramSetting = ProgramSpecificMongoModel::init(
            'ProgramSetting', $this->databaseName, $forceNew);        
        $this->Dialogue = ProgramSpecificMongoModel::init(
            'Dialogue', $this->databaseName, $forceNew);
        $this->ValidationHelper = new ValidationHelper($this);
    }
    
    //Patch the missing callback for deleteAll in Behavior
    public function deleteAll($conditions, $cascade = true, $callback = false)
    {
        parent::deleteAll($conditions, $cascade, $callback);
        $this->flushCached();
    }
    
    
    public $validate = array(
        'phone' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter a phone number.'
                ),            
            'validPhone'=>array(
                'rule' => 'validPhone',
                'message' => 'noMessage',
                'required' => true
                ),
            'isReallyUnique' => array(
                'rule' => 'isReallyUnique',
                'message' => 'This phone number already exists in the participant list.',
                'required' => true
                ),
            ),
        'profile' => array(
            'validateLabels' => array(
                'rule' => 'validateLabels',
                'message' => 'noMessage'
                ),
            ),
        'tags' => array(
            'validateTags' => array(
                'rule' => 'validateTags',
                'message' => 'noMessage'
                ),
            ),
        'join-type' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please select one option.'
                ),
            ),
        'simulate' => array(
            'boolean' => array(
                'rule' => array('boolean'),
                'message' => 'Please enter simulate as a boolean option.'
                ),
            ),
        'enrolled' => array(
            'validateEnrolleds' => array(
                'rule' => 'validateEnrolleds',
                'message' => 'noMessage'
                ),
            ),
        'import-type' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please select one option.'
                ),
            'allowedChoice' => array(
                'rule' => array('inList', array('keep', 'replace', 'update')),
                'message' => 'import tags and labels option not allowed.'
                ),
            ),
        );
    
    
    public $validateLabel = array(
        'label' => array(
            'validateValue' => array(
                'rule' => array('custom', VusionConst::LABEL_REGEX),
                'message' => VusionConst::LABEL_FAIL_MESSAGE,
                ),
            ),
        'value' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The label value cannot be empty.',
                ),
            'validateValue' => array(
                'rule' => array('custom', VusionConst::LABEL_VALUE_REGEX),
                'message' => VusionConst::LABEL_VALUE_FAIL_MESSAGE,
                ),            
            ),
        'raw' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'The field raw is required.'
                ),
            ),
        );

    public $validateEnrolled = array(
        'dialogue-id' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The dialogue-id cannot be empty.',
                ),
            ),
        'date-time' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The date-time cannot be empty.',
                ),
            'isValid' => array(
                'rule' => array('custom', VusionConst::DATE_TIME_REGEX),
                'message' => 'The date-time format is not incorrect.'
                )
            )
        );
    
    
    public function validateEnrolleds($check)
    {
        $validationErrors = $this->ValidationHelper->runValidationRulesOnList($check, $this->validateEnrolled);
        if (is_array($validationErrors)) {
            $this->validationErrors['enrolled'] = $validationErrors;
            return false;
        }
        return true;
    }    
    
    
    public function validateTags($check)
    {
        $index = 0;
        foreach ($check['tags'] as $tag) {
            if (is_string($validationError = $this->validateTag($tag))) {
                if (!isset($this->validationErrors['tags'])) {
                    $this->validationErrors['tags'] = array();
                }
                $this->validationErrors['tags'][$index] = $validationError;
            }
            $index++;
        }
        if (isset($this->validationErrors['tags'])) {
            return false;
        }
        return true;
    }
    
    
    public function validateTag($check)
    {
        $regex = VusionConst::TAG_REGEX;
        if (!preg_match($regex, $check)) {
            return VusionConst::TAG_FAIL_MESSAGE;
        }
        return true;
    }
    
    
    public function validateLabels($check)
    {
        $count = 0;
        foreach ($check['profile'] as $element) {
            $validationErrors = array();
            foreach ($this->validateLabel as $field => $rules) {
                foreach ($rules as $rule) {
                    if (is_array($rule['rule'])) {
                        $func = $rule['rule'][0];
                        $args = array_slice($rule['rule'], 1);
                        $args = $args[0];
                    } else {
                        $func = $rule['rule'];
                        $args = null;
                    }
                    if ($func == 'required') {
                        $valid = array_key_exists($field, $element);
                    } else {
                        $valid = forward_static_call_array(array("VusionValidation", $func), array($element[$field], $args));
                    }
                    if (!is_bool($valid) || $valid == false) {
                        // To revert when creating a better form edit
                        //$validationErrors[$field][] = $rule['message'];
                        $validationErrors[] = $rule['message'];
                        break;
                    }
                }
            }
            if ($validationErrors != array()) {
                // To switch when creating a better form edit
                //$this->validationErrors['profile'][$count] = $validationErrors;
                $this->validationErrors['profile'] = $validationErrors;
                break;
            }
            $count++;
        }
        if (isset($this->validationErrors['profile'])) {
            return false;
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
    

    public function validPhone($check)
    {
        if ($this->data['Participant']['simulate']) {
            if (!preg_match(VusionConst::PHONE_SIMULATED_REGEX, $check['phone'])) {
                return VusionConst::PHONE_SIMULATED_REGEX_FAIL_MESSAGE;
            }
        } else {
            if (!preg_match(VusionConst::PHONE_NORMAL_REGEX, $check['phone'])) {
                return VusionConst::PHONE_NORMAL_REGEX_FAIL_MESSAGE;
            }
        }
        return true;
    }

    
    public static function cleanPhone($phone) 
    {
        if (isset($phone) and !empty($phone)) {
            $phone = trim($phone);           
            $phone = preg_replace("/[^\+\#\dO]/", "", $phone);
            //Replace letter O by zero
            $phone = preg_replace("/O/", "0", $phone);
            $phone = preg_replace("/^(00|0)/", "+", $phone);    
            if (!preg_match('/^[+\#]+[0-9]+/', $phone)) { 
                $phone = "+" . $phone; 
            }
            return (string) $phone;
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
    
    protected function _findAllSafeJoin($state, $query, $results=array())
    {
        
        return $this->findAllSafeJoin($state, $query, $results);
    }
    
    
    public function addMassTags($tag, $conditions=array())
    {   
        $tag = trim($tag);       
        $valid = $this->validateTag($tag);
        if (!is_bool($valid) || $valid != true){
            return $valid;
        }
        if (isset($conditions['$and'])) {
            $conditions['$and'] = array_merge($conditions['$and'], array(array('tags' => array('$ne' => $tag))));
        } else if (isset($conditions['tags'])) {
            $conditions['$and'] = array_merge(array(array('tags' => $conditions['tags'])), array(array('tags' => array('$ne' => $tag))));
            unset($conditions['tags']);
        } else {
            $conditions['tags'] = array('$ne' => $tag);
        }
        $massTag = array(
            '$push' => array(
                'tags' => $tag              
                )
            );    
        $this->updateAll($massTag, $conditions);        
        return true;
    }
    
    
    public function deleteMassTags($tag, $conditions=array())
    {   
        $tag = trim($tag);  
        $valid = $this->validateTag($tag);
        if (!is_bool($valid) || $valid != true) {
            return $valid;
        }
        $massUntag = array(
            '$pull' => array(
                'tags' => $tag              
                )
            );    
        $this->updateAll($massUntag, $conditions);        
        return true;
    }
    
    
    public function beforeValidate()
    {
        parent::beforeValidate();
        $programNow = $this->ProgramSetting->getProgramTimeNow();
        if ($programNow == null) {
            //The program time MUST be set
            return false;
        }
        
        if ($this->data['Participant']['simulate']) {
            $this->_setDefault('phone', $this->generateSimulatedPhone());
        }
        
        $this->_setDefault('phone', null);
        $this->data['Participant']['phone'] = $this->cleanPhone($this->data['Participant']['phone']);
        
        $this->_setDefault('tags', array());
        $this->data['Participant']['tags'] = Participant::cleanTags($this->data['Participant']['tags']);
        
        $this->_setDefault('profile', array());
        $this->data['Participant']['profile'] = Participant::cleanProfile($this->data['Participant']['profile']);
        

        $this->_setDefault('transport_metadata', array());
        
        if (!$this->data['Participant']['simulate']) {
            $this->_setDefault('simulate', false);
        }
        
        if (!$this->data['Participant']['_id']) {
            $this->_setDefault('last-optin-date', $programNow->format("Y-m-d\TH:i:s"));
            $this->_setDefault('last-optout-date', null);
            $this->_setDefault('session-id', $this->gen_uuid());
            $this->_setDefault('enrolled', array());            
        } 
        $this->_editEnrolls();
        
        return true;
    }
    
    
    public function generateSimulatedPhone()
    {  
        $i=1;
        while (true) {
            $simulatedPhone = ("#" . $i );
            $result = $this->find('count', array(
                'conditions' => array('phone' => $simulatedPhone)));
            if ($result < 1) {
                return $simulatedPhone;
            }
            $i++;
        }
    }
        
    
    public function getDistinctTagsAndLabels()
    {
        $results = $this->getDistinctTags();
        
        $distinctLabels = $this->getDistinctLabels();
        
        return array_merge($results, $distinctLabels);
    }
    
    
    public function getDistinctTags($conditions = null)  
    {
        $tagsQuery = array(
            'distinct'=>'participants',
            'key'=> 'tags');
        if (isset($conditions)) {
            $tagsQuery['query'] = $conditions;
        }
        $distinctTags = $this->query($tagsQuery);
        return $distinctTags['values'];
    }
    
    
    public function getDistinctLabels($conditions = null, $timeout = 30000)
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
            'out' => 'inline');
        
        if (isset($conditions)) {
            $labelsQuery['query'] = $conditions;
        }
        
        $mongo = $this->getDataSource();
        $cursor = $mongo->mapReduce($labelsQuery, $timeout);
        if ($cursor == false){ 
            return $results;
        }
        foreach ($cursor as $distinctLabel) {
            $results[] = $distinctLabel['_id'];
        }
        return $results;  
    }
    

    public function aggregateCountPerDay($conditions = array(), $timeout = 30000)
    {
        $results = array();
        $map = new MongoCode('function() {
            var optinDate = new Date(Date.parse(this["last-optin-date"].substring(0,10)));
            var endPeriode;
            if (this["last-optout-date"] != null) {
                endPeriode = new Date(this["last-optout-date"]);
            } else {
                endPeriode = new Date(Date.now());
            }

            function dateFormat(d) {
                var yyyy = d.getFullYear().toString();
                var mm = (d.getMonth()+1).toString();
                var dd  = d.getDate().toString(); 
                return yyyy + "-" + (mm[1]?mm:"0"+mm[0]) + "-" + (dd[1]?dd:"0"+dd[0]);
            }
        
            for (var d = optinDate; d <= endPeriode; d.setDate(d.getDate() + 1)) {
                current = dateFormat(d);
                emit(current, 1); 
            }
        }');
        $reduce = new MongoCode("function(k, vals) { 
            return vals.length; 
        }");
        $query = array(
            'mapreduce' => 'participants',
            'map'=> $map,
            'reduce' => $reduce,
            'query' => array(),
            'out' => 'inline');
        
        $mongo = $this->getDataSource();
        $cursor = $mongo->mapReduce($query, $timeout);
        if ($cursor == false){ 
            return $results;
        }
        foreach ($cursor as $result) {
            $results[] = array(
                'x' => $result['_id'],
                'y' => $result['value']);
        }
        return array(array(
            'key' => __('opt-in'),
            'values' => $results));
    }

    
    public function getExportHeaders($conditions = null)
    {
        $headers = array(
            "phone",
            //"last-optin-date",
            //"last-optout-date",
            "tags");
        
        $distinctLabels = $this->getDistinctLabels($conditions);
        foreach ($distinctLabels as $distinctLabel) {
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
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),            
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),            
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,            
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,            
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
    }
    
    
    public static function cleanTags($inputTags=array())
    {
        $cleanedTags = array();
        if ($inputTags == null) {
            return $cleanedTags;
        }
        if (!is_array($inputTags)) {
            $inputTags = explode(',', $inputTags); 
        } 
        foreach ($inputTags as $tag) {
            $cleanedTags[] = trim(stripcslashes($tag), " \t\n\r\0\x0B,");
        }
        $cleanedTags = array_values(array_filter($cleanedTags));
        return $cleanedTags;
    }
    
    
    public static function cleanProfile($inputProfile) 
    {
        $cleanedProfile = array();
        if ($inputProfile == null) {
            return $cleanedProfile;
        } 
        
        if (!is_array($inputProfile)) {
            $inputProfile = explode(',', $inputProfile);
            foreach ($inputProfile as &$profile) {
                $profile = (strpos($profile, ':') !== false) ? $profile : $profile.":";
                list($label,$value)  = explode(":", $profile);
                $profile = array(
                    'label' => $label,
                    'value' => $value,
                    'raw' => null);
            }
        }
        foreach ($inputProfile as &$profile) {
            if (!isset($profile['label'])) {
                $profile = null;
                continue;
            }
            $profile['label'] = trim(stripcslashes($profile['label']));
            $profile['value'] = trim(stripcslashes($profile['value']));
            if ($profile['label'] == '') {
                $profile = null;
                continue;
            }
            if (!isset($profile['raw'])) {
                $profile['raw'] = null;
            }
        }
        $cleanedProfile = array_values(array_filter($inputProfile));
        return $cleanedProfile;
    }
    
    
    protected function _editEnrolls()
    {
        $updatedParticipantData   = $this->data;
        $originalParticipantData = $this->read(); 
        // $this->read() deletes already processed info and
        // and they must all be re-initialized.

        // ******** re-initialize already processed information *********/////
        $this->data['Participant'] = $updatedParticipantData['Participant'];
        // ******************************************************************////

        $programNow                = $this->ProgramSetting->getProgramTimeNow();

        if (!isset($updatedParticipantData['Participant']['enrolled']) or 
            !is_array($updatedParticipantData['Participant']['enrolled'])) {
            $this->data['Participant']['enrolled'] = array();
            return;
        }

        if (isset($updatedParticipantData['Participant']['enrolled']) and
            $updatedParticipantData['Participant']['enrolled'] == array()) {
            $this->data['Participant']['enrolled'] = array();
            return;
        }

        $this->data['Participant']['enrolled'] = array();
        foreach ($updatedParticipantData['Participant']['enrolled'] as $key => $value) {
            $dialogueId = (is_array($value)) ? $value['dialogue-id'] : $value;
            $enrollTime = (is_array($value)) ? $value['date-time'] : $programNow->format("Y-m-d\TH:i:s");
        

            if ($originalParticipantData == null || $originalParticipantData['Participant']['enrolled'] == array()) {
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
    

    public function save($data)
    {
        if (isset($data['Participant']['force-optin'])) { 
            $forceOptin = $data['Participant']['force-optin'];
            unset($data['Participant']['force-optin']);
            if ($forceOptin === 'true' && isset($data['Participant']['phone'])) {
                $participant = $this->find('first', array(
                    'conditions' => array('phone' => $this->cleanPhone($data['Participant']['phone']))));
                if (isset($participant['Participant'])) {
                    ## if optout, force optin by changing the create in an update
                    if ($participant['Participant']['last-optout-date'] != null) {
                        $this->id = $participant['Participant']['_id'];
                    }
                }
            }
        }
        return parent::save($data);
    }

    
    public function reset()
    {
        if (empty($this->id)) {
            throw new Exception("Reset needs id to be set.");
        }
        $participant = $this->read(null, $this->id);
        $resetedParticipant = array('Participant' => array(
            'phone' => $participant['Participant']['phone']));
        $this->create(); ##reinitialize the model
        $this->id = $participant['Participant']['_id'];
        return $this->save($resetedParticipant);
    }
    
    
    public function tagsFromStringToArray($tags) 
    {
        $tags = trim(stripcslashes($tags));
        return explode(",", $tags);
    }
    
    
    public function import($programUrl, $fileFullPath, $tags=null, $enrolled=null, $importTagsAndLabels='keep')
    {
        $defaultTags = $this->getDefaultImportedTag();
        if (isset($tags)) {
            $tags = $this->tagsFromStringToArray($tags);
            $tags = array_filter($tags);
            if (empty($tags)) {
                $tags = array();
            }
            foreach ($tags as $tag) {
                $valid = $this->validateTag($tag);
                if (!is_bool($valid)) {
                    array_push($this->importErrors, __("Error a tag is not valid: %s.", $tag)); 
                    return false;
                }
            }
            $tags = array_merge($defaultTags, $tags);
        } else {
            $tags = $defaultTags;
        }
        
        $ext = end(explode('.', $fileFullPath));
        if (!($ext == 'csv') and !($ext == 'xls')) {
            array_push($this->importErrors, __("The file format %s is not supported.", $ext)); 
            return false;
        }
        
        if ($ext == 'csv') {
            return $this->importCsv($programUrl, $fileFullPath, $tags, $enrolled, $importTagsAndLabels);
        } else if ($ext == 'xls') {
            return $this->importXls($programUrl, $fileFullPath, $tags, $enrolled, $importTagsAndLabels);
        }
        
    }
    
    public function addTags($participant, $savedTags) 
    {
        $tags = array();
        if (isset($participant['tags'])) {
            $tags = Participant::cleanTags($participant['tags']);
        }
        $savedTags = (is_array($savedTags) ? $savedTags : array());
        return array_unique(array_merge($tags, $savedTags));
    }

    public function addLabels($participant, $savedLabels)
    {
        $labels = array();
        if (isset($participant['profile'])) {
            $labels = Participant::cleanProfile($participant['profile']);
        }
        //$savedLabels = (is_array($savedLabels) ? $savedLabels : array());
        $merged = array_merge($labels, $savedLabels);
        $result = $this->_uniqueMultidimArray($merged, 'label');
        return $result;
    }


    protected function _uniqueMultidimArray($array, $key){
        $temp_array = array();
        $i = 0;
        $key_array = array();
        
        foreach($array as $val){
            if(!in_array($val[$key],$key_array)){
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    

    public function saveParticipantWithReport($participant, $enrolled, $importTagsAndLabels, $fileLine=null)
    {
        $this->create();
        $exist = $this->find('count', array('conditions' => array('phone' => $participant['phone'])));
        if ($exist) {
            if ($importTagsAndLabels == 'keep') {
                $report = array(
                    'phone' => $participant['phone'],
                    'saved' => false,
                    'exist-before' => true,
                    'message' => array($this->validate['phone']['isReallyUnique']['message']),
                    'line' => $fileLine);
                return $report;
            }
            
            $savedParticipant = $this->find('first', array('conditions' => array('phone' => $participant['phone'])));
            $this->id         = $savedParticipant['Participant']['_id']."";
            
            if ($importTagsAndLabels == 'replace') {
                $tags   = (isset($participant['tags']) ? $participant['tags'] : array());
                $labels = (isset($participant['profile']) ? $participant['profile'] : array());                
            } else {
                $tags   = $this->addTags($participant, $savedParticipant['Participant']['tags']);
                $labels = $this->addLabels($participant, $savedParticipant['Participant']['profile']);
            }        
            $participant            = $savedParticipant['Participant'];
            $participant['tags']    = $tags;
            $participant['profile'] = $labels;
        }
        
        if (isset($enrolled)) {
            $participant['enrolled'] = $enrolled;
        }
        
        $savedParticipant = $this->save($participant);
        if ($savedParticipant) {
            $report = array(
                'phone' => $savedParticipant['Participant']['phone'],
                'saved' => true,
                'exist-before' => $exist,
                'message' => array('Insert ok'),
                'line' => $fileLine);
            return $report;
        }
        $validationMessage = array();
        foreach ($this->validationErrors as $key => $error) {
            array_push($validationMessage, $this->validationErrors[$key][0]);
        }
        $report = array(
            'phone' => $participant['phone'],
            'saved' => false,
            'exist-before' => $exist,
            'message' => $validationMessage,
            'line' => $fileLine);
        return $report;
    }
    
    public function importJsonDecoded($programUrl, $jsonParticipants,  $tags=array(), $enrolled=null, $importTagsAndLabels='keep')
    {
        $count  = 0;
        $report = array();
        $defaultTags = array('imported');
        $tags = array_merge($defaultTags, $this->tagsFromStringToArray($tags));

        foreach($jsonParticipants as $jsonParticipant) {
            $participant          = array();
            $participant['phone'] = $this->cleanPhone($jsonParticipant->phone_number);
            $participant['tags']  = $tags;
            foreach ($jsonParticipant->profile as $key => $value) {
                $participant['profile'][] = array(
                    'label' => $key, 
                    'value' => $value->value,
                    'raw' => null);
            }
            //Save if not a duplicate
            $report[] = $this->saveParticipantWithReport(
                $participant,
                $enrolled,
                $importTagsAndLabels,
                $count + 1);
            $count++; 
        }
        return $report;
    }

    
    public function importCsv($programUrl, $fileFullPath, $tags, $enrolled, $importTagsAndLabels='keep')
    {
      
        $count        = 0;
        $entry        = array();
        $hasHeaders   = false;
        $hasTags      = false;  
        $headers      = array();
        $labels       = array();
        $report       = array();
        $uniqueNumber = array();
        
        if (($handle = fopen($fileFullPath,"r")) === false) {
            array_push($this->importErrors, __("The csv file cannot be open."));
            return false;
        }
        
        while (($entry = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($count == 0) {
                $index = 0;
                foreach ($entry as $header) {
                    $header = trim($header);
                    $headers[strtolower($header)] = array(
                        'name' => $header,
                        'index' => $index);
                    $index++;
                }
                if (isset($headers['phone'])) {
                    $hasHeaders = true;
                    $count++;
                    $labels = $this->arrayFilterOutNotLabel($headers);
                    continue;
                } else {
                    if (count($headers) > 1) {
                        array_push($this->importErrors, __("The file cannot be imported. The first line should be label names, the first label must be 'phone'.")); 
                        return false;
                    }
                    $headers = array(
                        'phone' => array('index' => 0),
                        'tags' => array('index' => 1));
                }
            }
            //skip empty rows
            if (!isset($entry[0])) {
                continue;
            }
            $participant          = array();
            //Get Phone
            $participant['phone'] = $this->cleanPhone($entry[$headers['phone']['index']]);
            //Get Tags
            $participant['tags']  = array();
            if (isset($headers['tags']) && isset($entry[$headers['tags']['index']])) {
                $participant['tags'] = explode(",", $entry[$headers['tags']['index']]);
            }
            $participant['tags'] = array_merge($tags, $participant['tags']);
            //Get Labels
            foreach ($labels as $label) {
                $value = $entry[$label['index']];
                if ($value == '') {
                    continue;
                }
                $participant['profile'][] = array(
                    'label' => $label['name'], 
                    'value' => (string) $value,
                    'raw' => null);
            }
            //Save if not a duplicate
            if (!isset($uniqueNumber[$participant['phone']])) {
                $uniqueNumber[$participant['phone']] = '';
                $report[]                            = $this->saveParticipantWithReport($participant, $enrolled, $importTagsAndLabels, $count + 1);
            }
            $count++; 
        }
        return $report;
    }
    
    
    private function arrayFilterOutNotLabel($input) 
    {
        $tmp = array_filter(
            array_keys($input), 
            function ($k) {
                return (!in_array($k, array('phone', 'tags')));
            }
            );
        return array_intersect_key($input, array_flip($tmp));
    }    
    
    
    private function importXls($programUrl, $fileFullPath, $tags, $enrolled, $importTagsAndLabels='keep')
    {
        require_once 'excel_reader2.php';
        
        $hasHeaders   = false;
        $headers      = array();
        $labels       = array();
        $uniqueNumber = array();
        $data         = new Spreadsheet_Excel_Reader($fileFullPath);
        
        if (strcasecmp('phone', $data->val(1,'A')) == 0) {
            $hasHeaders = true;
            for ( $j = 2; $j <= $data->colcount($sheet_index = 0); $j++) {
                if ($data->val(1, $j) == null || $data->val(1, $j) == '') {
                    break;
                }
                $header = trim($data->val(1, $j)); 
                $headers[strtolower($header)] = array(
                    'name' => $header, 
                    'index' => $j);
            }
            $labels = $this->arrayFilterOutNotLabel($headers);
        } else {
            if ($data->val(1, 'B')!=null) {
                array_push($this->importErrors, __("The file cannot be imported. The first line should be label names, the first label must be 'phone'."));
                return false;
            }
        }
        for ($i = ($hasHeaders) ? 2 : 1; $i <= $data->rowcount($sheet_index=0); $i++) {
            if ($data->val($i,'A')==null) {
                continue;
            }
            $participant = array();
            //Get Phone
            $participant['phone'] = $this->cleanPhone($data->val($i,'A'));
            //Get tags
            $participant['tags'] = array();
            if (isset($headers['tags'])) {
                $participant['tags'] = explode(",", $data->val($i, $headers['tags']['index']));
            }
            $participant['tags'] = array_merge($tags, $participant['tags']);
            //Get Labels
            foreach ($labels as $label) {
                if ($data->val($i, $label['index']) == null) 
                    continue;
                $participant['profile'][] = array(
                    'label' => $label['name'],
                    'value' => (string) $data->val($i, $label['index']),
                    'raw' => null);
            }
            if (!isset($uniqueNumber[$participant['phone']])) {
                $uniqueNumber[$participant['phone']] = '';
                $report[] = $this->saveParticipantWithReport($participant, $enrolled, $importTagsAndLabels, $i);
            }
        }
        return $report;
    }
    
    
    public $runActionsFields = array(
        'phone',
        'dialogue-id',
        'interaction-id',
        'answer');
    
    
    public function validateRunActions(&$data)
    {
        $runActionsErrors = array();
        foreach ($this->runActionsFields as $mandatoryField) {
            if (!isset($data[$mandatoryField])) {
                $runActionsErrors[$mandatoryField] = "This field is missing";
            }
        }
        if ($runActionsErrors != array()) {
            return $runActionsErrors;
        }
        $data['phone'] = $this->cleanPhone($data['phone']);
        if (!$this->find('count', array('conditions' => array('phone' => $data['phone'])))) {
            $runActionsErrors['phone'] = __("No participant with phone: %s.", $data['phone']);
        }
        $result = $this->Dialogue->isInteractionAnswerExists(
            $data['dialogue-id'],
            $data['interaction-id'],
            $data['answer']);
        if ($result != true || is_array($result)) {
            $runActionsErrors += $result;
        }
        if ($runActionsErrors === array()) {
            return true;
        }
        return $runActionsErrors;
    }
    
    
    //Filter variables and functions
    public $filterFields = array(
        'phone' => array(
            'label' => 'phone',
            'operators'=> array(
                'start-with' => array(
                    'parameter-type' => 'text'),
                'equal-to' => array(
                    'parameter-type' => 'text'),
                'start-with-any' => array(
                    'parameter-type' => 'text'),
                'simulated' => array(
                    'parameter-type' => 'none'))),
        'optin' => array(
            'label' => 'optin',
            'operators' => array(
                'now' => array(
                    'parameter-type' => 'none'),
                'date-from' => array(
                    'parameter-type' => 'date'),
                'date-to' => array(
                    'parameter-type' => 'date'))),
        'optout' => array(
            'label' => 'optout',
            'operators' => array(
                'now' =>array(
                    'parameter-type' => 'none'),
                'date-from' => array(
                    'parameter-type' => 'date'),
                'date-to' => array(
                    'parameter-type' => 'date'))),
        'enrolled' => array(
            'label' => 'enrolled',
            'operators' => array(
                'in' => array(
                    'parameter-type' => 'dialogue'),
                'not-in' =>  array(
                    'parameter-type' => 'dialogue'))),
        'tagged' => array(
            'label' => 'tagged',
            'operators' => array(
                'with' =>  array(
                    'parameter-type' => 'tag',
                    'conditional-action' => true),
                'not-with' =>  array(
                    'parameter-type' => 'tag',
                    'conditional-action' => true))),
        'labelled' => array(
            'label' => 'labelled',
            'operators' => array(
                'with' =>  array(
                    'parameter-type' => 'label',
                    'conditional-action' => true),
                'not-with' =>  array(
                    'parameter-type' => 'label',
                    'conditional-action' => true))),
        'schedule' => array(
            'label' => 'schedule',
            'operators' => array(
                'are-present' => array(
                    'parameter-type' => 'none',
                    'join' => array(
                        'field' => 'phone',
                        'model' => 'Schedule',
                        'function' => 'getUniqueParticipantPhone',
                        'parameters' => array('cursor' => true)))))
        );
    
    
    public $filterOperatorOptions = array(
        'all' => 'all',
        'any' => 'any'
        );
    
    
    public function fromFilterToQueryCondition($filterParam) 
    {
        $condition = array();
        
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
                $condition['last-optin-date']['$gt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
            } elseif ($filterParam[2] == 'date-to') {
                $condition['last-optin-date']['$lt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
            }
        } elseif ($filterParam[1] == 'optout') {
            if ($filterParam[2] == 'now') { 
                $condition['session-id'] = null;
            } elseif ($filterParam[2] =='date-from') {
                $condition['last-optout-date']['$gt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
            } elseif ($filterParam[2] =='date-to') {
                $condition['last-optout-date']['$lt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
            }
        } elseif ($filterParam[1] == 'phone') {
            if ($filterParam[2] == 'simulated') {
                $condition['simulate'] = true;
            } elseif ($filterParam[3]) {
                if ($filterParam[2] == 'start-with-any') {
                    $phoneNumbers = explode(",", str_replace(" ", "", $filterParam[3]));
                    if ($phoneNumbers) {
                        if (count($phoneNumbers) > 1) {
                            $or = array();
                            foreach ($phoneNumbers as $phoneNumber) {
                                $regex = array('$regex' => "^\\".$phoneNumber);
                                $or[] = array('phone' => $regex);
                            }
                            $condition['$or'] = $or;
                        } else {
                            $condition['phone'] = array('$regex' => "^\\".$phoneNumbers[0]);
                        }
                    } 
                } elseif ($filterParam[2] == 'start-with') {
                    $condition['phone'] = array('$regex' => "^\\".$filterParam[3]); 
                } elseif ($filterParam[2] == 'equal-to') {
                    $condition['phone'] = $filterParam[3];        
                }
            } else {
                $condition['phone'] = '';
            }
        } elseif ($filterParam[1]=='tagged') {
            if ($filterParam[2] == 'with') {
                $condition['tags'] = $filterParam[3];
            } elseif ($filterParam[2] == 'not-with') {
                $condition['tags'] = array('$ne' => $filterParam[3]);
            }
        } elseif ($filterParam[1] == 'labelled') {
            if ($filterParam[3]) {
                $label = explode(":", $filterParam[3]); 
                if ($filterParam[2] == 'with') {
                    $condition['profile'] = array(
                        '$elemMatch' => array(
                            'label' => $label[0],
                            'value' => $label[1])
                        );
                } elseif (($filterParam[2] == 'not-with')) {
                    $condition['profile'] = array(
                        '$elemMatch' => array(
                            '$or' => array(
                                array('label' => array('$ne' => $label[0])),
                                array('value' => array('$ne' => $label[1]))
                                )
                            )
                        );
                }
            } else {
                $condition['profile'] = '';
            }
        }
        return $condition;
    }
    
    
}
