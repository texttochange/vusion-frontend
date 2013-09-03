<?php
App::uses('VirtualModel', 'Model');
App::uses('VusionConst', 'Lib');


class Action extends VirtualModel
{
    var $name = 'action';
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
                        'proportional-tagging')),
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
                        'proportional-tagging' => array('proportional-tags'))),
                'message' => 'The action-type required field are not present.'
                )
            ),
        'enroll' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldOrValue', 'type-action', 'enrolling', 'delayed-enrolling'),
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
            'validDynamicContent' => array(
                'rule' => 'validDynamicContent',
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
            )      
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
        foreach($data[$field] as $subcondition) {
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
        if (!preg_match($operators[$subcondition['subcondition-operator']]['regex'], $subcondition['subcondition-parameter'])) {
            return array(
                'subcondition-parameter' => array(
                    $operators[$subcondition['subcondition-operator']]['message']));
        }
        return true;
    }
    
    
    public function validDynamicContent($field, $data)
    {
        if (isset($data[$field])) {
            preg_match_all(VusionConst::DYNAMIC_CONTENT_MATCHER_REGEX, $data[$field], $matches, PREG_SET_ORDER);
            $allowed = array("domain", "key1", "key2", "otherkey");
            foreach($matches as $match) {
                $match = array_intersect_key($match, array_flip($allowed));
                foreach ($match as $key=>$value) {
                    if (!preg_match(VusionConst::DYNAMIC_CONTENT_ALLOWED_REGEX, $value)) {
                        return __("To be used as dynamic content, '%s' can only be composed of letter(s), digit(s) and/or space(s).", $value);
                    }
                }
                if (!preg_match(VusionConst::DYNAMIC_CONTENT_DOMAIN_REGEX, $match['domain'])) {
                    return __("To be used as dynamic content, '%s' can only be either 'participant' or 'contentVariable'.", $match['domain']);
                }
                if ($match['domain'] == 'participant') {
                    if (isset($match['key2'])) {
                        return __("To be used as dynamic concent, participant only accept one key.");
                    }
                } else if ($match['domain'] == 'contentVariable') {
                    if (isset($match['otherkey'])) {
                        return __("To be used as dynamic concent, contentVariable only accept max two keys.");
                    }
                } 
            }
        }
        return true;
    }


}