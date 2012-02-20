<?php

App::uses('AppController','Controller');
App::uses('Script','Model');
//App::uses('VumiSupervisord','Lib');
App::uses('VumiRabbitMQ', 'Lib');

class ScriptsController extends AppController
{

    var $components = array('RequestHandler', 'Acl', 'VumiSupervisord');
    var $helpers = array('Js' => array('Jquery'));


    public function beforeFilter()
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
        $this->RequestHandler->accepts('json');
        $this->RequestHandler->addInputType('json', array('json_decode'));
    }


    public function index()
    {
        $programName = $this->Session->read($this->params['program'].'_name');
        $draft = $this->Script->find('draft');
        //print_r($draft);
        if (count($draft)){
            $script = $draft[0]['Script'];
        } else {
            $script = null;
        }
        $this->set(compact('programName', 'script'));
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
        $programName = $this->Session->read($this->params['program'].'_name');
        $programUrl = $this->params['program'];
        $draft = $this->Script->find('draft');
        if (count($draft)){
            $script = $draft[0]['Script'];
        } else {
            $script = null;
        }
        $this->set(compact('programName', 'programUrl', 'script'));
    }


    public function activate_draft()
    {
        $programName = $this->Session->read($this->params['program'].'_name');
        $result_db = $this->Script->makeDraftActive();
        $result_supervisord = $this->VumiSupervisord->startWorker($this->params['program']);
        $result_rabbitmq = $this->VumiRabbitMQ->sendInitMessageToWorker(
            $this->params['program'], 
            $this->Session->read($this->params['program'].'_db'));
        $this->VumiRabbitMQ->sendStartMessageToWorker(
            $this->params['program']);
        //$this->set(compact('programName', 'result_db', 'result_supervisord'));
        $this->redirect(array('program'=>$this->params['program'], 'controller'=>'home'));
    }


    public function active()
    {
        $programName = $this->Session->read($this->params['program'].'_name');
        $programUrl = $this->params['program'];
        $draft = $this->Script->find('active');
        if (count($draft)){
            $script = $draft[0]['Script'];
        } else {
            $script = null;
        }
        $this->set(compact('programName', 'programUrl', 'script'));
    }


    function constructClasses()
    {
        parent::constructClasses();
        
        $options = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->Script = new Script($options);
        
        $this->VumiRabbitMQ = new VumiRabbitMQ();
    }


}

