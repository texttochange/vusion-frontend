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
    	        return false;
    	    }
    	    else if (is_array($value)) {
    	        $result = $this->hasKeyword($value, $keyword);
    	        return $result;
    	    }
    	}
    }
    
    
    public function hasNoMatchingAnswers($script, $status)
    {
    	    foreach ($script[0]['Script']['script']['dialogues'] as $dialogue) {
                    
                    if ($status['History']['dialogue-id']
                            and $status['History']['dialogue-id'] == $dialogue['dialogue-id']) {
                    
                        foreach ($dialogue['interactions'] as $interaction) {
                            if ($status['History']['interaction-id']
                                and $status['History']['interaction-id'] == $interaction['interaction-id']) {
                            
                                if ($interaction['type-interaction'] == 'question-answer'
                                    and $interaction['type-question'] == 'close-question') {
                                
                                    foreach ($interaction['answers'] as $key => $value) {
                                        $response    = $interaction['keyword']." ".$value['choice'];
                                        $responseTwo = $interaction['keyword']." ".($key+1);
                                        if ($status['History']['message-content'] == $response
                                            or $status['History']['message-content'] == $responseTwo) {
                                        
                                            break;
                                            
                                        } else if ($status['History']['message-content'] != $response
                                            and $status['History']['message-content'] != $responseTwo
                                            and $key == (count($interaction['answers'])-1)){
                                                                                    
                                            return true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                return false;
    }

	
}
