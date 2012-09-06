<?php

App::uses('MongoModel', 'Model');


class ProgramSetting extends MongoModel
{


    var $specific    = true;
    var $name        = 'ProgramSetting';
    var $useDbConfig = 'mongo';

     function getModelVersion()
    {
        return "1";
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
            return array();
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

}
