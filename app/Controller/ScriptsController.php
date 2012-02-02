<?php
App::uses('AppController','Controller');
App::uses('Script','Model');

class ScriptsController extends AppController {
	
	var $components = array('RequestHandler');
	public $helpers = array('Js' => array('Jquery'));
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('*');
	}
	
	public function index(){
		$this->set('programName', $this->params['program']);	
	}
	
	public function add(){
		//if ($this->request->is('post')) {
			$this->Script->create();
			if ($this->Script->save($this->request->data)) {
				$this->set('result', array('status' => '1'));
			} else {
				$this->set('result', array('status' => '0'));
			}
		//}
	}
	
	
	function constructClasses() {
		parent::constructClasses();
		
		$options = array('database' => ($this->Session->read($this->params['program']."_db")));
		
		$this->Script = new Script($options);
	}

	
}

?>
