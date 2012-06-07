<?php
App::uses('AppModel', 'Model');
App::uses('History', 'Model');


class Program extends AppModel
{

    public $displayField     = 'name';
    var $hasAndBelongsToMany = 'User';
    
    public $findMethods = array(
        'authorized' => true,
        'count' => true
        );
    
    public $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please give a name to the program.'
                ),
            'unique' => array(
                'rule' => 'isunique',
                'required' => true,
                'message' => 'Another program is currently using this name, please choose another one.'
                ),
            ),
        'url' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please give a url to the program.'
                ),
            'unique' => array(
                'rule' => 'isunique',
                'required' => true,
                'message' => 'Another program is currently using this url, please choose another one.'
                ),
            'alphaNumeric'=> array(
                'rule' => 'alphaNumeric',
                'required' => true,
                'message' => 'The url can only be composed of letters and digits.'
                ),
            ),
        'database' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please give a database to the program.'
                ),
            'unique' => array(
                'rule' => 'isunique',
                'required' => true,
                'message' => 'Another program is currently using this database, please choose another one.'
                ),
            'alphaNumeric' => array(
                'rule' => 'alphaNumeric',
                'required' => true,
                'message' => 'The database can only be composed of letters and digits.'
                ),
            )
        );
    
    public function _findAuthorized($state, $query, $results = array())
    {
        //print_r($query);
        if ($state == 'before') {
            //print_r($query);
            return $this->limitedAccessConditions($query);
        }
        return $results;
    }
    
    
    public function _findCount($state, $query, $results = array())
    {
        if ($state === 'before') {
            $db = $this->getDataSource();
            if (empty($query['fields'])) {
                $query['fields'] = $db->calculate($this, 'count');
            } elseif (is_string($query['fields'])  && !preg_match('/count/i', $query['fields'])) {
                $query['fields'] = $db->calculate($this, 'count', array(
                    $db->expression($query['fields']), 'count'
                ));
            }
            $query['order'] = false;
            
            return $this->limitedAccessConditions($query);
        } elseif ($state === 'after') {
            foreach (array(0, $this->alias) as $key) {
                if (isset($results[0][$key]['count'])) {
                    if (($count = count($results)) > 1) {
                        return $count;
                    } else {
                        return intval($results[0][$key]['count']);
                    }
                }
            }
            return false;
        }
    }
    
    
    protected function limitedAccessConditions($query)
    {
        if (isset($query['specific_program_access']) and $query['specific_program_access']) {
                $query['joins'] = array(
                    array(
                        'table' => 'programs_users',
                        'alias' => 'ProgramUser',
                        'type' => 'LEFT',
                        'conditions' => array(
                                'Program.id = ProgramUser.program_id'
                            )
                        )
                    );
                $query['conditions'] = array(
                    'ProgramUser.user_id' => $query['user_id']
                    );
                if (!empty($query['program_url'])) {
                    $query['conditions'] = array_merge(
                        $query['conditions'],
                        array('Program.url' => $query['program_url'])
                    ); 
                }
            } else {
                //TODO DRY it!
                if (!empty($query['program_url'])) {
                    $query['conditions'] = array('Program.url' => $query['program_url']);
                }
            }
        return $query;
    }
    
    public function deleteProgram()
    {
       $program = $this->read();
       if (!$this->delete()) {
           return false;
       }
       $mongoDbSource = ConnectionManager::getDataSource('mongo');
       $config = $mongoDbSource->config;
       $host = "mongodb://";
       $hostname = $config['host'] . ':' . $config['port'];
       
       if(!empty($config['login'])){
           $host .= $config['login'] .':'. $config['password'] . '@' . $hostname . '/'. $config['database'];
       } else {
           $host .= $hostname;
       }
       $con = new Mongo($host);
       $db = $con->selectDB($program['Program']['database']);
       $db->drop();
       return true;
    }
    
   
}
