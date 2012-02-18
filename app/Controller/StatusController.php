<?php

App::uses('AppController','Controller');
App::uses('ParticipantsState','Model');

class StatusController extends AppController
{

    public $uses = array('ParticipantsState');
    var $components = array('RequestHandler');
    var $helpers = array('Js' => array('Jquery'));


    public function beforeFilter()
    {
        parent::beforeFilter();
        //For initial creation of the admin users uncomment the line below
        //$this->Auth->allow('*');
    }


    public function index()
    {
        $programName = $this->Session->read($this->params['program'].'_name');
        $programUrl = $this->params['program'];
        $statuses = $this->paginate();
        $this->set(compact('programName', 'programUrl', 'statuses'));
    }


    function constructClasses()
    {
        parent::constructClasses();
        
        $options = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->ParticipantsState = new ParticipantsState($options);
    }


}
