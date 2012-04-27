<?php

App::uses('AppController','Controller');
App::uses('Script','Model');
App::uses('Program', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramScriptsController extends AppController
{

    public $uses = array('Script');

    var $components = array('RequestHandler', 'Acl');
    var $helpers    = array('Js' => array('Jquery'));


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
        $this->Script         = new Script($options);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->VumiRabbitMQ   = new VumiRabbitMQ();
    }


    public function index()
    {
        $draft = $this->Script->find('draft');
        
        if (count($draft)) {
            $script = $draft[0]['Script'];
        } else {
            $script = null;
        }
        $this->set(compact('script'));
    }


    /** TODO: move this logic into the Model 
    * But when doing so the update didn't work due to duplicat entry on primary key.
    */
    public function add()
    {      
        if ($this->request->is('post')) {
            $saveData['Script'] = (is_object($this->request->data) 
                    ? get_object_vars($this->request->data) 
                    : $this->request->data
                    );
            $draft = $this->Script->find('draft');
            if ($draft) {    
                $this->Script->create();
                $this->Script->id          = $draft[0]['Script']['_id'];
                $saveData['Script']['_id'] = $draft[0]['Script']['_id'];
                
                if ($this->Script->save($saveData)) {
                    $this->set(
                        'result', array(
                            'status' => '1',
                            'id' => $this->Script->id
                            )
                    );
                } else {
                    $this->set(
                        'result', array(
                            'status' => '0'
                            )
                    );
                }
            } else {
                $this->Script->create();
                if ($this->Script->save($saveData)) {
                    $this->set(
                        'result', array(
                            'status' => '1',
                            'id' => $this->Script->id
                            )
                    );
                } else {
                    $this->set('result', array('status' => '0'));
                }
            }
        }
    }


    public function draft()
    {
        $draft = $this->Script->find('draft');
        
        if (count($draft)) {
            $script = $draft[0]['Script'];
        } else {
            $script = null;
        }
        $this->set(compact('script'));
    }


    protected function _notifyUpdateBackendWorker($workerName)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateSchedule($workerName);
    }


    public function activateDraft()
    {
        $programUrl = $this->params['program'];
        if ($this->_hasAllProgramSettings()) {
            $this->Script->makeDraftActive(); 
            $this->_notifyUpdateBackendWorker($programUrl);
        } else {
            $this->Session->setFlash(__('Please set the program settings then try again.'));
        }
   
        $this->redirect(array('program'=>$programUrl, 'controller'=>'programHome'));
    }


    public function active()
    {
        $draft = $this->Script->find('active');
        if (count($draft)) {
            $script = $draft[0]['Script'];
        } else {
            $script = null;
        }
        $this->set(compact('script'));
    }


    public function validateKeyword()
    {       
//    	print_r($this->request);
//        $keywordToValidate = $this->request->query['keyword'];
        $keywordToValidate = $this->request->data['keyword'];
        $programs          = $this->Program->find(
            'all', 
            array('conditions'=> 
            array('Program.url !='=> $this->params['program'])
            )
        );
        $shortCode = $this->ProgramSetting->find('getProgramSetting', array('key'=>'shortcode'));
       
        if (!$shortCode) {
            $this->set('result', array(
                    'status'=>'fail', 
                    'message' => 'program shortcode has not been defined, please go to program settings'
                    ));
            return;
        }
 
        foreach ($programs as $program) {
            $programSettingModel = new ProgramSetting(array('database'=>$program['Program']['database']));
            if ($programSettingModel->find('hasProgramSetting', array('key'=>'shortcode', 'value'=> $shortCode))) {
                $scriptModel = new Script(array('database'=>$program['Program']['database']));
                $foundKeyword = $scriptModel->find('hasKeyword', array('keyword' => $keywordToValidate));
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

