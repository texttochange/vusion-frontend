<?php
App::uses('Component', 'Controller');

class UtilsComponent extends Component {


    public function isAssoc($array)
    {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }


    public function fillNonAssociativeArray($array) {
        if (!is_array($array)) {
            return $array;
        }
        foreach($array as $k => $subarray) {
            $array[$k] = $this->fillNonAssociativeArray($subarray);
        }
        if (!$this->isAssoc($array)) {
            $maxIndex = max(array_keys($array));
            for ( $i=0 ; $i < $maxIndex ; $i++) {
                if (!isset($array[$i])) {
                    $array[$i] = null;
                }
            }
            ksort($array);
        }
        return $array;
    }


}