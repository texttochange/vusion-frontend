<?php
App::uses('Component', 'Controller');
App::uses('UserLog', 'Model');
App::uses('VusionConst', 'Lib');

class UserLogMonitorComponent extends Component
{
    
    var $components = array(
        'Auth');
    
    var $eventData = null;    
    
    function beforeRender($controller)
    {
        if ($controller->_getViewVar('requestSuccess')) {
            $this->logAction();
        }
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
                    'import' => __('Imported  participant(s)'),
                    'massDelete' => __('Deleted filtered participant(s)'),
                    'runActions' => __('RunAction for participant: '),
                    ),
                'GET' => array(
                    'massTag' => __('Tagged participant(s)'),
                    'massUntag' => __('Untagged participant(s)')
                    )
                ),
            'programs' => array(
                'POST' => array(
                    'add' => __('Added a new program '),
                    'edit' => __('Edited a program'),
                    'delete' => __('Deleted a program'),
                    'archive' => __('Archived a program')
                    )
                ),
            'programDialogues' => array(
                'POST' => array(
                    'save' => __('Added a new draft dialogue'),
                    'delete' => __('Deleted a dialogue'),
                    ),
                'GET' => array(
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
                'POST' => array(
                    'save' => __('Saved a new request'),
                    'delete' => __('Deleted a request')
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
    
    
    public function logAction()
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
        
        $this->_saveUserAction($action,
            $method,
            $controller);
    }
    
    
    protected function _saveUserAction($action, $method, $controller)
    {
        $programDatabaseName = null;
        $programName         = null;
        $parametereventData  = null;
        
        $now = new DateTime('now');        
        if ($programDatabaseName == null){
            $programDatabaseName = $this->eventData['programDatabaseName'];
        }
        
        if (isset($this->Controller->programDetails['database'])) {
            $programDatabaseName = $this->Controller->programDetails['database'];
        } 
                
        if ($programName == null) {
            $programName = $this->eventData['programName'];
        }
        
        if (isset($this->Controller->programDetails['name'])) {
            $programName = $this->Controller->programDetails['name'];
        }  
        
        $programTimezone = 'UTC';        
        if (isset($this->Controller->programDetails['settings']['timezone'])) {
            $programTimezone = $this->Controller->programDetails['settings']['timezone'];
        }
        
        if (!isset($this->userLogActions[$controller][$method][$action])){
            return false;
        } 
        
        $parameterEventData = is_array($this->eventData) ? ' ' : $this->eventData;
        
        $userLog['controller'] = $controller;
        $userLog['action']     = $action;
        $userLog['parameters'] = $this->userLogActions[$controller][$method][$action].$parameterEventData;
        $userLog['user-id']    = $this->Auth->user('id');
        $userLog['user-name']  = $this->Auth->user('username');            
        $userLog['timezone']   = $programTimezone;
        $userLog['timestamp']  = $now->format(VusionConst::DATE_TIME_ISO_FORMAT);
        
        if ($programName) {
            $userLog['program-database-name'] = $programDatabaseName;
            $userLog['program-name']          = $programName;
            $this->UserLog->create('program-user-log');
        } else {
            $this->UserLog->create('vusion-user-log');
        }
        
        return $this->UserLog->save($userLog);
    }
    
    
    public function setEventData($eventData)
    { 
        $this->eventData = $eventData;
    }
    
    
}
