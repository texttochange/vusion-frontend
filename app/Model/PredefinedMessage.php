<?php

App::uses('MongoModel', 'Model');
App::uses('VusionValidation', 'Lib');
App::uses('VusionConst', 'Lib');

class PredefinedMessage extends MongoModel
{
    var $specific = true;
    var $name = 'PredefinedMessage';
    var $useDbConfig = 'mongo';
    
    function getModelVersion()
    {
        return '1';
    }
    
    
    function getRequiredFields($objectType=null)
    {
        return array(
            'name',
            'content'
            );
    }
    
    
    public $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'A predefined message must have a name.'
                ),
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This name already exists. Please choose another.'
                ),
            ),
        'content' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter some content for this message.'
                ),
            'validApostrophe' => array(
                'rule' => array('notRegex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                ),
            ),
        );
    
    
    public function isUnique($check)
    {
        if ($this->id) {
            $conditions = array('id'=>array('$ne'=> $this->id),'name' => $check['name']);
        } else {
            $conditions = array('name' => $check['name']);
        }
        $result = $this->find('count', array(
            'conditions' => $conditions
            ));
        return $result < 1;
    }
    
    public function notRegex($check, $regex=null) 
    {
        return VusionValidation::customNot($check['content'], $regex);
    }
    
    
}
