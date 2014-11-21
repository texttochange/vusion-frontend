<?php
App::uses('Controller', 'Controller');
App::uses('ProgramSetting', 'Model');
App::uses('UnattachedMessage', 'Model');
App::uses('Dialogue', 'Model');
App::uses('Request', 'Model');
App::uses('PredefinedMessage', 'Model');

class AppController extends Controller
{
    
    var $uses = array('Program', 'Group');
    
    var $components = array(
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
            'authenticate' => array(
                'Basic' => array(
                    'fields' => array('username' => 'email')
                    ),
                'Form' => array(
                    'fields' => array('username' => 'email')
                    )
                ),
            'authorize' => array(
                'Actions' => array('actionPath' => 'controllers')
                )
            ),
        'Acl',
        'Cookie', 
        'PhoneNumber',
        'CreditManager',
        'BackendLog',
        'Stats',
        'ArchivedProgram',
        'UserLogMonitor'
        );
    
    var $helpers = array(
        'PhoneNumber',
        'Html',
        'Form',
        'Session',
        'Js',
        'Time',
        'AclLink',
        'Text',
        'BigNumber',
        'CreditManager',
        'Documentation'
        );
    
    var $redis              = null;
    var $redisProgramPrefix = "vusion:programs"; 
    var $programDetails     = array();
    
    
    function beforeFilter()
    {
        //Verify the access of user to this program
        if ($this->Auth->loggedIn() && !empty($this->params['program'])) {
            $this->Program->recursive = -1;
            
            $data = $this->Program->find('authorized', array(
                'specific_program_access' => $this->Group->hasSpecificProgramAccess(
                    $this->Auth->user('group_id')),
                'user_id' => $this->Auth->user('id'),
                'program_url' => $this->params['program']
                ));
            if (count($data)==0) {
                throw new NotFoundException('Could not find this page.');
            }
            
            $programDetails = array();
            foreach (array('name', 'url', 'database', 'status') as $key) {
                $programDetails[$key] = $data[0]['Program'][$key];
            }
            
            $this->Session->write($programDetails['url']."_db", $programDetails['database']);
            $this->Session->write($programDetails['url']."_name", $programDetails['name']);
            
            $programSettingModel = new ProgramSetting(array('database' => $programDetails['database']));
            $programDetails['settings'] = $programSettingModel->getProgramSettings();
            $this->programDetails = $programDetails;
            $this->set(compact('programDetails'));
            
            if (!$this->ArchivedProgram->isAllowed()) {
                $this->_stop();
            }

            //In case of a Json request, no need to set up the variables
            if ($this->_isAjax() || $this->params['ext']=='csv') {
                return;
            }

            $currentProgramData = $this->_getCurrentProgramData($programDetails['database']);            
            $programLogsUpdates = $this->BackendLog->getLogs($programDetails['database'], 5);
            $programStats       = array('programStats' => $this->Stats->getProgramStats($programDetails['database'], true));
            $creditStatus       = $this->CreditManager->getOverview($programDetails['database']);
            $this->set(compact('currentProgramData', 'programLogsUpdates', 'programStats', 'creditStatus')); 
        }
        $this->UserLogMonitor->logAction();
        $countryIndexedByPrefix = $this->PhoneNumber->getCountriesByPrefixes();
        $this->set(compact('countryIndexedByPrefix'));
    }
    
    
    function constructClasses()
    {
        parent::constructClasses();
        
        $this->redis = new Redis();
        $redisConfig = Configure::read('vusion.redis');
        $redisHost   = (isset($redisConfig['host']) ? $redisConfig['host'] : '127.0.0.1');
        $redisPort   = (isset($redisConfig['port']) ? $redisConfig['port'] : '6379');
        $this->redis->connect($redisHost, $redisPort);
        $redisPrefix = Configure::read('vusion.redisPrefix');
        if (is_array($redisPrefix)) { 
            $this->redisProgramPrefix = $redisPrefix['base'] . ':' . $redisPrefix['programs'];
        }
    }
    
    
    protected function _getcurrentProgramData($databaseName)
    {
        $unattachedMessageModel = new UnattachedMessage(array('database' => $databaseName));
        $predefinedMessageModel = new PredefinedMessage(array('database' => $databaseName));        
        $dialogueModel          = new Dialogue(array('database' => $databaseName));
        $requestModel           = new Request(array('database' => $databaseName));
        
        $currentProgramData = array(
            'unattachedMessages' => array(
                'scheduled' => $unattachedMessageModel->find('scheduled'),
                'drafted' => $unattachedMessageModel->find('drafted')),
            'predefinedMessages' => $predefinedMessageModel->find('all'),
            'dialogues' => $dialogueModel->getActiveAndDraft(),
            'requests' => $requestModel->find('all'),
            );
        return $currentProgramData;
    }
    
    
    protected function _isAjax() 
    {
        return ($this->request->is('ajax') ||  $this->request->ext == 'json');
    }
    
    public function beforeRender(){
        if ($this->_isAjax() && $this->response->statusCode() == 403) {
            return false;
        }
        if ($this->_isAjax()) {
            $this->RequestHandler->renderAs($this, 'json');
            $this->layout = 'default';
        }
    }

    public function beforeRedirect($url, $status = null, $exit = true) {
        // this statement catches not redirect to login for ajax requests
        if($this->_isAjax() && $url == array('controller' => 'users', 'action' => 'login')) {
            throw new ForbiddenException();
        }
        return true;
    }
    
}
