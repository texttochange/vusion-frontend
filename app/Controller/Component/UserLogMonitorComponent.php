<?php
App::uses('Component', 'Controller');
App::uses('UserLog', 'Model');
App::uses('VusionConst', 'Lib');

class UserLogMonitorComponent extends Component
{
    
    var $components = array(
        'Session',
        'Auth');
    
    
    function beforeRender($controller)
    {
        $this->logAction();
    }
    
    
    function beforeRedirect($controller)
    {
        $this->logAction();
    }
    
    
    public function initialize($controller)
    {
        $this->Controller = $controller;        
        $this->UserLog    = ClassRegistry::init('UserLog');
        
        $this->userLogActions  = array(
            'programParticipants' => array(
                'POST' => array(
                    'delete' => __('Deleted participant(s)'),
                    'add' => __('Added a new participant'),
                    'edit' => __('Edited participant'),
                    'import' => __('Imported  participant(s)')
                    ),
                'GET' => array(
                    'massTag' => __('Tagged participant(s)'),
                    'massUntag' => __('Untagged participant(s)')
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
                    'save' => __('Added a new draft dialogue'),
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
                    'delete' => __('Deleted a request'),
                    'edit' => __('Edited a request')
                    )
                ),
            'programHistory' => array(
                'POST' => array(
                    'delete' => __('Deleted program history')
                    )
                ),
            'programSettings' => array(
                'POST' => array(
                    'edit' => __('Edited a program setting')
                    )
                ),
            'users' => array(
                'POST' => array(
                    'add' => __('Added new a user'),
                    'edit' => __('Edited a user'),
                    'delete' => __('Deleted a user'),
                    'changePassword' => __('Changed password')
                    )
                )
            );        
    }
    
    
    public function userLogSessionWrite($programDatabaseName = null, $programName = null)
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
            'controller' => $controller,
            'programDatabaseName' => $programDatabaseName,
            'programName' => $programName));
    }
    
    
    public function logAction()
    {
        if ($this->Session->check('UserLogMonitor')) {
            $sessionAction = $this->Session->read('UserLogMonitor');
            $this->_logAction($sessionAction['action'],
                $sessionAction['method'],
                $sessionAction['controller'],
                $sessionAction['programDatabaseName'],
                $sessionAction['programName']);
            $this->Session->delete('UserLogMonitor');
        }
    }
    
    
    protected function _logAction($action, $method, $controller, $programDatabaseName = null, $programName = null)
    {
        $now = new DateTime('now');
        if (isset($this->Controller->programDetails['database'])) {
            $programDatabaseName = $this->Controller->programDetails['database'];
        } 
        
        if (isset($this->Controller->programDetails['name'])) {
            $programName = $this->Controller->programDetails['name'];
        }
        
        $programTimezone = 'UTC';        
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
            $userLog['user-id']               = $this->Auth->user('id');
            $userLog['user-name']             = $this->Auth->user('username');            
            $userLog['timezone']              = $programTimezone;
            $userLog['timestamp']             = $now->format(VusionConst::DATE_TIME_ISO_FORMAT);
            $this->UserLog->create();
            $this->UserLog->save($userLog);
        }
        return true;
        
    }
    
    
    public function userLogRequestEditSessionWrite($programDatabaseName = null, $programName = null)
    {        
        $controller = $this->Controller->request->params['controller'];
        
        $this->Session->write('UserLogMonitor', array(
            'action' => 'edit',
            'method' => 'POST',
            'controller' => $controller,
            'programDatabaseName' => $programDatabaseName,
            'programName' => $programName));
    }
    
}
