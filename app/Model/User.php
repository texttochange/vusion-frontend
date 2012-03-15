<?php
App::uses('AppModel', 'Model');
App::uses('AuthComponent', 'Controller/Component');
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
    

}
