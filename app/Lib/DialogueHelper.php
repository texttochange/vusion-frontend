<?php
App::uses('VusionConst', 'Lib');


class DialogueHelper
{

    static public function fromPhpDateToVusionDate($phpDate)
    {
        return $phpDate->format("Y-m-d\TH:i:s");
    }

    static public function fromVusionDateToPhpDate($vusionDate) 
    {
        return new DateTime($vusionDate);
    }

    static public function validateDate($date)
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
    static public function validateDateFromForm($date)
    {
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/', $date, $parts) == true) {
            return true;
        } else {
            return false;
        }
    }

    static public function isValideDateFromSearch($date)
    {
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $parts) == true) {
            return true;
        } else {
            return false;
        }
    }


    static public function convertDateFormat($date)
    { 
        if (!DialogueHelper::validateDate($date)) {
            if (DialogueHelper::isValideDateFromSearch($date)) {
                return DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d\T00:00:00');
            } elseif (DialogueHelper::validateDateFromForm($date)) {
                return DateTime::createFromFormat('d/m/Y H:i', $date)->format('Y-m-d\TH:i:s');
            }
        } 
        return $date;
    }


    public function recurseScriptDateConverter(&$currentLayer)
    {
        if (!is_array($currentLayer)) {
            return true;
        }

        foreach ($currentLayer as $key => &$value) {
            if (!is_int($key) && ($key == 'date-time') && !DialogueHelper::validateDate($value)) {
                if (DialogueHelper::validateDateFromForm($value)) {
                    $value = DialogueHelper::convertDateFormat($value);
                } else {
                    return false;
                }
            }
            elseif (is_array($value)) {
                if (!DialogueHelper::recurseScriptDateConverter($value)) {
                   return false;
                }
            }
        }
        return true;
    }
    
    
    static public function objectToArray($data)
    {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value)
            {
                $result[$key] = DialogueHelper::objectToArray($value);
            }
            return $result;
        }
        return $data;
    }

    
    static protected function _recurseStatus(&$newCurrentLayer, $response, $responseTwo)
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


    static public function cleanKeywords($keywords)
    {
        if (is_string($keywords)) {
            return DialogueHelper::fromKeyphrasesToKeywords($keywords);
        }
        $cleanKeywords = array();
        foreach ($keywords as $keyword) {
            $cleanKeywords = array_merge($cleanKeywords, DialogueHelper::fromKeyphrasesToKeywords($keyword));
        }
        return array_values(array_unique($cleanKeywords));
    }

    
    static public function fromKeyphrasesToKeywords($keyphrases) 
    {
        $keyphraseArray = explode(",", $keyphrases);
        array_walk($keyphraseArray, create_function('&$val', '$val = trim($val); $val = explode(" ", $val); $val = DialogueHelper::cleanKeyword($val[0]);'));
        return $keyphraseArray;
    }


    static public function cleanKeyphrases($keyphrases)
    {
        if (is_string($keyphrases)) {
            $keyphrases = explode(",", $keyphrases);            
        }
        array_walk($keyphrases, create_function('&$val', '$val = trim($val); $val = DialogueHelper::cleanKeyword($val);'));
        return $keyphrases;
    }
    

    static public function keywordCmp($keyword1, $keyword2)
    {
        return (DialogueHelper::cleanKeyword($keyword1) == DialogueHelper::cleanKeyword($keyword2));
    }

    // THIS FUNCTION SHOULDN'T BE CHANGE WITHOUT BACKEND EQUIVALENT FUNCTION
    // The function is located in utils/keyword.py
    static public function cleanKeyword($string) {
        $string = preg_replace( '@\x{00c6}@u', "AE", $string);    // Æ => AE
        $string = preg_replace( '@\x{00e6}@u', "ae", $string);    // æ => ae
        $string = preg_replace( '@\x{0152}@u', "OE", $string);    // Œ => OE
        $string = preg_replace( '@\x{0153}@u', "oe", $string);    // œ => oe
        $string = preg_replace( '/ማ/', "M", $string);    // ማ => M
        $string = preg_replace( '/ም/', "PM", $string);    // ም => PM
        $string = preg_replace( '/ል/', "Lee", $string);    //ል => Lee
        
        $a = 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåçèéêëìíîïðñòóôõöøùúûüýýþÿŔŕ '; 
        $b = 'aaaaaaceeeeiiiidnoooooouuuuybsaaaaaaceeeeiiiidnoooooouuuuyybyRr '; 
        $string = utf8_decode($string);
        $string = strtr($string, utf8_decode($a), $b); 
        $string = strtolower($string);
        return utf8_encode($string); 
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
    

    static public function foundKeywordsToMessage($programDb, $keyword, $keywordDetails, $contact='')
    {
        if ($keywordDetails['program-db'] == $programDb) {
            if ($keywordDetails['by-type'] == 'Dialogue') {
                $message = __("'%s' already used in Dialogue '%s' of the same program.", $keyword, $keywordDetails['dialogue-name']);    
            } elseif ($keywordDetails['by-type'] == 'Request') {
                $message = __("'%s' already used in Request '%s' of the same program.", $keyword, $keywordDetails['request-name']);
            } elseif ($keywordDetails['by-type'] == 'ProgramSetting') {
                $message = __("'%s' is not authorized, to authorize please send all required keywords to %s.", $keyword, $contact);
            }
        } else {
            $message = __("'%s' already used by a %s of program '%s'.", $keyword, $keywordDetails['by-type'], $keywordDetails['program-name']);
        }
        return $message;
    }

    //Prefixed code utility functions
    public static function fromPrefixedCodeToCountry($prefixedCode, $countriesByPrefixes) {
        $prefix = DialogueHelper::fromPrefixedCodeToPrefix($prefixedCode, $countriesByPrefixes);
        return $countriesByPrefixes[$prefix];
    }


    public static function fromPrefixedCodeToPrefix($prefixedCode, $countriesByPrefixes) {
        if (preg_match(VusionConst::PREFIXED_LOCAL_CODE_REGEX, $prefixedCode)) {
            $exploded = explode('-', $prefixedCode);
            $prefix = $exploded[0];
        } elseif (preg_match(VusionConst::INTERNATIONAL_CODE_REGEX, $prefixedCode)) {           
            for($i = 1; $i < strlen($prefixedCode); $i++) {
                $potentialPrefix = substr($prefixedCode, 1, $i);
                if (isset($countriesByPrefixes[$potentialPrefix])) {
                    $prefix = $potentialPrefix;
                    break;
                }
            }
        }
        if (!isset($prefix)) {
            throw new VusionException(__("Cannot find valid country prefix from %s.", $prefixedCode));
        }
        return $prefix;
    }

    public static function fromPrefixedCodeToCode($prefixedCode) {
        if (preg_match(VusionConst::PREFIXED_LOCAL_CODE_REGEX, $prefixedCode)) {
            $explodedCode = explode('-', $prefixedCode);
            return $explodedCode[1];
        } elseif (preg_match(VusionConst::INTERNATIONAL_CODE_REGEX, $prefixedCode)) { 
            return $prefixedCode;
        }
        return null;
    }

    //Funtion that load the country name and international prefix from a file.
    //For usage by a model only, othewise use the CountryComponent
    public static function loadCountries($filePath, $indexName="Prefix") {
        $importedCountries = fopen($filePath,"r");
        $countries=array();
        $headers = fgetcsv($importedCountries);
        $i = 0;
        foreach($headers as $header){
            if ($indexName === $header) {
                $index = $i;
            }
            $i += 1;
        }
        while(!feof($importedCountries)){
           $country = fgetcsv($importedCountries);
           $countries[trim($country[$index])] = trim($country[0]);
        }
        return $countries;
    }

    public static function loadPrefixesByCountries($filePath) {
        return array_flip(DialogueHelper::loadCountries($filePath, "Prefix"));
    }


}


