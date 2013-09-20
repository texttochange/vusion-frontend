<?php

App::uses('MongoModel', 'Model');
App::uses('VusionConst', 'Lib');

class ContentVariable extends MongoModel
{
    var $specific = true;
    var $name = 'ContentVariable';
    var $useDbConfig = 'mongo';
    
    function getModelVersion()
    {
        return '2';
    }
    
    
    function getRequiredFields($objectType=null)
    {
        return array(
            'keys',
            'table',
            'value'
            );
    }
    
    
    public $validate = array(
        'keys' => array(
            'validateKeys' => array(
                'rule' => 'validateKeys',
                'message' => 'noMessage'
                ),
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This keys pair already exists. Please choose another.'
                ),
            ),
        'table' => array(),
        'value' => array(
            'validateValue' => array(
                'rule' => array('custom', VusionConst::CONTENT_VARIABLE_VALUE_REGEX),
                'message' => VusionConst::CONTENT_VARIABLE_VALUE_FAIL_MESSAGE,
                ),
            ),
        );
    
    
    public function validateKeys($check)
    {
        $keyString = '';
        $regex = VusionConst::CONTENT_VARIABLE_KEYS_FULL_REGEX;
        $index = 0;
        foreach ($check['keys'] as $keyItem) {
            if (is_string($validationError = $this->validateKey($keyItem))) {
               if (!isset($this->validationErrors['keys'])) {
                    $this->validationErrors['keys'] = array();
                }
                $this->validationErrors['keys'][$index] = $validationError;
            }
            $index++;
            $keyString = $keyString . $keyItem['key'] . ".";
        }
        if (isset($this->validationErrors['keys'])) {
            return false;
        } else if (!preg_match($regex, rtrim($keyString, '.'))) {
            return VusionConst::CONTENT_VARIABLE_KEYS_FULL_FAIL_MESSAGE;
        }
        return true;
    }
    
    
    public function validateKey($check) {
        $regex = VusionConst::CONTENT_VARIABLE_KEY_REGEX;
        if (!preg_match($regex, $check['key'])) {
            return VusionConst::CONTENT_VARIABLE_KEY_FAIL_MESSAGE;
        }
        return true;
    }
    
    
    public function isUnique($check)
    {
        foreach($check['keys'] as &$key) {
            $key = $key['key'];
        }
        if ($this->id) {
            $conditions = array(
                'id' => array('$ne'=> $this->id), 
                'keys' => $check['keys']);
        } else {
            $conditions = $check;
        }
        $result = $this->find('fromKeys', array('conditions' => $conditions));
        return count($result) < 1;
    }
    
    
    public function beforeValidate()
    {
        parent::beforeValidate();
        
        if (isset($this->data['ContentVariable']['keys']) and !is_array($this->data['ContentVariable']['keys'])) {
            $this->data['ContentVariable']['keys'] = $this->fromKeysStringToKeysArray($this->data['ContentVariable']['keys']);
        } else if (is_array($this->data['ContentVariable']['keys'])) {
            foreach($this->data['ContentVariable']['keys'] as &$key) {
                if (!is_array($key)) {
                    $key = array('key' => $key);
                }
            }
        }
        return true;
    }


    protected function fromKeysStringToKeysArray($keysString)
    {
        $keys = trim(stripcslashes($keysString));
        $keys = array_filter(explode(".", $keys));
        $keysArray = array();
        foreach ($keys as $key) {
            $keysArray[] = array('key' => trim($key));
        }
        return $keysArray;
    }
    

    public  $findMethods = array(
        'count' => true,
        'first' => true,
        'all' => true,
        'fromKeys' => true,
        );


    public function _findFromKeys($state, $query, $results = array())
    {
        if ($state == 'before') {
            $keyConditions = array();
            $keys = $query['conditions']['keys'];
            unset($query['conditions']['keys']);
            for($i = 0 ; $i < count($keys) ; $i++) {
                $keyConditions['keys.'.$i] = array('key' => $keys[$i]);
            }
            $keyConditions['keys'] = array('$size' => count($keys));
            $query['conditions'] = array_merge($query['conditions'], $keyConditions);
            return $query;
        } 
        return $results;
    }


    public function allowToEdit($oldContentVariable, $newContentVariable)
    {
        if (isset($oldContentVariable['ContentVariable'])) {
            $oldContentVariable = $oldContentVariable['ContentVariable'];
        }
        if (isset($newContentVariable['ContentVariable'])) {
            $newContentVariable = $newContentVariable['ContentVariable'];
        }
        if (!isset($oldContentVariable['table'])) {
            return true;
        }
        if (isset($newContentVariable['keys']) && $oldContentVariable['keys'] != $this->fromKeysStringToKeysArray($newContentVariable['keys'])) {
            return __("Editing a keys/value's keys without the editing the table is not allowed.");
        }
        if (!isset($newContentVariable['table']) || $oldContentVariable['table'] != $newContentVariable['table']) {
            return __("Editing a keys/value's table without the editing the table is not allowed.");
        }
        return true;
    }


}
