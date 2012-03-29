<?php
App::uses('MongoModel', 'Model');
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
        
        foreach ($results as $status) {
            foreach ($script[0]['Script']['script']['dialogues'] as $dialogue) {
                    
                    if ($status['History']['dialogue-id']
                            and $status['History']['dialogue-id'] == $dialogue['dialogue-id']) {
                    
                        foreach ($dialogue['interactions'] as $interaction) {
                            if ($status['History']['interaction-id']
                                and $status['History']['interaction-id'] == $interaction['interaction-id']) {
                            
                                if ($interaction['type-interaction'] == 'question-answer'
                                    and $interaction['type-question'] == 'close-question') {
                                
                                    foreach ($interaction['answers'] as $key => $value) {
                                        $response    = $interaction['keyword']." ".$value['choice'];
                                        $responseTwo = $interaction['keyword']." ".($key+1);
                                        if ($status['History']['message-content'] == $response
                                            or $status['History']['message-content'] == $responseTwo) {
                                        
                                            break;
                                            
                                        } else if ($status['History']['message-content'] != $response
                                            and $status['History']['message-content'] != $responseTwo
                                            and $key == (count($interaction['answers'])-1)){
                                        
                                            $filteredResults[] = $status;
                                        }
                                    }
                                }
                            }
                        }
                    }
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
        
        foreach ($results as $status) {
            foreach ($script[0]['Script']['script']['dialogues'] as $dialogue) {
                    
                    if ($status['History']['dialogue-id']
                            and $status['History']['dialogue-id'] == $dialogue['dialogue-id']) {
                    
                        foreach ($dialogue['interactions'] as $interaction) {
                            if ($status['History']['interaction-id']
                                and $status['History']['interaction-id'] == $interaction['interaction-id']) {
                            
                                if ($interaction['type-interaction'] == 'question-answer'
                                    and $interaction['type-question'] == 'close-question') {
                                
                                    foreach ($interaction['answers'] as $key => $value) {
                                        $response    = $interaction['keyword']." ".$value['choice'];
                                        $responseTwo = $interaction['keyword']." ".($key+1);
                                        if ($status['History']['message-content'] == $response
                                            or $status['History']['message-content'] == $responseTwo) {
                                        
                                            break;
                                            
                                        } else if ($status['History']['message-content'] != $response
                                            and $status['History']['message-content'] != $responseTwo
                                            and $key == (count($interaction['answers'])-1)){
                                        
                                            $filteredResults[] = $status;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }                
        }
        
        return count($filteredResults);
    }
    
    
}
