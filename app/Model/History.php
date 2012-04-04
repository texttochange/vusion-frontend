<?php
App::uses('MongoModel', 'Model');
App::uses('ScriptHelper', 'Lib');
/**
 * Program Model
 *
 */
class History extends MongoModel
{

    var $specific = true;    

    
/**
 * Display field
 *
 * @var string
 */
    //var $name = 'ParticipantStat';
    var $useDbConfig = 'mongo';    
    var $useTable    = 'history';
    
    public $findMethods = array(
        'participant' => true,
        'count' => true,
        'scriptFilter' => true
        );
    
    public function __construct($id = false, $table = null, $ds = null)
    {
    	    parent::__construct($id, $table, $ds);
    	    
    	    $this->scriptHelper = new ScriptHelper();
    }
    
    
    public function _findParticipant($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions'] = array('participant-phone' => $query['phone']);
            return $query;
        }
        return $results;
    }
    
    
    public function _findCount($state, $query, $results = array())
    {
        if ($state === 'before') {
            if (isset($query['type']) and $query['type'] == 'scriptFilter') {
                return $this->_countFiltered($state,$query);
            } else {
                $db = $this->getDataSource();
                if (empty($query['fields'])) {
                    $query['fields'] = $db->calculate($this, 'count');
                } elseif (is_string($query['fields'])  && !preg_match('/count/i', $query['fields'])) {
                    $query['fields'] = $db->calculate($this, 'count', array(
                        $db->expression($query['fields']), 'count'
                    ));
                }
            }
            $query['order'] = false;
            return $query;
        } elseif ($state === 'after') {
            if (isset($query['type']) and $query['type'] == 'scriptFilter') {
                return $this->_countFiltered($state,$query);
            } else {
                foreach (array(0, $this->alias) as $key) {
                    if (isset($results[0][$key]['count'])) {
                        if (($count = count($results)) > 1) {
                            return $count;
                        } else {
                            return intval($results[0][$key]['count']);
                        }
                    }
                }
            }
            return false;
        }
    }
    
    
    public function _findScriptFilter($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions'] = array('message-type' => 'received');
            return $query;
        }
        $script = $query['script'];
        $filteredResults = array();
        
        if (isset($csript)) {
            foreach ($results as $status) {
                if ($this->scriptHelper->hasNoMatchingAnswers($script, $status))
                    $filteredResults[] = $status;
            }
        }
        
        return $filteredResults;
    }
        
    
    protected function _countFiltered($state, $query)
    {
        if ($state == 'before') {
            $query['conditions'] = array('message-type' => 'received');
            return $query;
        }
        $script = $query['script'];
        $filteredResults = array();
        $results = $this->find('all');
        
        if (isset($csript)) {
            foreach ($results as $status) {
                if ($this->scriptHelper->hasNoMatchingAnswers($script, $status))
                    $filteredResults[] = $status;
            }
        }
        
        return count($filteredResults);
    }
    
    
}
