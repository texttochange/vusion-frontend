<?php
App::uses('VirtualModel', 'Model');
App::uses('VusionConst', 'Lib');


class Action extends VirtualModel
{
    var $modelName = 'action';
    var $modelVersion = '1'; 

    var $fields = array('type-action');
   
    public $validate = array(
            'optin' => array(),
            'optout' => array(),
            'enrolling' => array(
                'enroll' => null),
            'delayed-enrolling' => array(
                'enroll' => array(
                    'notempty' => array(
                        'rule' => 'notempty',
                        'message' => 'The enroll field cannot be empty.'),
                    ),
                'offset-days' => array(
                    'containsDayTime' => array(
                        'rule' => array('contains' => array('days', 'at-time')),
                        'message' => 'The days and time has to be set.'),
                    'validOffsetDays' => array(
                        'rule' => 'validOffsetDays',
                        'message' => 'The offset days is not valid.'))),
            'tagging' => array(
                'tag' =>  array(
                    'validTag' => array(
                        'rule' => 'validTag',
                        'message' => VusionConst::TAG_FAIL_MESSAGE))),
            'reset' => array(),
            'feedback' => array(
                'content' => array(
                    'notForbiddenApostrophe' => array(
                        'rule' => 'notForbiddenApostrophe',
                        'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE))));


    public function trimArray($Input)
    {
        if (!is_array($Input))
            return trim(stripcslashes($Input));
 
        return array_map(array($this,'TrimArray'), $Input);
    }


    public function beforeValidate($action)
    {
        $action = $this->trimArray($action);

        $action['object-type'] = $this->modelName;
        $action['model-version'] = $this->modelVersion;
       
        return $action;
    }


    public function notempty($field, $action)
    {
        if ($action[$field]==null) {
            return false;
        }
        return true;
    }


    public function contains($field, $keys, $action) 
    {
        foreach($keys as $key) {
            if (!isset($action[$field][$key])) {
                return false;
            }
        }
        return true;
    }


    public function notForbiddenApostrophe($field, $action)
    {
        if (preg_match(VusionConst::APOSTROPHE_REGEX, $action[$field])) {
            return false;
        }
        return true;
    }


    public function validOffsetDays($field, $action) 
    {
        if (intval($action['offset-days']['days']) < 1) {
            return false;
        }
        $atTime = explode(':', $action['offset-days']['at-time']);
        if (0 <= intval($atTime[0]) && intval($atTime[0]) < 24 && 0<= intval($atTime[1]) && intval($atTime[1]) < 60) {
            return false;
        }
        return true;
    }


    public function validTag($field, $action)
    {
        $tagRegex = VusionConst::TAG_REGEX;
        if (!preg_match($tagRegex, $action[$field])) {
            return false;
        }
        return true;
    }


    public function validates()
    {
        $action = $this->data;
        
        if (!isset($this->validate[$action['type-action']])) {
            array_push($this->validationErrors, __("The action %s is not supported.", $action['type-action']));
            return false;
        }
        
        if ($this->validate[$action['type-action']] == null) {
            return true;
        }

        foreach($this->validate[$action['type-action']] as $field => $rules) {
            if (!isset($action[$field])) {
                array_push($this->validationErrors, __("The field %s is missing.", $field));
                return false;
            }            
            foreach($rules as $name => $rule) {
                if (!is_array($rule['rule'])) {
                    if (!call_user_func(array($this, $rule['rule']), $field, $action)) {
                        array_push($this->validationErrors, $rule['message']);
                        break;
                    }
                } else {
                    $args = reset($rule['rule']);
                    $func = key($rule['rule']);
                    if (!call_user_func(array($this, $func), $field, $args, $action)) {
                        array_push($this->validationErrors, $rule['message']);
                        break;
                    }
                }
            }
        }
        if ($this->validationErrors != array()) {
            return false;
        }
        return true;
    }

}