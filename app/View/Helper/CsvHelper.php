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
   		$line = null;
        $quotedElements = array_map(function($val) { return '"'.$val.'"'; }, $elements);
        $first = true;
        foreach ($orderedKey as $key) {
            if ($first) {
                $line = $quotedElements[$key];
                $first = false;
            } else {
                $line += "," . $quotedElements[$key];
            }
        }
       return $line . "\n";
    }

}
