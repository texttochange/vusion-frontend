<?php

App::uses('AppController', 'Controller');
App::uses('Script', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramSimulatorController extends AppController
{

    public $uses    = array('Script');
    var $components = array('RequestHandler');


    public function constructClasses()
    {
        parent::constructClasses();
        $options            = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->Script       = new Script($options);
        $this->VumiRabbitMQ = new VumiRabbitMQ();
        $this->redis        =  new Redis();
        $this->redis->connect('127.0.0.1');
    }


    public function beforeFilter()
    {
    	parent::beforeFilter();
        $this->Auth->allow('*');
    }


    public function simulate($id = null)
    {
        $this->Script->id = $this->params['id'];
        if (!$this->Script->exists()){
            $this->Session->setFlash(
                __('The script id is not in the database, please choose one available.'));
            $scripts= array(
            	    'currently active' => $this->Script->find('active'),
            	    'draft' => $this->Script->find('draft')
            	    );
            $this->set(compact('scripts'));
            return;
        }
        $this->_startSimulateScript($this->Script->id);
        
    }


    protected function _startSimulateScript($script_id)
    {
    	 $database_name = $this->Session->read($this->params['program'].'_db');
         $this->VumiRabbitMQ->sendMessageToCreateSimulatedWorker($database_name, $script_id);
    }


    public function send()
    {
        $programUrl = $this->params['program'];
        if ($this->request->is('post')) {
            $message = $this->request->data['message'];
            $this->redis->lPush('vusion:'.$programUrl.':simulator:inbound', $message);
        }
    }


    public function receive()
    {
        $programUrl = $this->params['program'];
        $message = $this->redis->lPop('vusion:'.$programUrl.':simulator:outbound');
        $this->set(compact('message'));
    }

}
