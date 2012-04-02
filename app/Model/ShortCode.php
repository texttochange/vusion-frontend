<?php
App::uses('MongoModel', 'Model');
/**
 * Shortcode Model
 *
 */
class ShortCode extends MongoModel
{

    var $specific    = true;
    var $name        = 'ShortCode';
    var $useDbConfig = 'mongo';
    var $useTable    = 'shortcodes';
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
