<?php

class DialogueHelper
{

    public function validateDate($date)
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
    public function validateDateFromForm($date)
    {
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/', $date, $parts) == true) {
            return true;
        } else {
            return false;
        }
    }

    public function isValideDateFromSearch($date)
    {
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $parts) == true) {
            return true;
        } else {
            return false;
        }
    }


    public function convertDateFormat($date)
    { 
        if (!$this->validateDate($date)) {
            if ($this->isValideDateFromSearch($date)) {
                return DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d\T00:00:00');
            } elseif ($this->validateDateFromForm($date)) {
                return DateTime::createFromFormat('d/m/Y H:i', $date)->format('Y-m-d\TH:i:s');
            }
            return null;
        } 
        return $date;
    }


    public function recurseScriptDateConverter(&$currentLayer)
    {
        if (!is_array($currentLayer)) {
            return true;
        }

        foreach ($currentLayer as $key => &$value) {
            if (!is_int($key) && ($key == 'date-time') && !$this->validateDate($value)) {
                if ($this->validateDateFromForm($value)) {
                    $value = $this->convertDateFormat($value);
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
    
    
    public function objectToArray($data)
    {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value)
            {
                $result[$key] = $this->objectToArray($value);
            }
            return $result;
        }
        return $data;
    }
    
    
    public function clearAndExplode($stringValue)
    {
        $stringValue = str_replace(" ", "", $stringValue);
        return explode(",", $stringValue);
    }


    public function hasKeyword(&$currentLayer, $keyword)
    {
        if (!is_array($currentLayer)) {
            return false;
        }
        
        foreach ($currentLayer as $key => $value) {
            if (!is_int($key) && ($key == 'keyword')) {
                $currentKeywords = $this->clearAndExplode($value);
                $noSpacedCurrentKeywords = array();
                if (isset($currentLayer['answer-accept-no-space']) 
                        && $currentLayer['answer-accept-no-space'] != null 
                        && $currentLayer['answers']) {
                    foreach($currentLayer['answers'] as $answer) {
                        foreach($currentKeywords as $currentKeyword) {
                            $noSpacedCurrentKeywords[] = $currentKeyword.$answer['choice'];
                        }
                    }
                }
                $currentKeywords = array_merge($currentKeywords, $noSpacedCurrentKeywords);
                $toValidKeywords = $this->clearAndExplode($keyword);
                foreach ($currentKeywords as $currentKeyword) {
                    foreach ($toValidKeywords as $toValidKeyword) {
                        if (strtolower($currentKeyword) == strtolower($toValidKeyword)) {
                            return $toValidKeyword;   
                        }
                    }
                }
            }
            else if (is_array($value)) {
                $searchResult = $this->hasKeyword($value, $keyword);
                if ($searchResult)
                    return $searchResult;
            }
        }
        return false;
    }

    public function getKeywords(&$currentLayer)
    {
        $keywords = array();
        if (!is_array($currentLayer)) {
            return $keywords;
        }
        
        foreach ($currentLayer as $key => $value) {
            if (!is_int($key) && ($key == 'keyword')) {
                $currentKeywords = $this->clearAndExplode($value);
                $noSpacedCurrentKeywords = array();
                if (isset($currentLayer['answer-accept-no-space']) 
                        && $currentLayer['answer-accept-no-space'] != null 
                        && $currentLayer['answers']) {
                    foreach($currentLayer['answers'] as $answer) {
                        foreach($currentKeywords as $currentKeyword) {
                            $noSpacedCurrentKeywords[] = $currentKeyword.$answer['choice'];
                        }
                    }
                }
                $currentKeywords = array_merge($currentKeywords, $noSpacedCurrentKeywords);
                $keywords = array_merge($keywords, $currentKeywords);
            }
            else if (is_array($value)) {
                $keywords = array_merge($keywords, $this->getKeywords($value));
            }
        }
        return $keywords;
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
    
    public function getInteraction($currentLayer, $interaction_id)
    {
        
        if (!is_array($currentLayer)) {
            return false;
        }
        
        foreach ($currentLayer as $key => $value) {
            if ($key == 'interaction-id' && $value == $interaction_id) {
                return $currentLayer;
            }
            else if (is_array($value)) {
                $result = $this->getInteraction($value, $interaction_id);
                if ($result)
                    return $result;
            }
        }
        return array();
    }


    public function getRequestKeywordToValidate($value) 
    {
        $requests = explode(", ", $value);
        $keywords = "";
        foreach ($requests as &$request) {
            $request = explode(" ", $request);
            $request = $request[0];
        }
        return implode(", ",$requests);
    }


    public static function getRequestKeywords($request)
    {
         if (!isset($request['keyword'])) {
             return array();
         }
         return fromKeyphrasesToKewywords($request['keyword']);
    }

    
    static public function fromKeyphrasesToKeywords($keyphrases) 
    {
        $keyphraseArray = explode(",", $keyphrases);
        array_walk($keyphraseArray, create_function('&$val', '$val = trim($val);'));
        $keywords = array();
        foreach ($keyphraseArray as $keyphrase) {
            $words = explode(" ", $keyphrase);
            $keywords[] = strtolower($words[0]);
        }
        return $keywords;
    }
    
    static public function cmpKeywords($keyword1, $keyword2)
    {
        return (0 == strcasecmp($keyword1, $keyword2));
    }

    // THIS FUNCTION SHOULDN'T BE CHANGE WITHOUT BACKEND EQUIVALENT FUNCTION
    // The function is located in utils/keyword.py
    static public function cleanKeyword($string) {
        $string = preg_replace( '@\x{00c6}@u', "AE", $string);    // Æ => AE
        $string = preg_replace( '@\x{00e6}@u', "ae", $string);    // æ => ae
        $string = preg_replace( '@\x{0152}@u', "OE", $string);    // Œ => OE
        $string = preg_replace( '@\x{0153}@u', "oe", $string);    // œ => oe

        $a = 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåçèéêëìíîïðñòóôõöøùúûüýýþÿŔŕ '; 
        $b = 'aaaaaaceeeeiiiidnoooooouuuuybsaaaaaaceeeeiiiidnoooooouuuuyybyRr '; 
        $string = utf8_decode($string);
        $string = strtr($string, utf8_decode($a), $b); 
        $string = strtolower($string); 
        return utf8_encode($string); 
    }

    //TODO might require to normalise the keywords
    static public function isUsedKeyword($keyword, $usedKeywords)
    {
        $keyword = strtolower($keyword);
        $usedKeyword = array_change_key_case($usedKeywords);
        if (isset($usedKeywords[$keyword])) {
            return array($keyword => $usedKeywords[$keyword]);
        } else {
            return false;
        }
    }

    
    public function compareDialogueByName($a, $b)
    {
        if ($a['Active'] && $b['Active'])
            return strcasecmp($a['Active']['name'],$b['Active']['name']);
        if ($a['Active'] && !$b['Active'])
            return -1;
        if (!$a['Active'] && $b['Active'])
            return 1;
        if ($a['Draft'] && $b['Draft'])
            return strcasecmp($a['Draft']['name'],$b['Draft']['name']);
    }
    

}


