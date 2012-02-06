<?php

App::uses('AppController','Controller');
App::uses('Script','Model');
App::uses('Participant','Model');
App::uses('ParticipantsState','Model');
App::uses('VumiSupervisord','Lib');


class HomeController extends AppController {

	//var $uses = array('ProgramDocument');
	
	var $components = array('RequestHandler');
	var $helpers = array('Js' => array('Jquery'));
	
	public function index() {
	
		//echo "Home Index -"; 
		
		//$participantCount = $this->Participant->find('count');
		$programName = $this->Session->read($this->params['program'].'_name');
		
		$hasScriptActive = count($this->Script->find('countActive'));
		$hasScriptDraft = count($this->Script->find('countDraft'));
		
		$isScriptEdit = $this->Acl->check(array(
				'User' => array(
					'id' => $this->Session->read('Auth.User.id')
				),
			), 'controllers/Scripts');
		
		$isParticipantAdd = $this->Acl->check(array(
				'User' => array(
					'id' => $this->Session->read('Auth.User.id')
				),
			), 'controllers/Participants/add');
		
		$participantCount = $this->Participant->find('count');
	
		$this->set(compact('programName',
			'hasScriptActive', 
			'hasScriptDraft',
			'isScriptEdit', 
			'isParticipantAdd', 
			'participantCount'));
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
