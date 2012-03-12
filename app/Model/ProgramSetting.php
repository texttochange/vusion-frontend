<?php

App::uses('MongoModel', 'Model');


class ProgramSetting extends MongoModel 
{


    var $specific    = true;
    var $name        = 'ProgramSetting';
    var $useDbConfig = 'mongo';

    public $findMethods =  array(
    	    'programSetting' => true,
    	    'count' => true,
    	    'hasProgramSetting' => true,
    	    'getProgramSetting' => true,
    	    );

    
    protected function _findProgramSetting($state, $query, $results = array())
    {
    	    if ($state == 'before') {
    	    	    $query['conditions']['ProgramSetting.key'] = $query['key'];
    	    	    return $query;
    	    }
    	    return $results;
    }

    
    protected function _findHasProgramSetting($state, $query, $results = array())
    {
    	    if ($state == 'before') {
    	    	    $query['conditions']['ProgramSetting.key'] = $query['key'];
    	    	    $query['conditions']['ProgramSetting.value'] = $query['value'];
    	    	    return $query;
    	    }
    	    return $results;
    }

    protected function _findGetProgramSetting($state, $query, $results = array())
    {
        if ($state == 'before') {
    	    	    $query['conditions']['ProgramSetting.key'] = $query['key'];
    	    	    return $query;
    	    }
        if (isset($results[0]))
            return $results[0]['ProgramSetting']['value'];
    	else
    	    return array();
    }


}
