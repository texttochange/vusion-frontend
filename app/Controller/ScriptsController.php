<?php

App::uses('AppController','Controller');
App::uses('Script','Model');
App::uses('VumiSupervisord','Lib');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('Program', 'Model');
App::uses('ProgramSetting', 'Model');

class ScriptsController extends AppController
{

    var $components = array('RequestHandler', 'Acl');
    var $helpers = array('Js' => array('Jquery'));


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
        
        $options = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->Script = new Script($options);
        
        $this->VumiRabbitMQ = new VumiRabbitMQ();
    }


    public function index()
    {
        $draft = $this->Script->find('draft');
        
        if (count($draft)){
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
            $saveData['Script'] = (is_object($this->request->data) ? get_object_vars($this->request->data) : $this->request->data);
            //below line in case need to remove the [script], however it create difficulties for the javascript rendering
            //$saveData['Script'] = (is_object($data['script']) ? get_object_vars($data['script']) : $data['script']);
            $draft = $this->Script->find('draft');
            //print_r($draft);
            if ($draft) {    
                //echo 'Draft case';
                $this->Script->create();
                $this->Script->id = $draft[0]['Script']['_id'];
                $saveData['Script']['_id'] = $draft[0]['Script']['_id'];
                //print_r($saveData);
                $this->Script->save($saveData);
                $this->set('result', array(
                        'status' => '1',
                        'id' => $this->Script->id
                        ));
            } else {
                //echo 'save case';
                $this->Script->create();
                if ($this->Script->save($saveData)) {
                    $this->set('result', array(
                        'status' => '1',
                        'id' => $this->Script->id
                        ));
                } else {
                    $this->set('result', array('status' => '0'));
                }
            }
        }
    }


    public function draft()
    {
        $draft = $this->Script->find('draft');
        
        if (count($draft)){
            $script = $draft[0]['Script'];
        } else {
            $script = null;
        }
        $this->set(compact('script'));
    }


    public function activateDraft()
    {
        $programUrl  = $this->params['program'];

        $result_db = $this->Script->makeDraftActive();
        /*
        $result_supervisord = $this->VumiSupervisord->startWorker($programUrl);
        $result_rabbitmq = $this->VumiRabbitMQ->sendInitMessageToWorker(
            $programUrl, 
            $this->Session->read($programUrl.'_db'));
        $this->VumiRabbitMQ->sendStartMessageToWorker($programUrl);
        */
        //$this->set(compact('programName', 'result_db', 'result_supervisord'));
        $this->redirect(array('program'=>$programUrl, 'controller'=>'home'));
    }


    public function active()
    {
        $draft = $this->Script->find('active');
        if (count($draft)){
            $script = $draft[0]['Script'];
        } else {
            $script = null;
        }
        $this->set(compact('script'));
    }

    public function validateKeyword()
    {
       
        $keywordToValidate = $this->request->data['keyword'];
        $programs = $this->Program->find('all');
        $programSetting = new ProgramSetting(array(
        	'database'=>($this->Session->read($this->params['program']."_db"))
        	));
       $shortCode = $programSetting->find('getProgramSetting', array('key'=>'shortcode'));
        
        foreach ($programs as $program) {
            $programSettingModel = new ProgramSetting(array('database'=>$program['Program']['database']));
            if ($programSettingModel->find('hasProgramSetting', array('key'=>'shortcode', 'value'=> $shortCode))) {
                $scriptModel = new Script(array('database'=>$program['Program']['database']));
                if ($scriptModel->find('keyword', array('keyword' => $keywordToValidate))){
                    $this->set('result', array('status'=>0, 'program'=>$program['Program']['name']));
                    return;
                }
            }
        }
//        echo "Not found any other program with this keyword";
        $this->set('result', array('status'=>1));
    }

}

