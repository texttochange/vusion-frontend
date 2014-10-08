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
	                'add' => __('Added anew program'),
	                'edit' => __('Edited aprogram'),
	                'delete' => __('Deleted aprogram'),
	                'archive' => __('Archived aprogram')
	                )
	            ),
	        'programDialogues' => array(
	            'POST' => array(
	                'save' => __('Added anew draft dialogue'),
	                'delete' => __('Deleted a dialogue'),
	                'activate' => __('Activated a dialogue')
	                )
	            ),
	        'programUnattachedMessages' => array(
	            'POST' => array(
	                'add' => __('Added anew separate message'),
	                'edit' => __('Edited aseparate message'),
	                'delete' => __('Deleted aseparate message')
	                )
	            ),
	        'programRequests' => array(
	            'POST' => array (
	                'save' => __('Added anew request'),
	                'delete' => __('Deleted arequest')
	                )
	            ),
	        'programHistory' => array(
	            'POST' => array(
	                'delete' => __('Deleted program History')
	                )
	            ),
	        'programSettings' => array(
	            'POST' => array(
	                'edit' => __('Edited program settings')
	                )
	            ),
	        'users' => array(
	            'POST' => array(
	                'add' => __('Added new User'),
	                'edit' => __('Edited User'),
	                'delete' => __('Deleted User'),
	                'changePassword' => __('Changed Password')
	                )
	            )
	        );
	    
	}
	
	
	public function userLogSessionWrite()
	{
	    $controller = 'default';
		if (isset($this->userLogActions[$this->Controller->request->params['controller']])){
			$controller = $this->Controller->request->params['controller'];
		}
	    
	    $action = 'index';
		if (isset($this->Controller->request->action)) {
			$action = $this->Controller->request->action;
		}
		
		$method = $this->Controller->request->method();
	    
	    $this->Session->write('UserLogMonitor', array(
	        'action' => $action,
	        'method' => $method,
	        'controller' => $controller));
	}
	
	
	public function logAction()
	{
	    if ($this->Session->check('UserLogMonitor')) {
            $sessionAction = $this->Session->read('UserLogMonitor');
            print_r($sessionAction);
            $this->_logAction($sessionAction['action'], $sessionAction['method'], $sessionAction['controller']);
            $this->Session->delete('UserLogMonitor');
        }
	}
	
	
	protected function _logAction($action, $method, $controller)
	{
	    $now = new DateTime('now');
	    
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
		    print_r('....................');
		    print_r($userLog);
		    $this->UserLog->create();
		    $this->UserLog->save($userLog);
		}
		return true;
		
	}
	
}
