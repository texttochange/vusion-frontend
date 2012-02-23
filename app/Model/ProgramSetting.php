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
    	    echo "start find with state:".$state." <br/>";
    	    if ($state == 'before') {
    	    	    $query['conditions']['ProgramSetting.key'] = $query['key'];
    	    	    return $query;
    	    }
    	    echo "the result of the search are:";
    	    print_r($results);
    	    echo "<br/>";
    	    return $results;
    }


}
