<?php
App::uses('MongoModel', 'Model');
/**
 * Program Model
 *
 */
class Schedule extends MongoModel
{

    var $specific = true;
    var $useDbConfig = 'mongo';
    
    public $findMethods = array(
        'soon' => true,
        'count' => true
        );
     
     protected function _findSoon($state, $query, $results = array())
     {
        if ($state == 'before') {
            $query['order']['datetime'] = 'asc';
            $query['limit'] = 10;
            return $query;
        }
        return $results;
    }
    
    
}
