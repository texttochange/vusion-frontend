<?php
App::uses('AppController','Controller');
App::uses('ParticipantsState','Model');

class StatusController extends AppController {
	
	//public $name = 'Participantsstates';
	
	var $components = array('RequestHandler');
	var $helpers = array('Js' => array('Jquery'));
	
	
	public function beforeFilter() {
		parent::beforeFilter();
		//For initial creation of the admin users uncomment the line below
		$this->Auth->allow('*');
	}
	
	public function index(){
		$this->set('programName', $this->params['program']);
	}
	
	public function add(){
	}
	
	function constructClasses() {
		parent::constructClasses();
		
		$options = array('database' => ($this->Session->read($this->params['program']."_db")));
		
		$this->ParticipantsState = new ParticipantsState($options);
	}
	
}

?>
