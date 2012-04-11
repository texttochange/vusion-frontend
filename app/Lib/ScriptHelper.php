<?php

class ScriptHelper
{

    private function _validateDate($date)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})$/', $date, $parts) == true) {
            $time = gmmktime($parts[4], $parts[5], $parts[6], $parts[2], $parts[3], $parts[1]);
          
            $input_time = strtotime($date);
            if ($input_time === false) return false;
            
            return $input_time == $time;
        } else {
            return false;
        }
    }

    //TODO to remove when client side javascrit is managing the format
    private function _validateDateFromForm($date)
    {
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/', $date, $parts) == true) {
            /*$time = gmmktime($parts[4], $parts[5], 0, $parts[2], $parts[1], $parts[3]);
          
            $input_time = strtotime($date);
            if ($input_time === false) return false;
            
            return $input_time == $time;*/
            return true;
        } else {
            return false;
        }
    }


    private function _convertDateFormat($date)
    {
        return DateTime::createFromFormat('d/m/Y H:i', $date)->format('Y-m-d\TH:i:s');
    }


    public function recurseScriptDateConverter(&$currentLayer)
    {
        if (!is_array($currentLayer)) {
            return true;
        }

        foreach ($currentLayer as $key => &$value) {
            if (!is_int($key) && ($key == 'date-time') && !$this->_validateDate($value)) {
                if ($this->_validateDateFromForm($value)) {
                    $value = $this->_convertDateFormat($value);
                } else {
                    return false;
                }
            }
            elseif (is_array($value)) {
                if (!$this->recurseScriptDateConverter($value)) {
                   return false;
                }
            }
        }
        return true;
    }
    
    
    public function object_to_array($data)
    {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value)
            {
                $result[$key] = $this->object_to_array($value);
            }
            return $result;
        }
        return $data;
    }
    

    public function hasKeyword(&$currentLayer, $keyword)
    {
        if (!is_array($currentLayer)) {
            return false;
        }
        
        foreach ($currentLayer as $key => &$value) {
            if (!is_int($key) && ($key == 'keyword')) {
                if (strtolower($value) == strtolower($keyword)) {
                    return true;
                }
            }
            else if (is_array($value)) {
                if ($this->hasKeyword($value, $keyword))
                    return true;
            }
        }
        return false;
    }
    

    public function hasNoMatchingAnswers(&$currentLayer, $status)
    {
        if (!is_array($currentLayer)) {
            return false;
        }
        
        foreach ($currentLayer as $key => &$value) {
            if (!is_int($key) && ($key == 'answers')) {
                foreach($value as $answer => $choice) {
		    $response    = $currentLayer['keyword']." ".$choice['choice'];
		    $responseTwo = $currentLayer['keyword']." ".($answer+1);
		    if ($this->_recurseStatus($status, $response, $responseTwo)) {
		        if($answer==(count($value)-1))
                            return true;
                        else
                            continue;
                    }
                    return false;
	        }
            }
            else if (is_array($value)) {
                $result = $this->hasNoMatchingAnswers($value, $status);
                return $result;
            }
        }
        return false;
    }
    
    
    protected function _recurseStatus(&$newCurrentLayer, $response, $responseTwo)
    {
        if (!is_array($newCurrentLayer)) {
            return false;
        }
        
        foreach ($newCurrentLayer as $newKey => &$newValue) {
            if (!is_int($newKey) && ($newKey == 'message-content')) {
                if (strtolower($newValue) != strtolower($response)
                	and strtolower($newValue) != strtolower($responseTwo)) {
                    return true;
                }
                return false;
            }
            else if (is_array($newValue)) {
                $newResult = $this->_recurseStatus($newValue, $response, $responseTwo);
                return $newResult;
            }
        }
        return false;
    }
    

}
