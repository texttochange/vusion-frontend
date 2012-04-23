<?php

App::uses('AppController', 'Controller');
App::uses('ProgramSetting', 'Model');
App::uses('ShortCode', 'Model');

class ProgramSettingsController extends AppController
{

    var $helpers = array('Js' => array('Jquery'));


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
        
        $optionsShortCode = array('database' => 'vusion');
        $this->ShortCode  = new ShortCode($optionsShortCode);
    }


    public function index()
    {
    	$programUrl = $this->params['program'];
        $isEditor = $this->Acl->check(
            array(
                'User' => array('id' => $this->Session->read('Auth.User.id')),
                ), 
            'controllers/ProgramSettings/edit');
        
        if ($isEditor) {
            $this->redirect(array('program'=>$programUrl, 'action'=>'edit'));	
        } else {
            $this->redirect(array('program'=>$programUrl, 'action'=>'view'));	
        }
    }


    public function view()
    {
    	
        $programSettings = $this->ProgramSetting->find('all');
        $this->set(compact('programSettings'));
    }


    public function edit()
    {    	
        if ($this->request->is('post') || $this->request->is('put')) {
            foreach ($this->request->data['ProgramSettings'] as $key => $value) {
                 //echo $key ." = " . $value;
                $programSetting = $this->ProgramSetting->find('programSetting', array( 'key' => $key));
                if (!$programSetting) {
                    //save
                    $this->ProgramSetting->create();
                } else {
                    //update
                    $this->ProgramSetting->id = $programSetting[0]['ProgramSetting']['_id'];
                }
                if ($this->ProgramSetting->save(array(
                        'key' => $key,
                        'value' => $value
                        ))){
                    $this->Session->setFlash("Program Settings saved",
                        'default',
                        array('class'=>'message success')
                    );
                }
            }
        }
        $shortcodes = $this->ShortCode->find('all');
        $this->set(compact('shortcodes'));
        $shortcode           = $this->ProgramSetting->find('programSetting', array( 'key' => 'shortcode'));
        $internationalprefix = $this->ProgramSetting->find('programSetting', array( 'key' => 'international-prefix'));
        $timezone            = $this->ProgramSetting->find('programSetting', array( 'key' => 'timezone'));
        //print_r($shortcode);
        if ($shortcode) {
            //echo "there is a shortcode";
            $programSettings = array(
                'ProgramSettings' => array (
                    'shortcode' => $shortcode[0]['ProgramSetting']['value'],
                    'international-prefix' => $internationalprefix[0]['ProgramSetting']['value'],
        	    'timezone' => $timezone[0]['ProgramSetting']['value']
        	    )
        	);
            
            $programTimezone = $this->ProgramSetting->find('programSetting', array('key' => 'timezone'));
    	    $this->set(compact('programTimezone'));
            
            $this->request->data = $programSettings;
            return $programSettings;
        }
        
    }


}
