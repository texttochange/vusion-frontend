<?php
App::uses('MongoModel', 'Model');

class ShortCode extends MongoModel
{

    var $specific    = true;
    var $name        = 'ShortCode';
    var $useDbConfig = 'mongo';
    var $useTable    = 'shortcodes';
    
    function getModelVersion()
    {
        return '1';
    }
   
    function getRequiredFields($objectType=null)
    {
        return array(
            'country',
            'shortcode',
            'international-prefix',
            'error-template',
            'support-customized-id');
    }

    var $findMethods = array(
        'prefixShortCode' => true,
        'count' => true,
        'all' => true,
        'first' => true,
        );

    protected function _findPrefixShortCode($state, $query, $results = array())
    {
        if ($state == 'before') {
            $details = explode("-",$query['prefixShortCode']);
            $query['conditions']['ShortCode.shortcode'] = $details[1];
            $query['conditions']['ShortCode.international-prefix'] = $details[0];
            return $query;
        }
        return $results[0];
    }

    //Need to validate the couple shortcode country is unique
    public $validate = array(
        'shortcode' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter a shortcode.'
                ),
            'isShortCodeCountryUnique'=> array(
                'rule' => 'isShortCodeCountryUnique',
                'message' => 'There is already the same shortcode for this country.',
                'required' => true
                )   
            ),
        'country' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please choose a country.'
                ),
            'isShortCodeCountryUnique'=> array(
                'rule' => 'isShortCodeCountryUnique',
                'message' => 'There is already the same shortcode for this country.',
                'required' => true
                )
            )
        );


    public function isShortCodeCountryUnique($check)
    {
        if ($this->id) {
            $conditions = array(
                'id'=>array('$ne'=> $this->id),
                'country' => $this->data['ShortCode']['country'], 
                'shortcode' => $this->data['ShortCode']['shortcode']);
        } else {
            $conditions = array(
                'country' => $this->data['ShortCode']['country'], 
                'shortcode' => $this->data['ShortCode']['shortcode']);
        }
        $result = $this->find('count', array(
            'conditions' => $conditions
            ));

        return $result < 1;            
    }

    public function beforeValidate()
    {
        parent::beforeValidate();
        $this->data['ShortCode']['shortcode'] = trim($this->data['ShortCode']['shortcode']);
        
        return true;
    }

}
