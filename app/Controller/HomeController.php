<?php

App::uses('AppController','Controller');
App::uses('Script','Model');
App::uses('Participant','Model');
App::uses('ParticipantsState','Model');

class HomeController extends AppController {

	//var $uses = array('ProgramDocument');
	
	public $components = array('RequestHandler');
	public $helpers = array('Js' => array('Jquery'));
	
	public function index() {
	
		//echo "Home Index -"; 
		
		/*$data = array(
			'truc' => array('truky')
			);
		$this->ProgramDocument->recursive = -1;
		$this->ProgramDocument->create();
		$this->ProgramDocument->save($data);*/
		//$participantCount = $this->Participant->find('count');
		$this->set('programName', $this->params['program']);
		$this->set('programActive', $this->Script->find('active'));
		$this->set('programDraft', $this->Script->find('draft'));
		$this->set('participantCount', $this->Participant->find('count'));
	}
	
	function constructClasses() {
		parent::constructClasses();
		//echo "Construct Home -";
		//print_r($this->Session->read($this->params['program'].'_db'));
		
		$options = array('database' => ($this->Session->read($this->params['program']."_db")));
		
		$this->Script = new Script($options);
		$this->Participant = new Participant($options);
		$this->ParticipantsState = new ParticipantsState($options);
	}
	
}



?>
