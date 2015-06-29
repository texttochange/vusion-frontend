<?php
App::uses('AppHelper', 'View/Helper');

class CsvHelper extends AppHelper
{
    function arrayToLine($elements) 
    {
        $quotedElements = array_map(function($val) { return '"'.$val.'"'; }, $elements);
        return implode(",", $quotedElements) . "\n";
    }

    function dictToLine($elements, $orderedKey)
    {
        $line = '';
        $quotedElements = array_map(function($val) { return '"'.$val.'"'; }, $elements);
        $first = true;
        foreach ($orderedKey as $key) {
            if (isset($quotedElements[$key])) {
                $value = $quotedElements[$key];
            } else {
                $value = '';
            }

            if ($first) {
                $line = $value;
                $first = false;
            } else {
                $line .= "," . $value;
            }
        }
        return $line . "\n";
    }

}
