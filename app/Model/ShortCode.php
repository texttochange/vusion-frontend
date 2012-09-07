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
            ));

}
