<?php 
App::uses('AppModel', 'Model');
App::uses('MongoDbSource', 'MongoDb.Model/Datasource');
App::uses('MongoModelValidator', 'Model');


abstract class MongoModel extends AppModel
{
    
    var $useDbConfig = 'vusion';   
    
    var $mongoFields = array(
        '_id',
        'modified',
        'created'
        );
    var $vusionFields = array(
        'model-version',
        'object-type'
        );
    
    
    abstract function getModelVersion();
    
    
    abstract function getRequiredFields($objectType);
    
    
    public function __construct($id = false, $table = null, $ds = null)
    {
        $this->defaultFields = array_merge($this->vusionFields, $this->mongoFields);
        parent::__construct($id, $table, $ds);
        $this->validator(new MongoModelValidator($this));
    }
    
    
    public function checkFields($object)
    {        
        if (isset($object['object-type'])) {
            $toCheck = array_merge($this->defaultFields, $this->getRequiredFields($object['object-type']));
        } else {
            $toCheck = array_merge($this->defaultFields, $this->getRequiredFields());
        }
        
        foreach ($object as $field => $value) {
            if (!in_array($field, $toCheck)) {
                unset($object[$field]);
            }
        }
        
        foreach ($toCheck as $field) {
            if (!isset($object[$field])) {
                $object[$field] = null;
            }
        };
        
        return $object;
    }
    
    
    public function create($objectType=null, $createDefaultFields=true)
    {
        parent::create();
        
        if (!$createDefaultFields) {
            return;
        }
        $toCreate = array_merge($this->defaultFields, $this->getRequiredFields($objectType));
        
        foreach ($toCreate as $field) {
            if (!isset($object[$field])) {
                $this->data[$this->alias][$field] = null;
            }
        };
        $this->data[$this->alias]['model-version'] = $this->getModelVersion();
        if ($objectType==null) {
            $this->data[$this->alias]['object-type'] = strtolower($this->name);  
        } else {
            $this->data[$this->alias]['object-type'] = $objectType;
        }
    }
    
    
    public function beforeValidate($options = array())
    {
        $this->data[$this->alias] = $this->checkFields($this->data[$this->alias]);
        $this->data[$this->alias]['model-version'] = $this->getModelVersion();
        $this->data = $this->_trimArray($this->data);
        return true;
    }
    
    
    public function _trimArray($document)
    {
        if (!is_array($document)) {
            if (is_string($document)) {
                $document = trim(stripcslashes($document));
            }
            return $document;
        }
        foreach ($document as &$element) {
            $element = $this->_trimArray($element);
        }
        return $document;
    }
    
    
    public function isVeryUnique($check)
    {
        $key = array_keys($check);
        $conditions = array($key[0] => $check[$key[0]]);
        
        if ($this->id) {
            $conditions['id'] = array('$ne'=> $this->id);
        }
        $result = $this->find(
            'count', array('conditions' => $conditions)
            );
        return $result < 1;
    }
    
    
    protected function _setDefault($field, $default)
    {
        if (!isset($this->data[$this->alias][$field])) {
            $this->data[$this->alias][$field] = $default;
        } 
    }
    
    
    function beforeSave($option = array())
    {
        $this->data[$this->alias]['modified'] = new MongoDate(strtotime('now'));
        return true;
    }


    public function requiredConditionalFieldValue($check, $cField, $cValue)
    {
        $key = key($check);
        if (!array_key_exists($key, $this->data)) {
            return true;
        }
        if (!array_key_exists($cField, $this->data)) {
            return false;
        }
        if ($this->data[$cField] != $cValue) {
            return false;
        }
        return true;
    }

    public function isArray($check)
    {
        $key = key($check);
        if (!is_array($check[$key])) {
            return false;
        }
        return true;
    }
    
    
}
