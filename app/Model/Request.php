<?php
App::uses('MongoModel', 'Model');

class Request extends MongoModel
{

    var $specific = true;
    var $name     = 'Request';

    var $findMethods = array(
        'count' => true,
        'first' => true,
        'all' => true,
        'keyword' => true,
        );

    protected function _findKeyword($state, $query, $results = array())
    {
        if ($state == 'before') {
            $keywords = explode(', ', $query['keywords']);
            foreach($keywords as $keyword) {
                  $conditions[] = array('Request.keyword' => new MongoRegex('/(,\s|^)'.$keyword.'[$\s,]/i'));
            }
            if (count($conditions)>1)
                $query['conditions'] = array('$or'=>$conditions);
            else
                $query['conditions'] = $conditions[0];
            return $query;
        }
        if ($results) {
            $keywords = explode(', ', $query['keywords']);
            foreach($keywords as $keyword) {
                  if (preg_match('/(,\s|^)'.$keyword.'($|\s|,)/i', $results[0]['Request']['keyword']))
                      return $keyword;
            } 
        } 
        return null;
    }

}
