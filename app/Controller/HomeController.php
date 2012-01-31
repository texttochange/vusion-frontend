<?php

App::uses('AppController','Controller');
App::uses('ProgramDocument','Model');

class HomeController extends AppController {

	//var $uses = array('ProgramDocument');
	
	public function index() {
		
		//echo "Home Index -"; 
		
		$data = array(
			'truc' => array('truky')
			);
		$this->ProgramDocument->recursive = -1;
		$this->ProgramDocument->create();
		$this->ProgramDocument->save($data);
	}
	
	function constructClasses() {
		parent::constructClasses();
		//echo "Construct Home -";
		//print_r($this->Session->read($this->params['program'].'_db'));
		
		$this->ProgramDocument = new ProgramDocument(array(
			'database' => ($this->Session->read($this->params['program']."_db"))
			));
	}
	
}



?>
