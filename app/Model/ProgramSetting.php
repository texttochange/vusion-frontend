<?php

App::uses('MongoModel', 'Model');


class ProgramSetting extends MongoModel 
{


    var $specific    = true;
    var $name        = 'ProgramSetting';
    var $useDbConfig = 'mongo';

    public $findMethods =  array(
    	    'programSetting' => true,
    	    'count' => true
    	    );

    
    protected function _findProgramSetting($state, $query, $results = array())
    {
    	    if ($state == 'before') {
    	    	    $query['conditions']['ProgramSetting.key'] = $query['key'];
    	    	    return $query;
    	    }
    	    return $results;
    }


}
