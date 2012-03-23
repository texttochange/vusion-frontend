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
        
        $options      = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->Script = new Script($options);
        
        $this->VumiRabbitMQ = new VumiRabbitMQ();
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
            /**
            * Below line in case need to remove the [script], 
            * however it create difficulties for the javascript rendering
            */
            /* $saveData['Script'] = (is_object($data['script']) 
            *                       ? get_object_vars($data['script']) 
            *                       : $data['script']);
            */

            $draft = $this->Script->find('draft');
            if ($draft) {    
                $this->Script->create();
                $this->Script->id          = $draft[0]['Script']['_id'];
                $saveData['Script']['_id'] = $draft[0]['Script']['_id'];
                $this->Script->save($saveData);
                $this->set(
                    'result', array(
                        'status' => '1',
                        'id' => $this->Script->id
                        )
                    );
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


    public function activateDraft()
    {
        $programUrl = $this->params['program'];

        $this->Script->makeDraftActive();
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
        if (count($draft)) {
            $script = $draft[0]['Script'];
        } else {
            $script = null;
        }
        $this->set(compact('script'));
    }


    public function validateKeyword()
    {       
        $keywordToValidate = $this->request->data['keyword'];
        $programs          = $this->Program->find(
        	'all', 
        	array('conditions'=> 
        		array('Program.url !='=> $this->params['program'])
        		)
        	);
        $programSetting    = new ProgramSetting(
            array('database'=>($this->Session->read($this->params['program']."_db")))
            );
        $shortCode = $programSetting->find('getProgramSetting', array('key'=>'shortcode'));
       
        if (!$shortCode) {
            $this->set('result', array(
                    'status'=>0, 
                    'message' => 'program shortcode not define, please go to program settings'
                    ));
            return;
        }
 
        foreach ($programs as $program) {
            $programSettingModel = new ProgramSetting(array('database'=>$program['Program']['database']));
            if ($programSettingModel->find(
                'hasProgramSetting', array(
                    'key'=>'shortcode', 
                    'value'=> $shortCode))
                    ) {
                $scriptModel = new Script(array('database'=>$program['Program']['database']));
                if ($scriptModel->find('keyword', array('keyword' => $keywordToValidate))) {
                    $this->set(
                        'result', array(
                            'status'=>0, 
                            'message'=>'already used by: ' . $program['Program']['name'])
                        );
                    return;
                }
            }
        }
        $this->set('result', array('status'=>1));
    }


}

