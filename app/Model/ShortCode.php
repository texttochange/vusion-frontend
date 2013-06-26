<?php
App::uses('MongoModel', 'Model');

class ShortCode extends MongoModel
{

    var $specific    = true;
    var $name        = 'ShortCode';
    var $useDbConfig = 'mongo';
    var $useTable    = 'shortcodes';

    var $localPrefixedShortCodePattern = '/^[0-9]+-[0-9]+/';
    var $internationalShortCodePattern = '/^\+[0-9]+/';
    var $maxCharacterPerSmsOptions = array(70, 140, 160);
    
    function getModelVersion()
    {
        return '2';
    }
   
    function getRequiredFields($objectType=null)
    {
        return array(
            'country',
            'shortcode',
            'international-prefix',
            'error-template',
            'support-customized-id',
            'supported-internationally',
            'max-character-per-sms');
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
            if (preg_match($this->localPrefixedShortCodePattern, $query['prefixShortCode'])) {
                $details = explode("-",$query['prefixShortCode']);
                $query['conditions']['ShortCode.shortcode'] = $details[1];
                $query['conditions']['ShortCode.international-prefix'] = $details[0];
                return $query;
            } elseif (preg_match($this->internationalShortCodePattern, $query['prefixShortCode'])) {
                $query['conditions']['ShortCode.shortcode'] =  $query['prefixShortCode'];
                return $query;
            } 
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
                ),
            'hasToIncludePrefix' => array(
                'rule' => 'hasToIncludePrefix',
                'message' => 'An supported internationally shortcode should include the international prefix.',
                'required' => true
                ),
            'notAllowSameNationalShortCodeInCountriesWithMatchingInternationalPrefix' => array(
                'rule' => 'notAllowSameNationalShortCodeInCountriesWithMatchingInternationalPrefix',
                'message' => 'Same national shortcode number is used in a country with matching international prefix, the system cannot handle this.',
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
            ),
        'max-character-per-sms' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please choose a maximum number of characters per sms.'
                ),
            'validValue'=> array(
                'rule' => array('inlist', array(70, 140, 160)),
                'message' => 'The valid value are only 70, 140 and 160.'
                ),
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
            if (!isset($this->data['ShortCode']['supported-internationally']) or $this->data['ShortCode']['supported-internationally']==0) { 
               $conditions = array(
                   'country' => $this->data['ShortCode']['country'], 
                   'shortcode' => $this->data['ShortCode']['shortcode']);
            } else {
                $conditions = array(
                   'shortcode' => $this->data['ShortCode']['shortcode']);       
            }
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
        $this->data['ShortCode']['international-prefix'] = trim($this->data['ShortCode']['international-prefix']);
        $this->data['ShortCode']['supported-internationally'] = intval($this->data['ShortCode']['supported-internationally']);
        $this->data['ShortCode']['support-customized-id'] = intval($this->data['ShortCode']['support-customized-id']);
        if (isset($this->data['ShortCode']['max-character-per-sms'])) {
            $this->data['ShortCode']['max-character-per-sms'] = intval($this->data['ShortCode']['max-character-per-sms']);
        }
        $this->_setDefault('max-character-per-sms', 160);
        return true;
    }

    public function hasToIncludePrefix($check)
    {
        if (isset($this->data['ShortCode']['supported-internationally']) and $this->data['ShortCode']['supported-internationally']==1) {
                $pattern = "/\+".$this->data['ShortCode']['international-prefix'].'/';
                return preg_match($pattern, $this->data['ShortCode']['shortcode']);
        } 
        return true;
    }

    public function notAllowSameNationalShortCodeInCountriesWithMatchingInternationalPrefix($check)
    {
        if ($this->data['ShortCode']['supported-internationally']==1)  {
            return true;
        }
        
        $regex = '';
        $prefix = '';
        foreach (str_split($this->data['ShortCode']['international-prefix']) as $digit) {
            $prefix .= $digit;
            $regex .= '('.$prefix.')';
        }

        $conditions = array(
            'shortcode' => $this->data['ShortCode']['shortcode'],
            'international-prefix' => new MongoRegex('/^['.$regex.']$/')
            );

         $result = $this->find('count', array(
            'conditions' => $conditions
            ));

        return $result < 1;   
    }

}
