<?php
App::uses('MongoModel', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('VusionConst', 'Lib');
App::uses('ValidationHelper', 'Lib');
App::uses('VusionValidation', 'Lib');


class ProgramSetting extends MongoModel
{
    
    
    var $specific    = true;
    var $name        = 'ProgramSetting';
    var $useDbConfig = 'mongo';
    
    
    function getModelVersion()
    {
        return "2";
    }
    
    
    function getRequiredFields($objectType=null)
    {
        return array(
            'key',
            'value'
            );
    }
    
    
    var $settings = array(
        'shortcode',
        'timezone',
        'international-prefix',
        'default-template-closed-question',
        'default-template-open-question',
        'default-template-unmatching-answer',
        'unmatching-answer-remove-reminder', 
        'customized-id',
        'double-matching-answer-feedback',
        'double-optin-error-feedback',
        'request-and-feedback-prioritized',
        'credit-type',
        'credit-number',
        'credit-from-date',
        'credit-to-date',
        'sms-forwarding-allowed',
        );
    
    public $validateSettings = array(
        'shortcode' => array(
            'notUsedKeyword' => array(
                'rule' => 'notUsedKeyword',
                'message' => null
                )
            ),
        'credit-type' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'The credit-type is required'
                ),
            'validValue' => array(
                'rule' => array('inList', array('none', 'outgoing-only', 'outgoing-incoming')),
                'message' => 'The type of credit is not supported',
                ),
            'validRequireFields' => array(
                'rule' => array(
                    'valueRequireFields', array(
                        'none' => array(),
                        'outgoing-only' => array('credit-number', 'credit-to-date', 'credit-from-date'),
                        'outgoing-incoming' => array('credit-number', 'credit-to-date', 'credit-from-date'),
                        )),
                'message' => 'The credit-type required fields are not present.'
                ),
            ),
        'credit-number' => array(
            'validateValue' => array(
                'rule' => array('custom', '/^\d+$/'),
                'message' => 'The number can only be an interger.',
                'required' => false
                ),
            ),
        'credit-from-date' => array(
            'validateDate' => array(
                'rule' => array('custom', VusionConst::DATE_TIME_REGEX),
                'message' => 'The format of the date has to be 15/02/2013.',
                'required' => false
                ),
            'lowerThan' => array(
                'rule' => array('lowerThan', 'credit-to-date'),
                'message' => 'This from date has to be before the to date.',
                'required' => false,
                ),
            ),
        'credit-to-date' => array(
            'validateDate' => array(
                'rule' => array('custom', VusionConst::DATE_TIME_REGEX),
                'message' => 'The format of the date has to be 15/02/2013.',
                'required' => false,
                ),
            'greaterThan' => array(
                'rule' => array('greaterThan', 'credit-from-date'),
                'message' => 'This to date has to be after the from date.',
                'required' => false,
                ),
            ),
        'sms-forwarding-allowed' => array(
            'validateValue' => array(
                'rule' => array('inList', array('none', 'full')),
                'message' => 'The sms forwarding value is not valid.',
                'required' => true,
                ),
            ),
        'double-matching-answer-feedback' => array(
            'validApostrophe' => array(
                'rule' => array('notRegex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                ),
            'validContentVariable' => array(
                'rule' => 'validContentVariable',
                'message' => 'noMessage'
                ),
            
            ),
        'double-optin-error-feedback' => array(
            'validApostrophe' => array(
                'rule' => array('notRegex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                ),
            'validContentVariable' => array(
                'rule' => 'validContentVariable',
                'message' => 'noMessage'
                )
            
            )
        );
    
    
    public $findMethods =  array(
        'programSetting' => true,
        'count' => true,
        'hasProgramSetting' => true,
        'getProgramSetting' => true,
        );
    
    
    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        
        $this->ValidationHelper = new ValidationHelper($this);
    }
    
    
    public function notRegex($check, $regex=null) 
    {
        if (!is_array($check))
            return VusionValidation::customNot($check, $regex[0]);
        $keys = array_keys($check);
        $key = $keys[0];
        return VusionValidation::customNot($check[$key], $regex[0]);
    }
    
    
    public function validContentVariable($check)
    {
        if (!is_array($check))
            return VusionValidation::validContentVariable($check);
        $keys = array_keys($check);
        $key = $keys[0];
        return VusionValidation::validContentVariable($check[$key]);
    }
    
    
    public function beforeValidate()
    {
        parent::beforeValidate();
        
        if (!in_array($this->data['ProgramSetting']['key'], $this->settings)) {
            return false;
        }
        
        if ($this->data['ProgramSetting']['value'] == '') {
            $this->data['ProgramSetting']['value'] = null;
        }   
        
        if ($this->data['ProgramSetting']['key'] == 'unmatching-answer-remove-reminder') {
            $this->data['ProgramSetting']['value'] = intval($this->data['ProgramSetting']['value']);
        }
        if ($this->data['ProgramSetting']['key'] == 'request-and-feedback-prioritized'
            and $this->data['ProgramSetting']['value'] == '1') {
        $this->data['ProgramSetting']['value'] = 'prioritized';
            }
            
    }
    
    
    protected function _findProgramSetting($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions']['ProgramSetting.key'] = $query['key'];
            return $query;
        }
        return $results;
    }
    
    
    protected function _findHasProgramSetting($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions']['ProgramSetting.key'] = $query['key'];
            $query['conditions']['ProgramSetting.value'] = $query['value'];
            return $query;
        }
        return $results;
    }
    
    
    protected function _findGetProgramSetting($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions']['ProgramSetting.key'] = $query['key'];
            return $query;
        }
        if (isset($results[0]))
            return $results[0]['ProgramSetting']['value'];
        else
        return null;
    }
    
    
    public function saveProgramSetting($key, $value) 
    {
        $setting = $this->find('all', array('conditions' => array('ProgramSetting.key' => $key)));
        
        $this->create();
        if ($setting) {
            $this->id = $setting[0]['ProgramSetting']['_id'];
        }
        return $this->save(
            array(
                'key' => $key,
                'value' => $value
                )
            );
    }
    
    
    public function saveProgramSettings($settings, $usedKeywords = array())
    {
        $this->usedKeywords = $usedKeywords;
        $settings = $this->_runBeforeValidate($settings);
        $validationErrors = $this->_runValidateRules($settings, $this->validateSettings);
        if (!is_bool($validationErrors) || !$validationErrors) {
            $this->validationErrors = $validationErrors;
            return false;
        }
        foreach ($settings as $key => $value) {
            $this->saveProgramSetting($key, $value);
        }
        return true;
    }
    
    
    public function getProgramSettings()
    {
        $rawSettings = $this->find('all');
        $settings = array();
        foreach ($rawSettings as $setting) {
            $settings[$setting['ProgramSetting']['key']] = $setting['ProgramSetting']['value'];
        }
        return $settings;
    }
    
    
    public function isNotPast($time)
    {      
        $programNow = $this->getProgramTimeNow();
        if ($programNow==null)
            return __("The program settings are incomplete. Please specificy the Timezone.");
        if ($time < $programNow)
            return false;
        return true;
    }
    
    
    public function notUsedKeyword($check, $data, $other)
    {
        if (isset($check['shortcode']) && $this->usedKeywords != array()){
            $errors = array();
            foreach ($this->usedKeywords as $keyword => $details) {
                $errors[] = DialogueHelper::foundKeywordsToMessage(
                    $this->databaseName, $keyword, $details);
            }
            return $errors;
        }
        return true;
    }
    
    
    public function getProgramTimeNow()
    {
        $now = new DateTime('now');
        $programTimezone = $this->find('getProgramSetting', array('key' => 'timezone'));
        if ($programTimezone == null)
            return null;
        
        date_timezone_set($now, timezone_open($programTimezone));        
        return $now;       
    }
    
    
    public function hasRequired()
    {
        $shortCode = $this->find('getProgramSetting', array('key'=>'shortcode'));
        $timezone = $this->find('getProgramSetting', array('key'=>'timezone'));        
        if ($shortCode and $timezone) {
            return true;
        }
        return false;
    }
    
    
    ## function required because the Setting model has a bad design: key/value
    ## This key/value design to be replace in the future but in the mean time
    ## one need a validation function to be run before.
    protected function _runValidateRules($data, $validationRules)    
    {
        return $this->ValidationHelper->runValidationRules($data, $validationRules);
    }
    
    
    protected function _runBeforeValidate($settings) 
    {
        if (!isset($settings['credit-type']) || $settings['credit-type'] == null) {
            $settings['credit-type'] = 'none';
        }
        
        if (isset($settings['credit-from-date'])) {
            $settings['credit-from-date'] = DialogueHelper::ConvertDateFormat($settings['credit-from-date']);
        }
        if (isset($settings['credit-to-date'])) {
            $settings['credit-to-date'] = DialogueHelper::ConvertDateFormat($settings['credit-to-date']);
        }
        if (!isset($settings['sms-forwarding-allowed'])) {
            $settings['sms-forwarding-allowed'] = 'full';
        } else if ($settings['sms-forwarding-allowed'] == '0') {
            $settings['sms-forwarding-allowed'] = 'none';   
        }
        return $settings;
    }
    
    
}
