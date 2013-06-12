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
                        'condition' => array('subconditions', 'condition-operator')),
                    'message' => 'The field required by set-condition are not present.'
                    )
                ),
            ),
        'condition-operator' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'set-condition', 'condition'),
                'message' => 'The set-condition field has not the valid value.',
                ),
            'validOpeartor' => array(
                'rule' => array('inlist', array('all', 'any')),
                'message' => 'The condition-operator value is not valid.'
                )
            ),
        'subconditions' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'set-condition', 'condition'),
                'message' => 'The set-condition field has not the valid value.',
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
                        'optin', 'optout', 'enrolling', 'delayed-enrolling', 'tagging', 'reset', 'feedback')),
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
                        'feedback' => array('content'))),
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
                )
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
        $this->_setDefault('set-condition', null);
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
        if (!is_int($data[$field])) {
            return false;
        }
        if (!(intval($data[$field]) >= 1)) {
            return false;
        }
        return true;
    }


    public function validSubconditions($field, $data)
    {
        if (!isset($data[$field])) {
            return true;
        }
        $count = 0;
        foreach($data[$field] as $subcondition) {
            $result = $this->_runValidateRules($subcondition, $this->validateSubcondition);
            if (is_array($result)) {
                $this->validationErrors[$field][$count] = $this->Action->validationErrors;
                return false;
            }
            $count++;
        }
        return true;
    }



}