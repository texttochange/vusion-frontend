<?php

App::uses('MongoModel', 'Model');

class ContentVariable extends MongoModel
{
    var $specific = true;
    var $name = 'ContentVariable';
    var $useDbConfig = 'mongo';
    
    function getModelVersion()
    {
        return '1';
    }
    
    
    function getRequiredFields($objectType=null)
    {
        return array(
            //'keys',
            'key',
            'value'
            );
    }
    
    
    public $validate = array(
        'key' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Dynamic content must have a key.'
                ),
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This key already exists. Please choose another.'
                ),
            ),
        'value' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter a value for this dynamic content.'
                ),
            ),
        );
    
    
    /*public function validateKays($check)
    {
        foreach ($check['keys'] as $element) {
            
        }
    }*/
    
    
    public function isUnique($check)
    {
        if ($this->id) {
            $conditions = array('id'=>array('$ne'=> $this->id),'key' => $check['key']);
        } else {
            $conditions = array('key' => $check['key']);
        }
        $result = $this->find('count', array(
            'conditions' => $conditions
            ));
        return $result < 1;
    }
    
    
    public function beforeValidate()
    {
        parent::beforeValidate();
    }
    
}
