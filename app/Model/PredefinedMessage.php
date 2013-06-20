<?php

App::uses('MongoModel', 'Model');

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
    
}
