<?php
App::uses('VusionConst', 'Lib');


class Action
{

    var $modelName = 'action';
    var $modelVersion = '1'; 

    var $payload = array();

    var $fields = array('type-action');
   
    var $validationErrors = array();

    public $validate = array(
            'optin' => null,
            'optout' => null,
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
            'reset' => null,
            'feedback' => array(
                'content' => array(
                    'notForbiddenApostrophe' => array(
                        'rule' => 'notForbiddenApostrophe',
                        'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE))));


    
    public function __construct() 
    {
        /*    
        $this->ACTION_TYPE = array(
            'optin' => null,
            'optout' => null,
            'enrolling' => array('enroll' => function($v) {return true;}),
            'delayed-enrolling' => array(
                'enroll' => function($v) {return true;},
                'offset-days' => array(
                    'days' => function($v) { return ($v!=null);},
                    'at-time'=> function($v) { return ($v!=null);})),
            'tagging' => array('tag' =>  function($v) {return ($v!=null);}),
            'reset' => null,
            'feedback' => null);
            */
    }


    public function create()
    {
        $this->validationErrors = array();
    }


    public function trimArray($Input){
 
        if (!is_array($Input))
            return trim(stripcslashes($Input));
 
        return array_map(array($this,'TrimArray'), $Input);
    }

    public function beforeValidate($action)
    {

        $action = $this->trimArray($action);

        /*
        foreach($this->fields as $field) {
            if (!isset($action[$field])){
                if (isset($action['type-answer-action'])) {
                    $action['type-action'] = $action['type-answer-action'];
                } else {
                    throw new MissingField("$field is missing in an Action.");
                }
            }    
        }*/

        $action['object-type'] = $this->modelName;
        $action['model-version'] = $this->modelVersion;
        /*
        if ($this->ACTION_TYPE[$action['type-action']] == null) {
            return $action;
        }
        
        foreach($this->ACTION_TYPE[$action['type-action']] as $field => $check) {
            if (is_callable($check)) { 
                if (!call_user_func($check, $ac tion[$field])){
                    throw new FieldValueIncorrect("Action Field:$field Value:$action[$field] is incorrect.");
                }
            } else {
                foreach($check as $fieldMore => $checkMore) {
                    if (!call_user_func($checkMore, $action[$field][$fieldMore])){
                        throw new FieldValueIncorrect("Action Field:$fieldMore Value:$action[$field][$fieldMore] is incorrect.");
                    }   
                }
            }
        }*/
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


    public function valid($action)
    {
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