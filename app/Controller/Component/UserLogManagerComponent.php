<?php
App::uses('Component', 'Controller');
App::uses('UserLog', 'Model');
App::uses('ProgramSetting', 'Model');


class UserLogManagerComponent extends Component
{
    
	var $components = array('Session');
	
	
	public function initialize($controller)
	{
	    $this->Controller = $controller;
	    
	    $this->userLogActions  = array(
	        'default' => array(
				'POST' => array(
					'add' => __('Adding this is not allowed within an archived program.'),
					'edit' => __('Editing this is not allowed within archived program.'),
					'delete' => __('Deleting this is not allowed within archived program.'))
				),
	        'programParticipants' => array(
	            'POST' => array(
	                'delete' => __('delete participants'))
	            ));
	    
	}
	
	
	public function logAction()
	{
	    $this->UserLog = new UserLog();
	    $now           = new DateTime('now');
	    // $this->ProgramSetting = new ProgramSetting($options);
	    
		$controller = 'default';
		if (isset($this->userLogActions[$this->Controller->request->params['controller']])){
			$controller = $this->Controller->request->params['controller'];
		} 
		$method = $this->Controller->request->method();
		
		$action = 'index';
		if (isset($this->Controller->request->action)) {
			$action = $this->Controller->request->action;
		}
		if (!isset($this->userLogActions[$controller][$method][$action])){
			return true;
		} else {
		    $userLog['controller']            = $controller;
	        $userLog['action']                = $action;
		    $userLog['program-database-name'] = $this->Controller->programDetails['database'];
		    $userLog['program-name']          = $this->Controller->programDetails['name'];
		    $userLog['parameters']            = $this->userLogActions[$controller][$method][$action];
		    $userLog['user-id']               = $this->Session->read('Auth.User.id');
		    $userLog['user-name']             = $this->Session->read('Auth.User.username');
		    $userLog['timestamp']             = $now->format("d/m/Y H:i:s");
		    $userLog['timezone']              = 'EXT';		    
		    
		    $this->UserLog->create();
		    $this->UserLog->save($userLog);
		    
		    $this->Session->setFlash($userLog['controller']);
			
			
		}
		return true;
		
	}
	
}
