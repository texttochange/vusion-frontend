<?php
App::uses('MongoModel', 'Model');
/**
 * Program Model
 *
 */
class ShortCode extends MongoModel
{

    var $specific = true;	
/**
 * Display field
 *
 * @var string
 */
    var $name = 'ShortCode';
    var $useDbConfig = 'mongo';
    /*
    public $findMethods = array(
    	    'ShortCode' => true,
    	    'count' => true
    	    );
    
    protected function _findShortCode($state, $query, $results = array())
    {
    	    if($state == 'before') {
    	    	    $query['conditions']['ShortCode.key'] = $query['key'];
    	    	    return $query;
    	    }
    	    return $results;
    } 
    */
    
}
