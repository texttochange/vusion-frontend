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

    protected function _setDefaultSubfield(&$data, $field, $default) {
        if (!isset($data[$field])) {
            $data[$field] = $default;
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
                return __('The %s field with value %s require the field %s.', $field, $data[$field], $requiredField);
            }
        }
        return true;
    }


    public function requiredConditionalFieldValue($field, $data, $cField, $cValue) 
    {
        if (!array_key_exists($field, $data)) {//print_r($field); echo "<br />";
            return true;
        }
        if (!array_key_exists($cField, $data)) {echo "cfield not in data here<br />";
            return false;
        }
        if ($data[$cField] != $cValue) {echo " cfield not equal to cvalue there<br />";
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


    public function requiredConditionalFieldOrKeyValue()
    {
        $args = func_get_args();
        $field = $args[0];
        $data = $args[1];
        $orKeyValue = $args[2];
        if (!isset($data[$field])) {
            return true;
        }
        foreach ($orKeyValue as $cField => $cValue) {
            if (!isset($data[$cField])) {
                continue;
            }
            if ($data[$cField] == $cValue) {
                return true;
            }
        }
        return false;
    }


    public function inList($field, $data, $list)
    {
        if (!array_key_exists($field, $data)) {
            return true;
        }
        if (!in_array($data[$field], $list)) {
            return false;
        }
        return true;
    }


    public function notEmptyArray($field, $data) {
        if (!array_key_exists($field, $data)) {
            return true;
        }
        if ($data[$field] == array()) {
            return false;
        }
        return true;
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
        if (!isset($data[$field])) {
            return true;
        }
        return (!$this->regex($field, $data, $regex));
    }


    public function notempty($field, $data)
    {
        if ($data[$field] == null) {
            return false;
        }
        return true;
    }


    public function validList($field, $data, $elementRules)
    {
        if (!isset($data[$field])) {
            return true;
        }
        $count = 0;
        $validationError = array();
        foreach($data[$field] as $subcondition) {
            $result = $this->_runValidateRules($subcondition, $elementRules);
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


    public function validates()
    {
        $data = $this->data;
        return $this->_validates($data, $this->validate);
    }


    protected function _validates($data, $validationRules)
    {
        $result = $this->_runValidateRules($data, $validationRules);
        if (is_bool($result)) {
            $this->validationErrors = array();
            return $result;
        } 
        $this->validationErrors = $result;
        return false;
    }


    protected function _runValidateRules($data, $validationRules)    
    {
        $validationErrors = array();
        foreach ($validationRules as $field => $validateField) {
            foreach ($validateField as $ruleName => $rule) {
                $defaultArgs = array($field, $data);
                if (is_array($rule['rule'])) {
                    $func = $rule['rule'][0];
                    $args = array_slice($rule['rule'], 1);
                } else {
                    $func = $rule['rule'];
                    $args = array();
                }
                $args = array_merge($defaultArgs, $args);
                $result = call_user_func_array(array($this, $func), $args);
                $errorMessage = null;
                if (is_string($result) || is_array($result)) {
                    $errorMessage = $result;
                    $result = false;
                }
                if (!$result) {
                    if (!isset($validationErrors[$field])) {
                        $validationErrors[$field] = array();
                    }
                    if (!isset($errorMessage)) {
                        $errorMessage = $rule['message'];
                    }
                    if (isset($errorMessage)) {
                        if (is_array($errorMessage)) {
                            $validationErrors[$field] = $errorMessage;
                        } else {
                            array_push($validationErrors[$field], $errorMessage);
                        }
                    }
                    break;
                }
            }
        }
        if ($validationErrors != array()) {
            return $validationErrors;
        }
        return true;
    }


}