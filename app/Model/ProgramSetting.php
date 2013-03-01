<?php

App::uses('MongoModel', 'Model');
App::uses('DialogueHelper', 'Lib');


class ProgramSetting extends MongoModel
{


    var $specific    = true;
    var $name        = 'ProgramSetting';
    var $useDbConfig = 'mongo';

    var $settings = array(
        'shortcode',
        'timezone',
        'international-prefix',
        'default-template-closed-question',
        'default-template-open-question',
        'default-template-unmatching-answer',
        'unmatching-answer-remove-reminder', 
        'customized-id',
        'double-matching-answer-feedback',
        'double-optin-error-feedback',
        'request-and-feedback-prioritized'
        );

    function getModelVersion()
    {
        return "2";
    }

    function getRequiredFields($objectType=null)
    {
        return array(
            'key',
            'value'
            );
    }


    public $findMethods =  array(
        'programSetting' => true,
        'count' => true,
        'hasProgramSetting' => true,
        'getProgramSetting' => true,
        );


    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        
        $this->DialogueHelper = new DialogueHelper();
    }


    public function beforeValidate()
    {
        parent::beforeValidate();

        if (!in_array($this->data['ProgramSetting']['key'], $this->settings)) {
            return false;
        } 

        if ($this->data['ProgramSetting']['value'] == '') {
            $this->data['ProgramSetting']['value'] = null;
        }

        if ($this->data['ProgramSetting']['key'] == 'unmatching-answer-remove-reminder') {
            $this->data['ProgramSetting']['value'] = intval($this->data['ProgramSetting']['value']);
        }
        
        if ($this->data['ProgramSetting']['key'] == 'request-and-feedback-prioritized'
                and $this->data['ProgramSetting']['value'] == '1') {
            $this->data['ProgramSetting']['value'] = 'prioritized';
        }
    
    }

    
    protected function _findProgramSetting($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions']['ProgramSetting.key'] = $query['key'];
            return $query;
        }
        return $results;
    }

    
    protected function _findHasProgramSetting($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions']['ProgramSetting.key'] = $query['key'];
            $query['conditions']['ProgramSetting.value'] = $query['value'];
            return $query;
        }
        return $results;
    }
    

    protected function _findGetProgramSetting($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions']['ProgramSetting.key'] = $query['key'];
            return $query;
        }
        if (isset($results[0]))
            return $results[0]['ProgramSetting']['value'];
    	else
            return null;
    }

    public function saveProgramSetting($key, $value) 
    {
        $setting = $this->find('all', array('conditions' => array('ProgramSetting.key' => $key)));
        
        $this->create();
        if ($setting) {
            $this->id = $setting[0]['ProgramSetting']['_id'];
        }
        return $this->save(
            array(
                'key' => $key,
                'value' => $value
                )
            );
    }

    public function getProgramSettings()
    {
        $rawSettings = $this->find('all');
        $settings = array();
        foreach ($rawSettings as $setting) {
            $settings[$setting['ProgramSetting']['key']] = $setting['ProgramSetting']['value'];
        }
        return $settings;
    }


    public function isNotPast($time)
    {      
        $programNow = $this->getProgramTimeNow();
        if ($programNow==null)
            return __("The program settings are incomplete. Please specificy the Timezone.");
        if ($time < $programNow)
            return false;
        return true;
    }


    public function getProgramTimeNow()
    {
        $now = new DateTime('now');
        $programTimezone = $this->find('getProgramSetting', array('key' => 'timezone'));
        if ($programTimezone == null)
            return null;
        
        date_timezone_set($now, timezone_open($programTimezone));        
        return $now;       
    }


}
