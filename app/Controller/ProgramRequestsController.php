<?php
App::uses('AppController', 'Controller');
App::uses('Request', 'Model');

class ProgramRequestsController extends AppController
{
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('*');
    }


    function constructClasses()
    {
        parent::constructClasses();
        $options       = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->Request = new Request($options);
    }


    public function index()
    {
    }


    public function edit()
    {
    }


    public function add()
    {
    }


    public function delete()
    {
    }


}
