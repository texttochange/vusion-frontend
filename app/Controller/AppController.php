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
            //'authError' => 'Authentication Failed',
            'authenticate' => array(
                'Form' => array(
                    //'field' => array('username' => 'username'),
                    'fields' => array('username' => 'email')
                    ),
                'Basic' => array(
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
        'LogManager'
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
        'CreditManager'
        );
    
    var $redis = null;
    var $redisProgramPrefix = "vusion:programs"; 
    
    
    function beforeFilter()
    {    
        //Verify the access of user to this program
        if (!empty($this->params['program'])) {
            $this->Program->recursive = -1;
            
            $data = $this->Program->find('authorized', array(
                'specific_program_access' => $this->Group->hasSpecificProgramAccess(
                    $this->Session->read('Auth.User.group_id')),
                'user_id' => $this->Session->read('Auth.User.id'),
                'program_url' => $this->params['program']
                ));
            if (count($data)==0) {
                throw new NotFoundException('Could not find this page.');
            }
            $programDetails = array(
                'name' => $data[0]['Program']['name'],
                'url' => $data[0]['Program']['url'],
                'database' => $data[0]['Program']['database']);
            $this->Session->write($programDetails['url']."_db", $programDetails['database']);
            
            $programSettingModel = new ProgramSetting(array('database' => $programDetails['database']));
            $programDetails['settings'] = $programSettingModel->getProgramSettings();
            $this->set(compact('programDetails')); 

            //In case of a Json request, no need to set up the variables
            if ($this->params['ext']=='json' or $this->params['ext']=='csv')
                return;

            $currentProgramData = $this->_getCurrentProgramData($programDetails['database']);            
            $programLogsUpdates = $this->LogManager->getLogs($programDetails['database'], 5);      
            $creditStatus = $this->CreditManager->getOverview($programDetails['database']);
            $this->set(compact('currentProgramData', 'programLogsUpdates', 'creditStatus')); 
        }
        $countryIndexedByPrefix = $this->PhoneNumber->getCountriesByPrefixes();
        $this->set(compact('countryIndexedByPrefix'));
    }
    
    
    function constructClasses()
    {
        parent::constructClasses();
        
        $this->redis = new Redis();
        $redisConfig = Configure::read('vusion.redis');
        $redisHost = (isset($redisConfig['host']) ? $redisConfig['host'] : '127.0.0.1');
        $redisPort = (isset($redisConfig['port']) ? $redisConfig['port'] : '6379');
        $this->redis->connect($redisHost, $redisPort);

        $redisPrefix = Configure::read('vusion.redisPrefix');
        if (is_array($redisPrefix)) { 
            $this->redisProgramPrefix = $redisPrefix['base'] . ':' . $redisPrefix['programs'];
        }
    }
    
    
    protected function _getcurrentProgramData($databaseName)
    {
        $unattachedMessageModel = new UnattachedMessage(array('database' => $databaseName));
        $unattachedMessages = $unattachedMessageModel->find('future');
        if (isset($unattachedMessages))
            $programUnattachedMessages = $unattachedMessages;
        else
        $programUnattachedMessages = null;
        
        $predefinedMessageModel = new PredefinedMessage(array('database' => $databaseName));
        $predefinedMessages = $predefinedMessageModel->find('all');
        if (isset($predefinedMessages))
            $programPredefinedMessages = $predefinedMessages;
        else
        $programPredefinedMessages = null;
        
        $dialogueModel = new Dialogue(array('database' => $databaseName));
        $dialogues = $dialogueModel->getActiveAndDraft();
        $requestModel = new Request(array('database' => $databaseName));
        $requests = $requestModel->find('all');
        
        $currentProgramData = array(
            'unattachedMessages' => $programUnattachedMessages,
            'predefinedMessages' => $programPredefinedMessages,
            'dialogues' => $dialogues,
            'requests' => $requests
            );
        
        return $currentProgramData;
    }
    
    
}
