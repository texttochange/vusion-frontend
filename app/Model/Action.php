<?php
App::uses('MissingField', 'Lib');


class Action
{

    var $modelName = 'action';
    var $modelVersion = '1'; 

    var $payload = array();

    var $fields = array('type-action');
   
    public function __construct() {

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

    }

    public function beforeValidate($action)
    {
        foreach($this->fields as $field) {
            if (!isset($action[$field])){
                if (isset($action['type-answer-action'])) {
                    $action['type-action'] = $action['type-answer-action'];
                } else {
                    throw new MissingField("$field is missing in an Action.");
                }
            }    
        }
        $action['object-type'] = $this->modelName;
        $action['model-version'] = $this->modelVersion;
        if ($this->ACTION_TYPE[$action['type-action']] == null) {
            return $action;
        }
        foreach($this->ACTION_TYPE[$action['type-action']] as $field => $check) {
            if (is_callable($check)) { 
                if (!call_user_func($check, $action[$field])){
                    throw new FieldValueIncorrect("Action Field:$field Value:$action[$field] is incorrect.");
                }
            } else {
                foreach($check as $fieldMore => $checkMore) {
                    if (!call_user_func($checkMore, $action[$field][$fieldMore])){
                        throw new FieldValueIncorrect("Action Field:$fieldMore Value:$action[$field][$fieldMore] is incorrect.");
                    }   
                }
            }
        }
        return $action;
    }

}