<?php

App::uses('AppController','Controller');
App::uses('Script','Model');
App::uses('Participant','Model');
App::uses('ParticipantsState','Model');
App::uses('VumiSupervisord','Lib');


class HomeController extends AppController {

	//var $uses = array('ProgramDocument');
	
	public $components = array('RequestHandler');
	public $helpers = array('Js' => array('Jquery'));
	
	public function index() {
	
		//echo "Home Index -"; 
		
		//$participantCount = $this->Participant->find('count');
		$this->set('programName', $this->params['program']);
		$this->set('programActive', $this->Script->find('active'));
		$this->set('programDraft', $this->Script->find('draft'));
		$this->set('participantCount', $this->Participant->find('count'));
	
		//get the lib
		//require_once('Lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
		//$f=new xmlrpcmsg('supervisor.getState');
		//print_r($this->VumiSupervisord->getAllProcessInfo());
	}
	
	function constructClasses() {
		parent::constructClasses();
		//echo "Construct Home -";
		//print_r($this->Session->read($this->params['program'].'_db'));
		
		$options = array('database' => ($this->Session->read($this->params['program']."_db")));
		
		$this->Script = new Script($options);
		$this->Participant = new Participant($options);
		$this->ParticipantsState = new ParticipantsState($options);

		$this->VumiSupervisord = new VumiSupervisord();

	}
	
}



?>
