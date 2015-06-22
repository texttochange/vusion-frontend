<?php
App::uses('BaseProgramSpecificController','Controller');
App::uses('ProgramSetting', 'Model');
App::uses('ShortCode', 'Model');
App::uses('Template', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramSettingsController extends BaseProgramSpecificController
{

    var $uses = array(
        'ProgramSetting',
        'ShortCode',
        'Template',
        'User',
        'Group');
    var $components = array(
        'Keyword',
        'ProgramAuth',
        'ArchivedProgram');
    var $helpers = array(
        'Js' => array(
            'Jquery'),
        'Time');
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
    }
    
    
    public function constructClasses()
    {
        parent::constructClasses();
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
        if (isset($programSettings['contact'])) {
            $user = $this->User->find('first', array('conditions' => array('User.id' => $programSettings['contact'])));
            $programSettings['contact'] = $user;
        }

        $this->set(compact('programSettings'));
    }
    
    
    public function edit()
    {        
        $programUrl = $this->params['program'];
        
        if ($this->programDetails['status'] === 'archived') {
            $this->Session->setFlash(__("The Program Settings cannot be edited while the program is archived."));
            $this->redirect(array(
                'program' => $programUrl,
                'controller' => 'programSettings',
                'action' => 'view'));
        }

        if ($this->request->is('post')) {
            
            $keywordValidation = $this->Keyword->areProgramKeywordsUsedByOtherPrograms(
                $this->programDetails['database'],
                $this->request->data['ProgramSetting']['shortcode']);
            
            if ($this->ProgramSetting->saveProgramSettings($this->request->data['ProgramSetting'], $keywordValidation)) {
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
        
        ## Set all the form options
        $shortcodes = $this->ShortCode->find(
            'all', 
            array('conditions'=> array('status' => array( '$ne'=> 'archived'))));
        
        $openQuestionTemplateOptions     = $this->Template->getTemplateOptions('open-question');
        $closedQuestionTemplateOptions   = $this->Template->getTemplateOptions('closed-question');
        $unmatchingAnswerTemplateOptions = $this->Template->getTemplateOptions('unmatching-answer');
        $contactUsers = $this->User->find('all', array(
            'conditions' => array('Group.name' => array('administrator', 'manager', 'program manager')),
            'fields' => array('id', 'username', 'group_id')));
        $currentKeywords = $this->Keyword->getCurrentKeywords($this->programDetails['database']);
        $this->set(compact(
            'openQuestionTemplateOptions',
            'closedQuestionTemplateOptions',
            'unmatchingAnswerTemplateOptions',
            'shortcodes',
            'contactUsers',
            'currentKeywords'));
        
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
            ## formating the data, should he done in the view one cannot edit the $this->data fields
            ## set a user friendly format
            if (isset($this->request->data['ProgramSetting']['credit-from-date'])) {
                $fromDate = new DateTime($this->request->data['ProgramSetting']['credit-from-date']);
                $this->request->data['ProgramSetting']['credit-from-date'] = $fromDate->format('d/m/Y');
            }
            if (isset($this->request->data['ProgramSetting']['credit-to-date'])) {
                $toDate = new DateTime($this->request->data['ProgramSetting']['credit-to-date']);
                $this->request->data['ProgramSetting']['credit-to-date'] = $toDate->format('d/m/Y');
            }
            if (isset($this->request->data['ProgramSetting']['authorized-keywords'])) {
                if (is_array($this->request->data['ProgramSetting']['authorized-keywords'])) {
                    $this->request->data['ProgramSetting']['authorized-keywords'] = implode(', ', $this->request->data['ProgramSetting']['authorized-keywords']);
                }
            }
        }
    }
    
    
    protected function _notifyUpdateProgramSettings($workerName)
    {
        return $this->VumiRabbitMQ->sendMessageToReloadProgramSettings($workerName);
    }
    
    
}
