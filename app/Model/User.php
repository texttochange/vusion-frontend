<?php
App::uses('AppModel', 'Model');
App::uses('AuthComponent', 'Controller/Component');
App::uses('FilterException', 'Lib');


class User extends AppModel
{
    
    public $name = 'User';
    
    public $displayField = 'username';
    
    public $validate = array(
        'username' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                ),
            ),
        'password' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                ),
            'minLength' => array(
                'rule' => array('minLength', 8),
                'message' => 'Password must be at least 8 characters long'
                ),
            'alphaNumeric' => array(
                'rule' => '/^(?=.*\d)(?=.*[a-zA-Z])[a-zA-Z\d]+$/',
                'message' => 'Password must be letters and numbers, and contain atleast one number'
                ),
            ),
        'email' => array(
            'email' => array(
                'rule' => 'email',
                'message' => 'Invalid email address',
                ),
            ),
        'group_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                ),
            ),
        'limited_program_access' => array(
            'boolean' => array(
                'rule' => array('boolean'),
                ),
            ),
        );
    
    
    public $belongsTo = array(
        'Group' => array(
            'className' => 'Group',
            'foreignKey' => 'group_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
            )
        );
    
    
    public $hasAndBelongsToMany = array(
        'Program' => array(
            'className' => 'Program',
            'joinTable' => 'programs_users',
            'foreignKey' => 'user_id',
            'associationForeignKey' => 'program_id',
            'unique' => true,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'finderQuery' => '',
            'deleteQuery' => '',
            'insertQuery' => ''
            )
        );
    
    
    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->Behaviors->load('Filter');
    }
	
    
    public function beforeSave()
    {
        if (isset($this->data[$this->alias]['password'])) {
            $this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
        }
        return true;
    }
    
    
    public $actsAs = array('Acl' => array('type' => 'requester'));
    
    
    public function parentNode()
    {
        if (!$this->id && empty($this->data)) {
            return null;
        }
        if (isset($this->data['User']['group_id'])) {
            $groupId = $this->data['User']['group_id'];
        } else {
            $groupId = $this->field('group_id');
        }
        if (!$groupId) {
            return null;
        } else {
            return array('Group' => array('id' => $groupId));
        }
    }
    
    
    #Filter variables and functions
    public $filterFields = array(
        'username' => array(
        	'label' => 'username',
        	'operators'=> array(
                'start-with' => array(
                    'parameter-type' => 'text'),
                'equal-to' => array(
                    'parameter-type' => 'text'))),
        'group_id' => array(
        	'label' => 'group',
        	'operators' => array(
                'is' => array(
                    'parameter-type' => 'group'),
                'not-is' =>  array(
                    'parameter-type' => 'group'))),
        );
    
    
    public $filterOperatorOptions = array(
        'all' => 'all',
        'any' => 'any'
        );  
    
    
    public function fromFilterToQueryCondition($filterParam) 
    {
        $condition = array();
        if ($filterParam[1] == 'group_id') {
            if ($filterParam[2] == 'is') {
                $condition['group_id'] = $filterParam[3];
            } elseif ($filterParam[2] == 'not-is') {
                $condition['group_id'] = array('$ne'=> $filterParam[3]);
            } 
        } elseif ($filterParam[1] == 'username') {
            if ($filterParam[2] == 'equal-to') {
                $condition['username'] = $filterParam[3];
            } elseif ($filterParam[2] == 'start-with') {
                $condition['username LIKE'] = $filterParam[3]."%"; 
            }            
        }
        return $condition;
    }
    
    
}
