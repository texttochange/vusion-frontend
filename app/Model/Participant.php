<?php
App::uses('MongoModel', 'Model');


class Participant extends MongoModel
{

    var $specific = true;    

    var $name        = 'Participant';
    var $useDbConfig = 'mongo';
        
    public $validate = array(
        'phone' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter a phone number.'
                ),
            'hasPlus'=>array(
                'rule' => 'hasPlus',
                'message' => 'The phone number must begin with a "+" sign.',
                'required' => true
                ),
            'isReallyUnique' => array(
                'rule' => 'isReallyUnique',
                'message' => 'This phone number already exists in the participant list.',
                'required' => true
                )
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
         $this->data['Participant']['phone'] = (string) $this->data['Participant']['phone'];
         if (isset($this->data['Participant']['name']))
             $this->data['Participant']['name'] = str_replace("\n" , "", $this->data['Participant']['name']);
         return true;
    }
    
    
    public function hasPlus($check)
    {
        $regex = '/^\+[0-9]+/';
        return preg_match($regex, $check['phone']);
    }
    
    
}
