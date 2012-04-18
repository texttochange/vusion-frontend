<?php

App::uses('AppController', 'Controller');
App::uses('Script', 'Model');

class ProgramSimulatorController extends AppController
{

    public $uses = array('Script');


    public function constructClasses()
    {
        parent::constructClasses();
        $options      = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->Script = new Script($options);
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
        }
    }


    public function send()
    {
    }


    public function receive()
    {
    }

}
