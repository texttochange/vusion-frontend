<?php

App::uses('AppController', 'Controller');
App::uses('ProgramSetting', 'Model');
App::uses('ShortCode', 'Model');
App::uses('Template', 'Model');
App::uses('VumiRabbitMQ', 'Lib');

class ProgramSettingsController extends AppController
{
    
    var $helpers = array('Js' => array('Jquery'));
    var $components = array('Keyword');
    
    
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
        $this->_instanciateVumiRabbitMQ();
    }
    
    
    protected function _instanciateVumiRabbitMQ(){
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
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
            
            $keywordValidation = $this->Keyword->areProgramKeywordsUsedByOtherPrograms(
                $this->Session->read($programUrl.'_db'), 
                $this->request->data['ProgramSetting']['shortcode']);
            if ($keywordValidation !== array()) {
                $this->Session->setFlash(
                    $this->Keyword->validationToMessage($keywordValidation),
                    'default', array('class'=>'message failure'));
            } else { 
                if ($this->ProgramSetting->saveProgramSettings($this->request->data['ProgramSetting'])) {
                    $this->_notifyUpdateProgramSettings($programUrl);
                    $this->Session->setFlash(__("Program Settings saved."),
                        'default',
                        array('class'=>'message success'));
                    $this->redirect(array(
                        'program' => $programUrl,
                        'controller' => 'programSettings',
                        'action' => 'edit'));
                    
                } else {
                    $this->set('validationErrorsArray', $this->ProgramSetting->validationErrors);
                    $this->Session->setFlash(__("Save settings failed."),
                        'default', array('class'=>'message failure'));
                }
            }
        }
        
        ## Set all the form options
        $shortcodes = $this->ShortCode->find('all');
        $openQuestionTemplateOptions     = $this->Template->getTemplateOptions('open-question');
        $closedQuestionTemplateOptions   = $this->Template->getTemplateOptions('closed-question');
        $unmatchingAnswerTemplateOptions = $this->Template->getTemplateOptions('unmatching-answer');
        $this->set(compact(
            'openQuestionTemplateOptions',
            'closedQuestionTemplateOptions',
            'unmatchingAnswerTemplateOptions',
            'shortcodes'));
        
        ## in case it's not an edit, the setting need to be retrieved from the database
        if (!isset($this->request->data['ProgramSetting'])) {
            $settings = $this->ProgramSetting->getProgramSettings();
            if ($settings) {
                $programSettings['ProgramSetting'] = $settings;
                if (isset($settings['timezone'])) {
                    $this->set('programTimezone', $settings['timezone']);
                }
                $this->request->data = $programSettings;
            }
            ## set a user friendly format
            if (isset($this->request->data['ProgramSetting']['credit-from-date'])) {
                $fromDate = new DateTime($this->request->data['ProgramSetting']['credit-from-date']);
                $this->request->data['ProgramSetting']['credit-from-date'] = $fromDate->format('d/m/Y');
            }
            if (isset($this->request->data['ProgramSetting']['credit-to-date'])) {
                $toDate = new DateTime($this->request->data['ProgramSetting']['credit-to-date']);
                $this->request->data['ProgramSetting']['credit-to-date'] = $toDate->format('d/m/Y');
            }
        }
    }
    
    
    protected function _notifyUpdateProgramSettings($workerName)
    {
        return $this->VumiRabbitMQ->sendMessageToReloadProgramSettings($workerName);
    }
    
    
}
