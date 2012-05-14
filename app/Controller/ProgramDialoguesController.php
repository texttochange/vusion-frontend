<?php
App::uses('AppController', 'Controller');
App::uses('Dialogue', 'Model');
App::uses('Program', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramDialoguesController extends AppController
{
    var $components = array('RequestHandler', 'Acl');


    public function beforeFilter()
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
        $this->RequestHandler->accepts('json');
        $this->RequestHandler->addInputType('json', array('json_decode'));
    }


    function constructClasses()
    {
        parent::constructClasses();
        $options              = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->Dialogue       = new Dialogue($options);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->VumiRabbitMQ   = new VumiRabbitMQ();
    }


    public function index()
    {
        $this->set('dialogues', $this->Dialogue->getActiveAndDraft());
    }


    public function save()
    {
        if ($this->request->is('post')) {
            $dialogue = (is_object($this->request->data)
                ? get_object_vars($this->request->data)
                : $this->request->data);
            $saveData['Dialogue'] = $dialogue['dialogue']; 
            //print_r($saveData);
            if ($this->Dialogue->saveDialogue($saveData)) {
                $this->set('result', 
                    array(
                        'status'=>'ok',
                        'dialogue-obj-id' => $this->Dialogue->id));
            } else {
                $this->set('result', array('status'=>'fail'));
            }
        }
    }

    public function edit()
    {
        $id = $this->params['id'];
        
        if (!isset($id))
            return;

        $this->Dialogue->id = $id;

        if (!$this->Dialogue->exists()) {
            $this->Session->setFlash(__("Dialogue doesn't exist."));
            return;
        }

        $this->set('dialogue', $this->Dialogue->read(null, $id));
    }

    public function activate()
    {
        $programUrl = $this->params['program'];
        $dialogueId = $this->params['id'];

        if ($this->_hasAllProgramSettings()) {
            if ($this->Dialogue->makeActive($dialogueId)) {
                $this->_notifyUpdateBackendWorker($programUrl);
                $this->Session->setFlash(__('Dialogue activated.'));
            } else
                $this->Session->setFlash(__('Dialogue unknown reload the page and try again.'));
        } else 
            $this->Session->setFlash(__('Please set the program settings then try again.'));
    
        
        $this->redirect(array('program'=>$programUrl, 'action' => 'index'));
    }


    public function validateKeyword()
    {
        $shortCode = $this->ProgramSetting->find('getProgramSetting', array('key'=>'shortcode'));
        if (!$shortCode) {
            $this->set('result', array(
                    'status'=>'fail', 
                    'message' => 'Program shortcode has not been defined, please go to program settings'
                    ));
            return;
        }

        $keywordToValidate = $this->request->data['keyword'];
        $dialogueId        = $this->request->data['dialogue-id'];
 
        /**Is the keyword used by another dialogue of the same program*/
        $dialogueUsingKeyword = $this->Dialogue->getActiveDialogueUseKeyword($keywordToValidate);
        if ($dialogueUsingKeyword && 
            $dialogueUsingKeyword['Dialogue']['dialogue-id'] != $dialogueId) {
            $this->set(
                'result', array(
                    'status'=>'fail', 
                    'message'=>$keywordToValidate.' already used in same program by: '.$dialogueUsingKeyword['Dialogue']['name'])
                );
            return;
            }

        /**Is the keyword used by another program*/
        $programs = $this->Program->find(
            'all', 
            array('conditions'=> 
            array('Program.url !='=> $this->params['program'])
            )
        );
        foreach ($programs as $program) {
            $programSettingModel = new ProgramSetting(array('database'=>$program['Program']['database']));
            if ($programSettingModel->find('hasProgramSetting', array('key'=>'shortcode', 'value'=> $shortCode))) {
                $dialogueModel = new Dialogue(array('database'=>$program['Program']['database']));
                $foundKeyword = $dialogueModel->useKeyword($keywordToValidate);
                if ($foundKeyword) {
                    $this->set(
                        'result', array(
                            'status'=>'fail', 
                            'message'=>$foundKeyword.' already used by: ' . $program['Program']['name'])
                        );
                    return;
                }
            }
        }
        $this->set('result', array('status'=>'ok'));

    }    


    public function testSendAllMessages()
    {
        $programUrl = $this->params['program'];
        if (isset($this->params['id'])) {
            $objectId = $this->params['id'];
            $this->set(compact('objectId'));
        }
 
        if ($this->request->is('post')) {
            $phoneNumber = $this->request->data['SendAllMessages']['phone-number'];
            $dialogueId  = $this->request->data['SendAllMessages']['dialogue-obj-id'];
            $result      = $this->_notifySendAllMessagesBackendWorker($programUrl, $phoneNumber, $dialogueId);
            $this->Session->setFlash(
                __('Message(s) being sent, should arrive shortly...'), 
                'default',
                array('class' => "message success")
                );
        }
        $dialogues = $this->Dialogue->getActiveAndDraft();    
        $this->set(compact('dialogues'));         
    }


    protected function _notifySendAllMessagesBackendWorker($workerName, $phone, $scriptId)
    {
        return $this->VumiRabbitMQ->sendMessageToSendAllMessages($workerName, $phone, $scriptId);
    }


    protected function _notifyUpdateBackendWorker($workerName)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateSchedule($workerName);
    }

    
    protected function _hasAllProgramSettings()
    {
        $shortCode = $this->ProgramSetting->find('getProgramSetting', array('key'=>'shortcode'));
        $timezone = $this->ProgramSetting->find('getProgramSetting', array('key'=>'timezone'));        
        if ($shortCode and $timezone) {
            return true;
        }
        return false;
    }


}
