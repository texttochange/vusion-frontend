<?php
App::uses('Component', 'Controller');
App::uses('UserLog', 'Model');


class UserLogMonitorComponent extends Component
{
    
	var $components = array('Session');
	
	
	public function initialize($controller)
	{
	    $this->Controller = $controller;
	    if (!Configure::read("mongo_db")) {
            $options = array(
                'database' => 'vusion'
                );
        } else {
            $options = array(
                'database' => Configure::read("mongo_db")
                );
        }
        
        $this->UserLog = new UserLog($options);
        
	    $this->userLogActions  = array(
	        'default' => array(
				'POST' => array(
					'add' => __('Adding to unlisted action in  program.'),
					'edit' => __('Editing to unlisted action in program.'),
					'delete' => __('Deleting to unlisted action in program.'))
				),
	        'programParticipants' => array(
	            'POST' => array(
	                'delete' => __('Deleted participant(s)'),
	                'add' => __('Added a new participant'),
	                'edit' => __('Edited participant'),
	                'import' => __('Imported new participant(s)')
	                ),
	            'GET' => array(
	                'massTag' => __('MassTaged participant(s)'),
	                'massUntag' => __('MassUntaged participant(s)')
	                )
	            ),
	        'programs' => array(
	            'POST' => array(
	                'add' => __('Added a new program'),
	                'edit' => __('Edited a program'),
	                'delete' => __('Deleted a program'),
	                'archive' => __('Archived a program')
	                )
	            ),
	        'programDialogues' => array(
	            'POST' => array(
	                'save' => __('Added new a dialogue'),
	                'delete' => __('Deleted a dialogue'),
	                'activate' => __('Activated a dialogue')
	                )
	            ),
	        'programUnattachedMessages' => array(
	            'POST' => array(
	                'add' => __('Added a new separate message'),
	                'edit' => __('Edited a separate message'),
	                'delete' => __('Deleted a separate message')
	                )
	            ),
	        'programRequests' => array(
	            'POST' => array (
	                'save' => __('Added a new request'),
	                'delete' => __('Deleted a request')
	                ),
	            'GET' => array(
	                'edit' => __('Edited a request')
	                )
	            ),
	        'programHistory' => array(
	            'POSt' => array(
	                'delete' => __('Deleted program History')
	                )
	            ),
	        'programSetting' => array(
	            'POST' => array(
	                'edit' => __('Edited program settings')
	                )
	            )
	        );
	    
	}
	
	
	public function userLogSessionWrite($userLogMonitor, $action, $method, $controller)
	{
	    $this->Session->write($userLogMonitor, array(
	        'action' => $action,
	        'method' => $method,
	        'controller' => $controller));
	}
	
	
	public function logAction()
	{
	    if ($this->Session->check('UserLogMonitor')) {
            $sessionAction = $this->Session->read('UserLogMonitor');
            $this->_logAction($sessionAction['action'], $sessionAction['method']);
            $this->Session->delete('UserLogMonitor');
        }
        
	}
		
	
	protected function _logAction($sessionAction, $method)
	{	    
	    $now = new DateTime('now');	    
	    
		$controller = 'default';
		if (isset($this->userLogActions[$this->Controller->request->params['controller']])){
			$controller = $this->Controller->request->params['controller'];
		}
		
		$action = 'index';
		if (isset($this->Controller->request->action)) {
			$action = $sessionAction;
		}
		
		$programDatabaseName = 'None';
		if (isset($this->Controller->programDetails['database'])) {
		    $programDatabaseName = $this->Controller->programDetails['database'];
		}
		
		$programName = 'None'; 
		if (isset($this->Controller->programDetails['name'])) {
		    $programName = $this->Controller->programDetails['name'];
		}
		
		$programTimezone = 'None';
		if (isset($this->Controller->programDetails['settings']['timezone'])) {
		    $programTimezone = $this->Controller->programDetails['settings']['timezone'];
		}
		
		if (!isset($this->userLogActions[$controller][$method][$action])){
			return true;
		} else {
		    $userLog['controller']            = $controller;
	        $userLog['action']                = $action;
		    $userLog['program-database-name'] = $programDatabaseName;
		    $userLog['program-name']          = $programName;
		    $userLog['parameters']            = $this->userLogActions[$controller][$method][$action];
		    $userLog['user-id']               = $this->Session->read('Auth.User.id');
		    $userLog['user-name']             = $this->Session->read('Auth.User.username');
		    $userLog['timestamp']             = $now->format("d/m/Y H:i:s");
		    $userLog['timezone']              = $programTimezone;
		    
		    $this->UserLog->create();
		    $this->UserLog->save($userLog);
		}
		return true;
		
	}
	
}
