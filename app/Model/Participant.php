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
                'message' => "A phone number must begin with a '+' sign and end with a serie of digits such as +3345678733.",
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
        if ($this->id) {
            $conditions = array('id'=>array('$ne'=> $this->id),'phone' => $check['phone']);
        } else {
            $conditions = array('phone' => $check['phone']);
        }
        $result = $this->find('count', array(
            'conditions' => $conditions
            ));
        return $result < 1;            
    }

    
    public function beforeValidate()
    {
        $this->beforeSave();
    }
    
    
    public function hasPlus($check)
    {
        $regex = '/^\+[0-9]+/';
        return preg_match($regex, $check['phone']);
    }
    
    
    public function beforeSave()
    {
        

        if (!isset($this->data['Participant']['phone']) or $this->data['Participant']['phone'] == "" )
            return false;

        $this->data['Participant']['phone'] = trim($this->data['Participant']['phone']);
        $this->data['Participant']['phone'] = preg_replace("/^(00|0)/", "+",$this->data['Participant']['phone']);    
        if (!preg_match('/^\+[0-9]+/', $this->data['Participant']['phone'])) 
            $this->data['Participant']['phone'] = "+".$this->data['Participant']['phone']; 

        $this->data['Participant']['phone'] = (string) $this->data['Participant']['phone'];
        
        if (isset($this->data['Participant']['name'])) {
            $this->data['Participant']['name'] = trim($this->data['Participant']['name']);
            $this->data['Participant']['name'] = str_replace("\n" , "", $this->data['Participant']['name']);
        }

        return true;
    }
    
    
}
