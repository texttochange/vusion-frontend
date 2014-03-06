<?php
App::uses('AppHelper', 'View/Helper');

class BigNumberHelper extends AppHelper
{
    
    public function replaceBigNumbers($count, $maxCharacters=5) 
    {   
        if (!is_numeric($count)) {
            return $count;
        }
        $postfix = "";
        if ($count < 1000) {
            $countFormat= number_format($count / 1);
        } else if ($count < 1000000) {
            $countFormat= number_format($count / 1000, 2);
            $postfix = "K";
        } else if ($count < 1000000000) {
            $countFormat= number_format($count / 1000000, 3);
            $postfix = "M";
        } else {
            $countFormat= number_format($count / 1000000000, 3);
            $postfix = "B";
        }
        if ($maxCharacters > 0 && strlen($countFormat) > $maxCharacters) {
            $toRemove = $maxCharacters - strlen($countFormat);
            $countFormat = substr($countFormat, 0, $toRemove);
            $countFormat = rtrim($countFormat, '.');
        } 
        return $countFormat . $postfix;   
    }
    
    
    public function roundOffNumbers($bigNumbers)
    {
        array_walk($bigNumbers, 'self::roundOffNumber');
        return $bigNumbers;
    }
    
    
    protected function roundOffNumber(&$value, $key)
    {
        $value = array('exact' => $value, 'rounded' => $this->replaceBigNumbers($value, 3));
    }
}
