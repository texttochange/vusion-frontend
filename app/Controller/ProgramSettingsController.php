<?php

App::uses('AppController', 'Controller');
App::uses('ProgramSetting', 'Model');
App::uses('ShortCode', 'Model');
App::uses('Template', 'Model');
App::uses('VumiRabbitMQ', 'Lib');

class ProgramSettingsController extends AppController
{

    var $helpers = array('Js' => array('Jquery'));
    public $components = array('Keyword');

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
        
        $optionVisionDb     = array('database' => 'vusion');
        $this->ShortCode    = new ShortCode($optionVisionDb);
        $this->Template     = new Template($optionVisionDb);
        $this->VumiRabbitMQ = new VumiRabbitMQ(
            Configure::read('vusion.rabbitmq'));
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
    	
        $programSettings = $this->ProgramSetting->getProgramSettings();
        if (isset($programSettings['default-template-open-question'])) {
            $template = $this->Template->read(null, $programSettings['default-template-open-question']);
            $programSettings['default-template-open-question'] = $template['Template']['name'];
        }
        if (isset($programSettings['default-template-closed-question'])) {
            $template = $this->Template->read(null, $programSettings['default-template-closed-question']);
            $programSettings['default-template-closed-question'] = $template['Template']['name'];
        }
        if (isset($programSettings['default-template-unmatching-answer'])) {
            $template = $this->Template->read(null, $programSettings['default-template-unmatching-answer']);
            $programSettings['default-template-unmatching-answer'] = $template['Template']['name'];
        }
        $this->set(compact('programSettings'));
    }


    public function edit()
    {    	
        $programUrl = $this->params['program'];

        if ($this->request->is('post') || $this->request->is('put')) {
                    
            $keywordValidation = $this->Keyword->validateProgramKeywords(
                $this->Session->read($programUrl.'_db'), 
                $this->request->data['ProgramSettings']['shortcode']);
            if ($keywordValidation['status'] == 'fail') {
                $this->Session->setFlash(
                    __("Keyword already used on this shortcode: %s", $keywordValidation['message']),
                    'default',
                    array('class'=>'message failure')
                    );
            } else {   
                foreach ($this->request->data['ProgramSettings'] as $key => $value) {
                    if ($this->ProgramSetting->saveProgramSetting($key, $value)) {
                        $this->Session->setFlash(__("Program Settings saved."),
                            'default',
                            array('class'=>'message success')
                            );
                    } else {
                        $this->Session->setFlash(__("Save settings failed."),
                            'default',
                            array('class'=>'message failure')
                            );
                    }
                }
                $this->_notifyUpdateProgramSettings($programUrl);
            }
        }
        $shortcodes = $this->ShortCode->find('all');
        $this->set(compact('shortcodes'));
       
        $openQuestionTemplateOptions     = $this->Template->getTemplateOptions('open-question');
        $closedQuestionTemplateOptions   = $this->Template->getTemplateOptions('closed-question');
        $unmatchingAnswerTemplateOptions = $this->Template->getTemplateOptions('unmatching-answer');
        $this->set(compact('openQuestionTemplateOptions',
            'closedQuestionTemplateOptions',
            'unmatchingAnswerTemplateOptions'));

        $settings = $this->ProgramSetting->getProgramSettings();

        if ($settings) {
            $programSettings['ProgramSettings'] = $settings;
    	    if (isset($settings['timezone']))
    	        $this->set('programTimezone', $settings['timezone']);
    	    
            $this->request->data = $programSettings;
        }
       
    }


    protected function _notifyUpdateProgramSettings($workerName)
    {
        return $this->VumiRabbitMQ->sendMessageToReloadProgramSettings($workerName);
    }


}
