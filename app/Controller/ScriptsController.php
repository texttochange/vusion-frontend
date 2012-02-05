<?php
App::uses('AppController','Controller');
App::uses('Script','Model');

class ScriptsController extends AppController {
	
	var $components = array('RequestHandler', 'Acl');
	var $helpers = array('Js' => array('Jquery'));
	
	public function beforeFilter() {
		parent::beforeFilter();
		//$this->Auth->allow('*');
		$this->RequestHandler->accepts('json');
		$this->RequestHandler->addInputType('json', array('json_decode'));
	}
	
	public function index(){
		$this->set('programName', $this->params['program']);	
	}
	
	/*
	public function add(){
		//print_r($this->request->data);
		if ($this->request->is('post')) {
			
			$this->Script->create();
			if ($this->Script->save($this->request->data)) {
				$this->set('result', array('status' => '1'));
			} else {
				$this->set('result', array('status' => '0'));
			}
		}
	}
	*/
	
	/** TODO: move this logic into the Model 
	* But when doing so the update didn't work due to duplicat entry on primary key.
	*/
	public function add(){
		//print_r($this->request->data);
		if ($this->request->is('post')) {
			$draft = $this->Script->find('draft');
			//print_r($draft);
			if ($draft) {	
				//echo 'Draft case';
				//$this->Script->mongoNoSetOperator = true;
				$this->Script->create();
				$this->Script->id = $draft[0]['Script']['_id'];
				$saveData['Script'] = get_object_vars($this->request->data);
				//echo 'type:'.gettype($saveData);
				$saveData['Script']['_id'] = $draft[0]['Script']['_id'];
				//print_r($saveData);
				$this->Script->save($saveData);
				$this->set('result', array(
						'status' => '1',
						'id' => $this->Script->id
						));
			} else {
				//echo 'save case';
				$this->Script->create();
				$saveData['Script'] = get_object_vars($this->request->data);
				//print_r($saveData);
				if ($this->Script->save($saveData)) {
					$this->set('result', array(
						'status' => '1',
						'id' => $this->Script->id
						));
				} else {
					$this->set('result', array('status' => '0'));
				}
			}
		}
	}
	
	
	function constructClasses() {
		parent::constructClasses();
		
		$options = array('database' => ($this->Session->read($this->params['program']."_db")));
		
		$this->Script = new Script($options);
	}

	
}

?>
