<?php
App::uses('AppController','Controller');
App::uses('Participant','Model');

class ParticipantsController extends AppController {
	
	public function index(){
		$this->set('programName', $this->params['program']);
	}
	
	function constructClasses() {
		parent::constructClasses();
		
		$options = array('database' => ($this->Session->read($this->params['program']."_db")));
		
		$this->Participant = new Participant($options);
	}
	
}

?>
