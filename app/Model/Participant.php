<?php
App::uses('MongoModel', 'Model');
/**
 * Program Model
 *
 */
class Participant extends MongoModel {

	var $specific = true;	
/**
 * Display field
 *
 * @var string
 */
    var $name = 'Participant';
    var $useDbConfig = 'mongo';
	
    public $validate = array(
    	    'phone' => array(
    	    	    'rule' => 'isReallyUnique',
    	    	    'required' => true
    	    	    ));

    public function isReallyUnique($check)
    {
    	    $result = $this->find('count', array(
    	    	    'conditions' => array('phone' => $check['phone'])
    	    	    ));
    	    return $result < 1;
    	    
    }

    public function beforeValidate()
    {
    	 $this->data['Participant']['phone'] = (float) $this->data['Participant']['phone'];
    	 $this->data['Participant']['name'] = str_replace("\n" , "", $this->data['Participant']['name']);
    	 return true;
    }
	
}
