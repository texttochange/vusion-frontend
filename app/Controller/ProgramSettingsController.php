<?php

App::uses('AppController', 'Controller');
App::uses('ProgramSetting', 'Model');

class ProgramSettingsController extends AppController
{


    public function beforeFilter()
    {
        parent::beforeFilter();
    }


    public function constructClasses()
    {
        parent::constructClasses();
        
        $options = array(
            'database' => ($this->Session->read($this->params['program'].'_db'))
            );
        $this->ProgramSetting = new ProgramSetting($options);
    }


    public function edit()
    {
        if ($this->request->is('post') || $this->request->is('put')) {
            foreach($this->request->data['ProgramSettings'] as $key => $value) {
                 //echo $key ." = " . $value;
                 $programSetting = $this->ProgramSetting->find('programSetting', array( 'key' => $key));
                 if (!$programSetting){
                     //save
                     $this->ProgramSetting->create();
                 } else {
                     //update
                     $this->ProgramSetting->id = $programSetting[0]['ProgramSetting']['_id'];
                 }
                 $this->ProgramSetting->save(array(
                         'key' => $key,
                         'value' => $value
                         ));
            }
        }
        return;
    }


}
