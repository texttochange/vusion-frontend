<?php


abstract class VirtualModel
{
    var $data = null;
    var $fields = array();
    var $validationErrors = array();


    public function __construct() 
    {
    }


    public function set($data) 
    {
        $this->data = $data;
        $this->validationErrors = array();        
    }


    public function create()
    {
        $this->validationErrors = array();
    }


    public function required($field, $data)
    {
        if (!isset($data[$field])) {
            return false;
        }
        return true;
    }


    public function requiredConditionalFieldValue($field, $data, $cField, $cValue) 
    {
        if (!isset($data[$cField])) {
            return false;
        }
        if ($data[$cField] != $cValue) {
            return true;
        }
        if (!isset($data[$field])) {
            return false;
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
        if (!isset($data[$cField])) {
            return false;
        }
        if (in_array($data[$cField], $cValues)) {
            return true;
        }
        if (!isset($data[$field])) {
            return false;
        }
        return true;
    }


    public function inList($field, $data, $list)
    {
        if (in_array($data[$field], $list)) {
            return true;
        }
        return false;
    }


    public function regex($field, $data, $regex)
    {
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