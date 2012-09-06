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

}
