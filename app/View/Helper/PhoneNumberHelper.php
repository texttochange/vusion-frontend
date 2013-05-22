<?php
App::uses('AppHelper', 'View/Helper');


class PhoneNumberHelper extends AppHelper {

    public function replaceCountryCodeOfShortcode($code, $countriesPrefixes) 
    {   
        if (!$this->isShortcodeWithPrefix($code)){
            return $code;
        }
        $explodedCode = explode("-", $code);
        $prefix = intval($explodedCode[0]);
        if (!isset($countriesPrefixes[$prefix])) {
            $explodedCode[0] = __("Unknown");
        } else {
            $explodedCode[0] = $countriesPrefixes[$prefix];
        }
        return implode("-", $explodedCode);
    }

    public function getInternationalPrefix($code, $countriesPrefix)
    {
        if ($this->isShortcodeWithPrefix($code)){
            $explodedCode = explode("-", $code);
            return $explodedCode[0];            
        }

        if ($this->isLongcode($code)){
            for($i = 1; $i < 6; $i++) {
                $tryPrefix = intval(substr($code, 1, $i));
                 if (isset($countriesPrefix[$tryPrefix])) {
                    return $tryPrefix;
                }
            }  
        }

        return false;
    }

    public function addInternationalCodeToShortcode($code, $internationalPrefix)
    {
        if (!$this->isShortcode($code))
            return $code;
        return $internationalPrefix."-".$code;
    }

    public function displayCode($code, $internationalPrefix, $countriesPrefixes)
    {
        $code = $this->addInternationalCodeToShortcode($code, $internationalPrefix);
        return $this->replaceCountryCodeOfShortcode($code, $countriesPrefixes);
    }

    public function isShortcodeWithPrefix($code)
    {
        return (preg_match('/^\\d*-\\d*$/', $code) == true);
    }

    public function isShortcode($code)
    {
        return (preg_match('/^\\d*$/', $code) == true);
    }

    public function isLongCode($code)
    {
        return (preg_match('/^\\+\\d*$/', $code) == true);
    }

}
