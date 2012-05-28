<?php
App::uses('MongoModel', 'Model');
App::uses('DialogueHelper', 'Lib');
/**
 * UnattachedMessage Model
 *
 */
class UnattachedMessage extends MongoModel
{

    var $specific    = true;
    var $name        = 'UnattachedMessage';
    var $useDbConfig = 'mongo';
    var $useTable    = 'unattached_messages';
    
    public $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter a name for this separate message.'
                )
            )
        );


    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);	    
        $this->DialogueHelper = new DialogueHelper();
    }


    public function beforeValidate()
    {
    	/*if ($this->DialogueHelper->validateDate($this->data['UnattachedMessage']['schedule']))
            return true;

        if (!$this->DialogueHelper->validateDateFromForm($this->data['UnattachedMessage']['schedule']))
            return false;

        $this->data['UnattachedMessage']['schedule'] = $this->DialogueHelper->convertDateFormat($this->data['UnattachedMessage']['schedule']);
        return true;   */ 
    }
    
    
    public function beforeSave()
    {
        if ($this->DialogueHelper->validateDate($this->data['UnattachedMessage']['schedule']))
            return true;

        if (!$this->DialogueHelper->validateDateFromForm($this->data['UnattachedMessage']['schedule']))
            return false;

        $this->data['UnattachedMessage']['schedule'] = $this->DialogueHelper->convertDateFormat($this->data['UnattachedMessage']['schedule']);
        return true;
    }
    
}
