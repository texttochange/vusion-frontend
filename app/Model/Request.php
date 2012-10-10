<?php
App::uses('MongoModel', 'Model');

class Request extends MongoModel
{

    var $specific = true;
    var $name     = 'Request';

     function getModelVersion()
    {
        return '1';
    }

    function getRequiredFields($objectType=null)
    {
        return array(
            'keyword',
            'set-no-request-matching-try-keyword-only',
            'actions',
            'responses'
            );
    }

    var $findMethods = array(
        'count' => true,
        'first' => true,
        'all' => true,
        'keyword' => true,
        'keyphrase' => true,
        );

    public function beforeValidate()
    {
        parent::beforeValidate();

        $this->data['Request']['object-type'] = strtolower($this->name);

        if ($this->data['Request']['actions'] == null) {
            $this->data['Request']['actions'] = array();
        }

        if ($this->data['Request']['responses'] == null) {
            $this->data['Request']['responses'] = array();
        }

        if ($this->data['Request']['set-no-request-matching-try-keyword-only'] == null) {
            $this->data['Request']['set-no-request-matching-try-keyword-only'] = 0;
        } else {
            $this->data['Request']['set-no-request-matching-try-keyword-only'] = 'no-request-matching-try-keyword-only';
        }


    }


    protected function _findKeyword($state, $query, $results = array())
    {
        if ($state == 'before') {
            $keywords = explode(', ', $query['keywords']);
            foreach($keywords as $keyword) {
                  $conditions[] = array('Request.keyword' => new MongoRegex('/(,\s|^)'.$keyword.'($|\s|,)/i'));
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


    protected function _findKeyphrase($state, $query, $results = array())
    {
        if ($state == 'before') {
            $keywords = explode(', ', $query['keywords']);
            foreach($keywords as $keyword) {
                  $conditions[] = array('Request.keyword' => new MongoRegex('/(,\s|^)'.$keyword.'($|,)/i'));
            }
            if (count($conditions)>1)
                $conditions = array('$or'=>$conditions);
            else
                $conditions = $conditions[0];
            if (isset($query['excludeRequest']) and $query['excludeRequest'] != '') {
                $exclude = array('Request._id' => array('$ne' => new MongoId($query['excludeRequest'])));
                $conditions = array(
                    '$and' =>  array($conditions, $exclude)
                    );
            }
            $query['conditions'] = $conditions;
            return $query;
        }
        if ($results) {
            $keywords = explode(', ', $query['keywords']);
            foreach($keywords as $keyword) {
                  if (preg_match('/(,\s|^)'.$keyword.'($|,)/i', $results[0]['Request']['keyword']))
                      return $keyword;
            } 
        } 
        return null;
    }

}
