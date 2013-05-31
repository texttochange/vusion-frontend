<?php


abstract class VirtualModel
{
    var $data = null;
    var $fields = array();
    var $validationErrors = array();


    public function __construct() 
    {
    }


    public function beforeValidate()
    {
        $this->data['model-version'] = $this->version;
        $this->data['object-type'] = strtolower($this->name);
        $this->data = $this->_trim_array($this->data);
        return true;
    }


    protected function _setDefault($field, $default) {
        if (!isset($this->data[$field])) {
            $this->data[$field] = $default;
        } 
    }


    public function _trim_array($document)
    {
        if (!is_array($document)) {
            if (is_string($document)) {
                $document = trim(stripcslashes($document));
            }
            return $document;
        }
        foreach ($document as &$element) {
            $element = $this->_trim_array($element);
        }
        return $document;
    }


    public function set($data) 
    {
        $this->data = $data;
        $this->validationErrors = array();        
    }


    public function getCurrent() 
    {
        return $this->data;        
    }


    public function create()
    {
        $this->validationErrors = array();
    }


    public function required($field, $data)
    {
        if (!array_key_exists($field, $data)) {
            return false;
        }
        return true;
    }


    public function valueRequireFields($field, $data, $requiredFieldsPerValue) 
    {   
        if (!array_key_exists($field, $data)) {
            return true;
        }
        if (!isset($requiredFieldsPerValue[$data[$field]])) {
            return true;
        }
        $requiredFields = $requiredFieldsPerValue[$data[$field]];
        foreach ($requiredFields as $requiredField) {
            if (!array_key_exists($requiredField, $data)) {
                return false;
            }
        }
        return true;
    }

    public function requiredConditionalFieldValue($field, $data, $cField, $cValue) 
    {
        if (!array_key_exists($field, $data)) {
            return true;
        }
        if (!array_key_exists($cField, $data)) {
            return false;
        }
        if ($data[$cField] != $cValue) {
            return true;
        }
        return true;
    }

    
    public function requiredConditionalFieldOrValue()
    {
        $args = func_get_args();
        $field = $args[0];
        $data = $args[1];
        $cField = $args[2];
        $cValues = array_slice($args, 3);
        if (!isset($data[$field])) {
            return true;
        }
        if (!isset($data[$cField])) {
            return false;
        }
        if (!in_array($data[$cField], $cValues)) {
            return false;
        }
        return true;
    }


    public function inList($field, $data, $list)
    {
        if (!isset($data[$field])) {
            return true;
        }
        if (in_array($data[$field], $list)) {
            return true;
        }
        return false;
    }


    public function regex($field, $data, $regex)
    {
        if (!isset($data[$field])) {
            return true;
        }
        if (preg_match($regex, $data[$field])) {
            return true;
        }
        return false;
    }


    public function notRegex($field, $data, $regex)
    {
        return (!$this->regex($field, $data, $regex));
    }


    public function notempty($field, $data)
    {
        if ($data[$field] == null) {
            return false;
        }
        return true;
    }


    abstract function validates();

}