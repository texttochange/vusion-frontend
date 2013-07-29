<?php
App::uses('VusionValidation', 'Lib');

## function required because the Setting model has a bad design: key/value
## This key/value design to be replace in the future but in the mean time
## one need a validation function to be run before.
class ValidationHelper 
{

    public function runValidationRules($data, $validationRules)    
    {
        $validationErrors = array();
        foreach ($validationRules as $field => $validateField) {
            foreach ($validateField as $ruleName => $rule) {
                if (is_array($rule['rule'])) {
                    $func = $rule['rule'][0];
                    $args = array_slice($rule['rule'], 1);
                } else {
                    $func = $rule['rule'];
                    $args = array();
                }
                $required = (isset($rule['required'])? $rule['required']: true);
                $result = false;
                if ($func == 'required') {
                     if (array_key_exists($field, $data)) {
                         $result = true;        
                     }
                } else {
                    if (!array_key_exists($field, $data) && !$required) {
                        $result = true;
                    } else {
                        if (!array_key_exists($field, $data)) {
                            $result = false;
                        } else {
                            $check = array($field => $data[$field]);
                            $result = call_user_func_array(array($this, $func), array($check, $args, $data));
                        }
                    }
                }
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


    public function custom($check, $regex, $data)
    {
        $check = array_shift(array_values($check));
        $regex = $regex[0];
        return forward_static_call_array(array("VusionValidation", 'custom'), array($check, $regex));
    }


    public function inlist($check, $list, $data) 
    {
        $check = array_shift(array_values($check));
        $list = $list[0];
        return forward_static_call_array(array("VusionValidation", 'inList'), array($check, $list));
    }

    public function valueRequireFields($check, $requiredFieldsPerValue, $data) 
    {   
        $requiredFieldsPerValue = $requiredFieldsPerValue[0]; 
        $field = array_shift(array_keys($check));
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

    public function lowerThan($check, $lowerThan, $data) 
    {
        $check = array_shift(array_values($check));
        $lowerThan = $lowerThan[0];
        if (!array_key_exists($lowerThan, $data)) {
            return true;
        }
        if (strcmp($check, $data[$lowerThan]) >= 0) {
            return false;
        }
        return true;
    }

    public function greaterThan($check, $greaterThan, $data) 
    {
        $check = array_shift(array_values($check));
        $greaterThan = $greaterThan[0];
        if (!array_key_exists($greaterThan, $data)) {
            return true;
        }
        $gt = $data[$greaterThan];
        if (strcmp($data[$greaterThan], $check) >= 0) {
            return false;
        }
        return true;
    }

}