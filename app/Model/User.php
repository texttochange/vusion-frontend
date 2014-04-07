<?php
App::uses('AppModel', 'Model');
App::uses('AuthComponent', 'Controller/Component');
App::uses('FilterException', 'Lib');
/**
*  User Model
*
*  @property Group $Group
*  @property Program $Program
*/
class User extends AppModel
{
    
    public $name = 'User';
    
    /**
    *  Display field
    *
    *  @var string
    */
    public $displayField = 'username';
    /**
    *  Validation rules
    *
    *  @var array
    */
    public $validate = array(
        'username' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
                ),
            ),
        'password' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
                ),
            ),
        'email' => array(
            'email' => array(
                'rule' => 'email',
                'message' => 'Invalid email address',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
                ),
            ),
        'group_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
                ),
            ),
        'limited_program_access' => array(
            'boolean' => array(
                'rule' => array('boolean'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
                ),
            ),
        'limited_unmatchableReply_access' => array(
            'boolean' => array(
                'rule' => array('boolean'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
                ),
            ),
        );
    
    //The Associations below have been created with all possible keys, those that are not needed can be removed
    
    /**
    *  belongsTo associations
    *
    *  @var array
    */
    public $belongsTo = array(
        'Group' => array(
            'className' => 'Group',
            'foreignKey' => 'group_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
            )
        );
    
    /**
    *  hasAndBelongsToMany associations
    *
    *  @var array
    */
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
    
    
    public function validateFilter($filterParam)
    {
        if (!isset($filterParam[1])) {
            throw new FilterException("Field is missing.");
        }
        
        if (!isset($this->filterFields[$filterParam[1]])) {
            throw new FilterException("Field '".$filterParam[1]."' is not supported.");
        }
        
        if (!isset($filterParam[2])) {
            throw new FilterException("Operator is missing for field '".$filterParam[1]."'.");
        }
        
        if (!isset($this->filterFields[$filterParam[1]]['operators'][$filterParam[2]])) {
            throw new FilterException("Operator '".$filterParam[2]."' not supported for field '".$filterParam[1]."'.");
        }
        
        if (!isset($this->filterFields[$filterParam[1]]['operators'][$filterParam[2]]['parameter-type'])) {
            throw new FilterException("Operator type missing '".$filterParam[2]."'.");
        }
        
        if ($this->filterFields[$filterParam[1]]['operators'][$filterParam[2]]['parameter-type'] != 'none' && !isset($filterParam[3])) {
            throw new FilterException("Parameter is missing for field '".$filterParam[1]."'.");
        }
    }
    
    
    public function fromFilterToQueryConditions($filter) {
        
        $conditions = array();
        
        foreach($filter['filter_param'] as $filterParam) {
            
            $condition = null;
            
            $this->validateFilter($filterParam);
            
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
            
            if ($filter['filter_operator'] == "all") {
                if (count($conditions) == 0) {
                    $conditions = $condition;
                } elseif (!isset($conditions['AND'])) {
                    $conditions = array('AND' => array($conditions, $condition));
                } else {
                    array_push($conditions['AND'], $condition);
                }
            }  elseif ($filter['filter_operator'] == "any") {
                if (count($conditions) == 0) {
                    $conditions = $condition;
                } elseif (!isset($conditions['OR'])) {
                    $conditions = array('OR' => array($conditions, $condition));
                } else {
                    array_push($conditions['OR'], $condition);
                }
            }
            
        }
        
        return $conditions;
    }
    
    
    public function hasUnmatchableReplyAccess($id)
    {
        $data = $this->findById($id);
        return $data['User']['limited_unmatchableReply_access'];    
    }
    
    
}
