<?php
class AppController extends Controller{
	
	var $uses = array('Program', 'Group');
	
	public $components = array(
		'Session',
		'Auth' => array(
			'loginAction' => array(
				'controller' => 'users',
				'action' => 'login',
				
				),
			'loginRedirect' => array(
				'controller' => 'programs',
				'action' => 'index'
				),
			'logoutRedirect' => array(
				'controller' => 'users',
				'action' => 'login'
				),
			//'authError' => 'Authentication Failed',
			'authenticate' => array(
				'Form' => array(
					'field' => array('username' => 'username')
					)
				),
			'authorize' => array(
				'Actions' => array('actionPath' => 'controllers')
				)
			),
		'Acl',
		'Cookie');
	
	public $helpers = array('Html', 'Form', 'Session');
	
	function beforeFilter(){
	}
	
	function constructClasses(){
		parent::constructClasses();
		
		//echo "Contruct AppController -";
	
		//Verify the access of user to this program
		if (!empty($this->params['program'])) {
			//echo "Url target a program -";

			$this->Program->recursive = -1;
			$data = $this->Program->find('authorized', array(
				'specific_program_access' => $this->Group->hasSpecificProgramAccess($this->Session->read('Auth.User.group_id')),
				'user_id' => $this->Session->read('Auth.User.id'),
				'program_url' => $this->params['program']
				));
			if (count($data)==0) {
				$this->Session->setFlash(__('This program does not exists'));
				$this->redirect('/');
			} else {
				$this->Session->write( $this->params['program'] . '_db', $data[0]['Program']['database']);
				//echo "SessionValue:".$this->Session->read($this->params['program'] . '_db');
			}
		}
	}


}

?>
