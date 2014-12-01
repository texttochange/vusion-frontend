<?php 
App::uses('MongoModel', 'Model');


class UserLog extends MongoModel
{

    var $specific = true;
    var $name = 'UserLog';


    function getModelVersion() {
        return '1';
    }


    function getRequiredFields($objectType=null)
    {
        return array(
 			'timestamp',
 			'user-name',
 			'user_id',
            'program-name',
            'controller',
            'action',
            'parameters');
    }


    public $validate = array(
        'timestamp' => array(
            'notempty' =>  array(
                'rule' => array('notempty'),
                'message' => 'The field timestamp cannot be empty.'),
            'format' => array(
            	'rule' => array('format'),
            	'message' => 'The field timestamp cannot is not correct.'),
			),
       'user-name' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'The field user email cannot be empty.'),
			),
        'user_id' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The field user id cannot be empty.'),
			),
        'program'  => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The field program name cannot be empty.'),
        	),
        'controller'  => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The field timestamp cannot be empty.'),
			),
        'action'  => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The field action cannot be empty.') ,
			),
        'parameters'  => array(
        	'notempty' => array(
			'rule' => 'notempty',
 			'message' => 'The field parameter cannot be empty.'),
 		    ),
        );        


    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
    }

	public function beforeValidate()
	{
		return parent::beforeValidate();
	}


	public function format($check) {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})$/', $check['timestamp'])) {
            return 'The timestamp format is not valid';
        }
		return true;
	}

}