<?php
App::uses('ProgramSpecificMongoModel', 'Model');
App::uses('VusionConst', 'Lib');


class ContentVariable extends ProgramSpecificMongoModel
{
    var $name = 'ContentVariable';
 
    
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
            'validateKeysCount' => array(
                'rule' => 'validateKeysCount',
                'message' => 'A content variable can a minimum of 1 key and a maximum of 3 keys, the format is for example \"key1.key2\".'
                ),            
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This keys pair already exists. Please choose another.'
                ),
            ),
        'table' => array(
            'nonMutable' => array(
                'rule' => 'nonMutable',
                'message' => 'Editing table is not allowed.',
                ),
            ),
        'value' => array(
            'validateValue' => array(
                'rule' => array('custom', VusionConst::CONTENT_VARIABLE_VALUE_REGEX),
                'message' => VusionConst::CONTENT_VARIABLE_VALUE_FAIL_MESSAGE,
                ),
            ),
        );
    
    public function validateKeysCount($check) 
    {
        if (count($check['keys']) < 1) {
            return __('The content variable has no keys. A minimum of 1 key and a maximum of 3 keys is allowed. A valid keys set is for example \"key1.key2\".');
        } else if (count($check['keys']) > 3) {
            return __('The content variable "%s" has %s keys. A minimum of 1 key and a maximum of 3 keys is allowed. A valid keys set is for example \"key1.key2\".', implode('.', $this->getListKeys($check['keys'])), count($check['keys']));
        }
        return true;
    }

    
    public function validateKeys($check)
    {
        foreach ($check['keys'] as $keyItem) {
            if (is_string($validationError = $this->validateKey($keyItem))) {
               if (!isset($this->validationErrors['keys'])) {
                    $this->validationErrors['keys'] = array();
                }
                $this->validationErrors['keys'][$index] = $validationError;
            }
        }
        if (isset($this->validationErrors['keys'])) {
            return false;
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
    
    
    public function nonMutable($check)
    {
        if ($this->id === false) {
            return true;
        }
        $conditions = array(
                'id' => $this->id,
                'table' => array('$ne' => $check['table']));
        $result = $this->find('count', array('conditions' => $conditions));
        return $result == 0;
    }


    public function getListKeys($keys) {
        $result = array();
        foreach($keys as $key) {
            $result[] = $key['key'];
        }
        return $result;
    } 

    public function setListKeys($keys) {
        foreach($keys as &$key) {
            if (!is_array($key)) {
                $key = array('key' => $key);
            }
        }
        return $keys;
    }


    public function beforeValidate()
    {
        parent::beforeValidate();
        
        if (isset($this->data['ContentVariable']['keys']) and !is_array($this->data['ContentVariable']['keys'])) {
            $this->data['ContentVariable']['keys'] = $this->fromKeysStringToKeysArray($this->data['ContentVariable']['keys']);
        } else if (is_array($this->data['ContentVariable']['keys'])) {
            $this->data['ContentVariable']['keys'] = $this->setListKeys($this->data['ContentVariable']['keys']);
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
        'fromKeysValue' => true
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


    public function _findFromKeysValue($state, $query, $results = array())
    {
        if ($state == 'before') {
            return $this->_findFromKeys($state, $query);
        }
        if (isset($results[0])) {
            return $results[0]['ContentVariable']['value'];
        } else {
            return null;
        }
    }


}
