<?php
App::uses('Component', 'Controller');

class LocalizeUtilsComponent extends Component {

    public function localizeLabelInArray($arrayWithLabels)
    {
        array_walk_recursive(
            $arrayWithLabels,
            array($this, '_localizedLabel'));
        return $arrayWithLabels;
    }

    protected function _localizedLabel(&$item, $key)
    {   
        if ($key == 'label') {
            $item = $item;
        }
    }
    
    public function localizeValueInArray($arrayToLocalize)
    {
    	array_walk_recursive(
    		$arrayToLocalize,
    		array($this, '_localizedValue'));
    	return $arrayToLocalize;
    }
    
    protected function _localizedValue(&$item)
    {
    	$item = $item;
    }

}