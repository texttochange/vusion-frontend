<?php
App::uses('VirtualModel', 'Model');
App::uses('VusionConst', 'Lib');
App::uses('VusionValidation', 'Lib');
App::uses('ContentVariableTable', 'Model');
App::uses('ProgramSpecificMongoModel', 'Model');


class Action extends VirtualModel
{
    var $name    = 'action';
    var $version = '2'; 
    
    var $fields = array(
        'set-condition',
        'type-action');
    
    
    public $validate = array(
        'set-condition' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'The field set-condition is missing.'
                ),
            'notempty' => array(
                'rule' => array('inlist', array(null, 'condition')),
                'message' => 'The value of set-condition is not valid.'
                ),
            'valueRequireField' => array(
                'rule' => array(
                    'valueRequireFields', array(
                        'condition' => array('condition-operator', 'subconditions')),
                    'message' => 'The field required by set-condition are not present.'
                    )
                ),
            ),
        'condition-operator' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'set-condition', 'condition'),
                'message' => 'The set-condition field has not the valid value.',
                ),
            'validOperator' => array(
                'rule' => array('inlist', array('all-subconditions', 'any-subconditions')),
                'message' => 'The condition-operator value is not valid.'
                )
            ),
        'subconditions' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'set-condition', 'condition'),
                'message' => 'The set-condition field has not the valid value.',
                ),
            'notEmptyArray' => array(
                'rule' => 'notEmptyArray',
                'message' => 'At least one subconditions has to be set.'
                ),
            'validSubconditions' => array(
                'rule' => 'validSubconditions',
                'message' => 'noMessage'
                )
            ),
        'type-action' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'The type-action field is missing.'
                ),
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The field-action cannot be empty.'
                ),
            'validValue' => array(
                'rule' => array(
                    'inlist', array(
                        'optin', 
                        'optout', 
                        'enrolling', 
                        'delayed-enrolling', 
                        'tagging', 
                        'reset', 
                        'feedback',
                        'proportional-tagging',
                        'proportional-labelling',
                        'url-forwarding',
                        'sms-forwarding',
                        'sms-invite',
                        'save-content-variable')),
                'message' => 'The type-action value is not valid.'
                ),
            'valueRequireFields' => array(
                'rule' => array(
                    'valueRequireFields', array(
                        'optin' => array(),
                        'optout' => array(),
                        'enrolling' => array('enroll'),
                        'delayed-enrolling' => array('enroll', 'offset-days'),
                        'tagging' => array('tag'),
                        'reset' => array(),
                        'feedback' => array('content'),
                        'proportional-tagging' => array('set-only-optin-count', 'proportional-tags'),
                        'proportional-labelling' => array('set-only-optin-count', 'label-name', 'proportional-labels'),
                        'url-forwarding' => array('forward-url'),
                        'sms-forwarding' => array('forward-to', 'forward-content', 'set-forward-message-condition'),
                        'sms-invite' => array('invite-content', 'invitee-tag', 'feedback-inviter'),
                        'save-content-variable' => array(
                            'scv-attached-table', 
                            'scv-row-keys',
                            'scv-col-key',
                            'scv-extra-cvs'))),
                'message' => 'The action-type required field are not present.'
                )
            ),
        'enroll' => array(
            'requiredConditional' => array (
                'rule' => array(
                    'requiredConditionalFieldOrValue',
                    'type-action',
                    'enrolling',
                    'delayed-enrolling'),
                'message' => 'The enroll field require an enrolling or delayed-enrolling action.',
                ),
            ),
        'offset-days' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-action', 'delayed-enrolling'),
                'message' => 'The enroll field require an enrolling or delayed-enrolling action.',
                ),
            'validSubfield' => array(
                'rule' => 'validOffsetDays',
                'message' => 'noMessage'
                ),
            ),
        'tag' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-action', 'tagging'),
                'message' => 'The content field require an tagging action.',
                ),
            'validTag' => array(
                'rule' => array('regex', VusionConst::TAG_REGEX),
                'message' => VusionConst::TAG_FAIL_MESSAGE
                ),
            ),
        'content' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-action', 'feedback'),
                'message' => 'The content field require an feedback action.',
                ),
            'notForbiddenApostrophe' => array(
                'rule' => array('notregex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                ),
            'validContentVariable' => array(
                'rule' => array('validContentVariable', VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE),
                'message' => 'noMessage'
                ),
            ),
        'proportional-tags' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-action', 'proportional-tagging'),
                'message' => 'The proportional-tags field require an proportional-tagging action.',
                ),
            'validProportionalTags' => array(
                'rule' => 'validProportionalTags',
                'message' => 'noMessage',
                ),
            ),
        'proportional-labels' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-action', 'proportional-labelling'),
                'message' => 'The proportional-labels field require an proportional-labelling action.',
                ),
            'validProportionalLabels' => array(
                'rule' => 'validProportionalLabels',
                'message' => 'noMessage',
                ),
            ),
        'set-only-optin-count' => array(
            'validValue' => array(
                'rule' => array('inList', array(null, 'only-optin-count')),
                'message' => 'The field set-only-optin-count doesn\'t have a valide value.'
                )
            ),
        'label-name' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-action', 'proportional-labelling'),
                'message' => 'The label-name is require in proportional-labelling action.',
                ),
            'validLabelName' => array(
                'rule' => array('regex', VusionConst::LABEL_REGEX),
                'message' => VusionConst::LABEL_FAIL_MESSAGE
                ),
            ),
        'forward-url' => array(            
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-action', 'url-forwarding'),
                'message' => 'The forwarding field require an url field.',
                ),
            'validUrlFormat' => array(
                'rule' => array('regex', VusionConst::FORWARD_URL_REGEX),
                'message' => VusionConst::FORWARD_URL_FAIL_MESSAGE,
                ),
            'validUrlReplacement' => array(
                'rule' => array(
                    'validUrlReplacement', array(
                        '[MESSAGE]',
                        '[FROM]',
                        '[TO]',
                        '[PROGRAM]')),
                'message' => 'noMessage',
                ),
            ),
        'forward-to' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-action', 'sms-forwarding'),
                'message' => 'The Receiver Tagged or Labelled Conditions field is required.',
                ),
            'validForwardTo' => array(
                'rule' => array('validForwardTo'),
                'message' => 'noMessage'
                ),
            ),
        'forward-content' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-action', 'sms-forwarding'),
                'message' => 'The content field require an SMS Forward action.',
                ),
            'notForbiddenApostrophe' => array(
                'rule' => array('notregex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                ),
            'validContentVariable' => array(
                'rule' => array('validContentVariable', VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE),
                'message' => 'noMessage'
                ),
            ),
        'set-forward-message-condition' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-action', 'sms-forwarding'),
                'message' => 'The field is required.',
                ),
            'validValue' => array(
                'rule' => array('inList', array(null, 'forward-message-condition')),
                'message' => 'This choice is not valide.'
                ),
            ),
        'forward-message-condition-type' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'set-forward-message-condition', 'forward-message-condition'),
                'message' => 'The set-forward-message-condition field has not the valid value.',
                ),
            'validValue' => array(
                'rule' => array('inlist', array('phone-number')),
                'message' => 'The field value is not valid.'
                )
            ),
        "forward-message-no-participant-feedback" => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'set-forward-message-condition', 'forward-message-condition'),
                'message' => 'The content field require an SMS Forward action.',
                ),
            'notForbiddenApostrophe' => array(
                'rule' => array('notregex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                ),
            'validContentVariable' => array(
                'rule' => array('validContentVariable', VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE),
                'message' => 'noMessage'
                ),
            ),
        'invite-content' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-action', 'sms-invite'),
                'message' => 'The content field require an SMS Invite action.',
                ),
            'notForbiddenApostrophe' => array(
                'rule' => array('notregex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                ),
            'validContentVariable' => array(
                'rule' => array('validContentVariable', VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE),
                'message' => 'noMessage'
                ),
            ),
        'invitee-tag' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-action', 'sms-invite'),
                'message' => 'The Invitee Tagged Condition field is required.',
                ),
            'validTag' => array(
                'rule' => array('regex', VusionConst::TAG_REGEX),
                'message' => VusionConst::TAG_FAIL_MESSAGE
                ),
            ),
        'feedback-inviter' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-action', 'sms-invite'),
                'message' => 'The content field require an SMS Invite action.',
                ),
            'notForbiddenApostrophe' => array(
                'rule' => array('notregex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                ),
            'validContentVariable' => array(
                'rule' => array('validContentVariable', VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE),
                'message' => 'noMessage'
                ),
            ),
        'keep-tags' => array(
            'validTag' => array(
                'rule' => array('regex', VusionConst::TAG_LIST_REGEX),
                'message' => VusionConst::TAG_LIST_FAIL_MESSAGE
                ),
            ),
        'keep-labels' => array(
            'validLabelName' => array(
                'rule' => array('regex', VusionConst::LABEL_NAMES_LIST_REGEX),
                'message' => VusionConst::LABEL_NAMES_LIST_FAIL_MESSAGE
                ),
            ),
        'scv-attached-table' => array(
            'validateTableId' => array(
                'rule' => array('validTableId'),
                'message' => 'Must reference an existing table id.'
                ),
            ),
        'scv-row-keys' => array(
            'validateRowKeys' => array(
                'rule' => array('validScvRowKeys'),
                'message' => 'noMessage'
                ),
            ),
        'scv-col-key' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter header name for this column',
                ),
            'validHeader' => array(
                'rule' => array('regex', VusionConst::CONTENT_VARIABLE_KEY_REGEX),
                'message' => VusionConst::CONTENT_VARIABLE_KEY_FAIL_MESSAGE,
                )
            ),
        'scv-extra-cvs' => array(
            'validExtraCvs' => array(
                'rule' => array('validScvExtraCvs'),
                'message' => 'noMessage'
                ),
            ),
        );
    
    public $validateOffsetDays = array(
        'days' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'The days field is required.'
                ),
            'validValue' => array(
                'rule' => 'validDays',
                'message' => 'Offset days has to be greater or equal to 1.'
                )
            ),
        'at-time' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'The at-time field is required.'
                ),
            'valid' => array(
                'rule' => array('regex', VusionConst::ATTIME_REGEX),
                'message' => VusionConst::ATTIME_FAIL_MESSAGE,
                )
            )
        );
    
    
    public $validateSubcondition = array(
        'subcondition-field' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'The field is required.'
                ),
            ),
        'subcondition-operator' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'The operator is required.'
                ),
            ),
        'subcondition-parameter' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'The parameter is required.'
                ),
            ),
        );
    
    
    public $validateSubconditionValues = array(
        'labelled' => array(
            'with' => array(
                'regex' => VusionConst::LABEL_FULL_REGEX,
                'message' => VusionConst::LABEL_FULL_FAIL_MESSAGE
                ),
            'not-with' => array(
                'regex' => VusionConst::LABEL_FULL_REGEX,
                'message' => VusionConst::LABEL_FULL_FAIL_MESSAGE,
                ),
            ),
        'tagged' => array(
            'with' => array(
                'regex' => VusionConst::TAG_REGEX,
                'message' => VusionConst::TAG_FAIL_MESSAGE,
                ),
            'not-with' => array(
                'regex' => VusionConst::TAG_REGEX,
                'message' => VusionConst::TAG_FAIL_MESSAGE,
                ),
            )
        );
    

    public $validateProportionalTag = array(
        'tag' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'The tag is required.'
                ),
            'validValue' => array(
                'rule' => array('regex', VusionConst::TAG_REGEX),
                'message' => VusionConst::TAG_FAIL_MESSAGE
                ),
            ),
        'weight' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'The weight is required.'
                ),
            'validValue' => array(
                'rule' => array('regex', '/^\d+$/'),
                'message' => 'The weight value can only be a integer.'
                ),
            ),
        );


    public $validateProportionalLabel = array(
        'label-value' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'The label value is required.'
                ),
            'validValue' => array(
                'rule' => array('regex', VusionConst::LABEL_VALUE_REGEX),
                'message' => VusionConst::LABEL_VALUE_FAIL_MESSAGE
                ),
            ),
        'weight' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'The weight is required.'
                ),
            'validValue' => array(
                'rule' => array('regex', '/^\d+$/'),
                'message' => 'The weight value can only be a integer.'
                ),
            ),
        );

    public $validateScvRowKeys = array(
        'name' => array( 
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter header name for this column',
                ),
            'validHeader' => array(
                'rule' => array('regex', VusionConst::CONTENT_VARIABLE_KEY_REGEX),
                'message' => VusionConst::CONTENT_VARIABLE_KEY_FAIL_MESSAGE
                ),
            'validScvRowKey' => array(
                'rule' => array('validScvRowKey'),
                'message' => 'The header has to be present as key in the table.'
                )
            ),
        'value' => array()
        );

    public $validateScvExtraCv = array(
        'name' => array( 
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter header name for this column',
                ),
            'validHeader' => array(
                'rule' => array('regex', VusionConst::CONTENT_VARIABLE_KEY_REGEX),
                'message' => VusionConst::CONTENT_VARIABLE_KEY_FAIL_MESSAGE
                ),
            'validScvExtraCvNotKey' => array(
                'rule' => array('validScvExtraCvNotKey'),
                'message' => 'The header cannot be a key in the table.'
                )
            ),
        'value' => array()
        );
    

    public function __construct($databaseName) 
    {
        $this->ContentVariableTable = ProgramSpecificMongoModel::init(
            'ContentVariableTable', $databaseName, true);
    }


    public function trimArray($Input)
    {
        if (!is_array($Input))
            return trim(stripcslashes($Input));
        
        return array_map(array($this,'TrimArray'), $Input);
    }
    
    
    public function beforeValidate()
    {
        parent::beforeValidate();
        if (isset($this->data['type-answer-action'])) {
            $this->data['type-action'] = $this->data['type-answer-action'];
            unset($this->data['type-answer-action']);
        }
        $this->_setDefault('type-action', null);
        if ($this->data['type-action'] == 'sms-forwarding') {
            $this->_setDefault('set-forward-message-condition', null);
            $this->_setDefault('forward-to', null);
        }
        if ($this->data['type-action'] == 'save-content-variable') {
            $this->_setDefault('scv-row-keys', null);
            $this->_setDefault('scv-extra-cvs', null);    
        }
        if (in_array($this->data['type-action'], array('proportional-tagging', 'proportional-labelling'))) {
            $this->_setDefault('set-only-optin-count', null);
        }
        $this->_setDefault('set-condition', null);
        if ($this->data['set-condition'] == 'condition') {
            $this->_setDefault('condition-operator', null);
            $this->_setDefault('subconditions', array());
            foreach ($this->data['subconditions'] as &$subconditions) {
                $this->_setDefaultSubfield($subconditions, 'subcondition-field', null);
                $this->_setDefaultSubfield($subconditions, 'subcondition-operator', null); 
                $this->_setDefaultSubfield($subconditions, 'subcondition-parameter', null); 
            }
        }
        return true;
    }
    
    
    public function validOffsetDays($field, $data)
    {
        if (!isset($data[$field])) {
            return true;
        }
        $result = $this->_runValidateRules($data[$field], $this->validateOffsetDays);
        if (is_array($result)) {
            return $result;
        }
        return true;
    }
    
    
    public function validDays($field, $data)
    {
        if (!isset($data[$field])) {
            return true;
        }
        if (!is_int((int)$data[$field])) {
            return false;
        }
        if (intval($data[$field] >= 1)) {
            return true;
        }
        return false;
    }
    
    
    public function validSubconditions($field, $data)
    {
        if (!isset($data[$field])) {
            return true;
        }
        $count = 0;
        $validationError = array();
        foreach ($data[$field] as $subcondition) {
            $result = $this->_runValidateRules($subcondition, $this->validateSubcondition);
            if (is_bool($result) && $result) {
                $result = $this->validSubconditionValue($subcondition);
            }
            if (is_array($result)) {
                $validationError[$count] = $result;
            }
            $count++;
        }
        if ($validationError != array()) {
            return $validationError;
        }
        return true;
    }
    
    
    public function validProportionalTags($field, $data)
    {
        return $this->validList($field, $data, $this->validateProportionalTag);
    }
    
    
    public function validProportionalLabels($field, $data)
    {
        return $this->validList($field, $data, $this->validateProportionalLabel);
    }
    
    
    public function validSubconditionValue($subcondition)
    {
        if (!isset($this->validateSubconditionValues[$subcondition['subcondition-field']])) {
            return array(
                'subcondition-field' => array(
                    __("The field value '%s' is not valid.", $subcondition['subcondition-field']))); 
        }
        $operators = $this->validateSubconditionValues[$subcondition['subcondition-field']]; 
        if (!isset($operators[$subcondition['subcondition-operator']])) {
            return array(
                'subcondition-operator' => array( 
                    __("The operator value '%s' is not valid.", $subcondition['subcondition-operator'])));
        }
        $subconditionOperator  =  $subcondition['subcondition-operator'];
        $subconditionParameter = $subcondition['subcondition-parameter'];
        if (!preg_match($operators[$subconditionOperator]['regex'], $subconditionParameter)) {
            return array(
                'subcondition-parameter' => array(
                    $operators[$subcondition['subcondition-operator']]['message']));
        }
        return true;
    }
    
    
    public function validContentVariable($field, $data, $allowedDomain)
    {
        return VusionValidation::validCustomizeContent($field, $data, $allowedDomain);
    }
    
    
    public function validUrlReplacement($field, $data, $urlReplacement) 
    {
        if (!isset($data[$field])) {
            return true;
        }
        $matches = array();
        preg_match(VusionConst::FORWARD_URL_REPLACEMENT_REGEX, $data[$field], $matches);
        foreach ($matches as $match) {
            if (!in_array($match, $urlReplacement)) {
                return "The replacement $match is not allowed.";
            }
        }
        return true;
    }
    
    public function validForwardTo($field, $data) 
    {
        if (!isset($data[$field])) {
            return true;
        }
        
        $selectors = explode(",", $data[$field]);
        $selectors = array_map('trim', $selectors);
        foreach ($selectors as $selector) {
            if (!preg_match(VusionConst::TAG_REGEX, $selector) 
                && !preg_match(VusionConst::LABEL_FULL_REGEX, $selector) 
            && !preg_match(VusionConst::LABEL_SELECTOR_REGEX, $selector)) {
            return __("The selector %s should be a tags or a labels. For labels, their value could be matching the sender by using content variable notation.", $selector);
            }
        }
        return true;
    }


    public function validTableId($field, $data)
    {
        if (!isset($data[$field])) {
            return true;
        }
        if ($this->ContentVariableTable->exists($data[$field])) {
            return true;
        }
        return false;
    }


    public function validScvRowKey($field, $data)
    {
        if (!isset($data[$field]) || !isset($this->data['scv-attached-table'])) {
            return true;
        }
        if (!$this->ContentVariableTable->hasKeyHeader($this->data['scv-attached-table'], $data[$field])) {
            return false;
        }
        return true;
    }


    public function validScvRowKeys($field, $data)
    {
        return $this->validList($field, $data, $this->validateScvRowKeys);
    }


    public function validScvExtraCvNotKey($field, $data)
    {
        if (!isset($data[$field]) || !isset($this->data['scv-attached-table'])) {
            return true;
        }
        if ($this->ContentVariableTable->hasKeyHeader($this->data['scv-attached-table'], $data[$field])) {
            return false;
        }
        return true;
    }


    public function validScvExtraCvs($field, $data)
    {
        return $this->validList($field, $data, $this->validateScvExtraCv);
    }

}