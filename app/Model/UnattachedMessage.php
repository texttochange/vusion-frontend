<?php
App::uses('MongoModel', 'Model');
App::uses('ScriptHelper', 'Lib');
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


    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);	    
        $this->scriptHelper = new ScriptHelper();
    }


    public function beforeValidate()
    {
        if (!$this->scriptHelper->validateDateFromForm($this->data['UnattachedMessage']['schedule']))
            return false;

        $this->data['UnattachedMessage']['schedule'] = $this->scriptHelper->convertDateFormat($this->data['UnattachedMessage']['schedule']);
        return true;    
    }
    
}
