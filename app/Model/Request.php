<?php
App::uses('MongoModel', 'Model');

class Request extends MongoModel
{

    var $specific = true;
    var $name     = 'Request';

     function getModelVersion()
    {
        return '2';
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

    public $validate = array(
        'keyword' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter a keyword for this request.'
                ),
            'format' => array(
                'rule' => array('keywordFormat'),
                'message' => 'This keyword format is not valid.'
                )
            )
        );

    public function keywordFormat($check) 
    {
        $keywordRegex = '/^[a-zA-Z0-9\s]+(,(\s)?[a-zA-Z0-9\s]+)*$/';
        if (preg_match($keywordRegex, $check['keyword'])) 
            return true;
        return false;
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

    public function getKeywords()
    {
        $requests = $this->find('all');
        $keywords = array();
        foreach($requests as $request) {
            $keyphrases = explode(', ', $request['Request']['keyword']);
            foreach($keyphrases as $keyphrase) {
                $words = explode(' ', $keyphrase);
                array_push($keywords, $words[0]);
            }
        }
        return $keywords;
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
