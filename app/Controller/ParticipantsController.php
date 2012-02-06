<?php
App::uses('AppController','Controller');
App::uses('Participant','Model');

class ParticipantsController extends AppController {
	

	function constructClasses() {
		parent::constructClasses();
		
		$options = array('database' => ($this->Session->read($this->params['program']."_db")));
		
		$this->Participant = new Participant($options);
	}

	function beforeFilter() {
		parent::beforeFilter();
	}
	
	public function index() {
		$programName = $this->Session->read($this->params['program'].'_name');
		$programUrl = $this->params['program'];
		$participants = $this->paginate();
		$this->set(compact('programName', 'programUrl', 'participants'));		
	}
	
	public function add() {	
		if ($this->request->is('post')) {
			$this->Participant->create();
			if ($this->Participant->save($this->request->data)) {
				$this->Session->setFlash(__('The participant has been saved.'));
				$this->redirect(array(
					'program' => $this->params['program'],  
					'controller' => 'participants',
					'action' => 'index'
					));
			} else {
				$this->Session->setFlash(__('The participant could not be saved.'));
			}
		}
		$programName = $this->Session->read($this->params['program'].'_name');
		$programUrl = $this->params['program'];
		$this->set(compact('programName', 'programUrl'));		
	}
	
	public function edit() {
	}
	
	public function delete () {
	}
	
	public function view() {
	}
	

	
}

?>
