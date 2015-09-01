<?php
App::uses('Controller', 'Controller');
App::uses('ProgramSetting', 'Model');
App::uses('UnattachedMessage', 'Model');
App::uses('Dialogue', 'Model');
App::uses('Request', 'Model');
App::uses('PredefinedMessage', 'Model');

class AppController extends Controller
{
    
    var $uses = array(
        'Program',
        'Group');
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
        'BackendLog',
        'Stats',
        'CreditManager',
        'UserLogMonitor',
        'Country');
    
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
        'Documentation');
    var $redis              = null;
    var $redisProgramPrefix = "vusion:programs";
    var $redisTicketPrefix  = "vusion:tickets"; 
    var $redisExportPrefix  = "vusion:exports"; 


    function beforeFilter()
    {
        if ($this->_isAjax() || $this->_isCsv()) {
            $this->Auth->unauthorizedRedirect = false;
            //Hack to allow passing the basic authentication as a parameter of the url
            if (isset($this->request->query['auth'])) {
                $basicAuth = explode(':', base64_decode($this->request->query['auth']));
                if (isset($basicAuth[1])) {
                    apache_setenv("PHP_AUTH_USER", $basicAuth[0]);
                    apache_setenv("PHP_AUTH_PW", $basicAuth[1]);
                }
            }
        }
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
    
    
    public function _isAjax()
    {
        return ($this->request->is('ajax') ||  $this->request->ext == 'json');
    }
    
    
    public function _isCsv()
    {
        return ($this->request->ext === 'csv');
    }
    
    
    public function beforeRender()
    {
        if (($this->_isAjax() ||$this->_isCsv()) && $this->response->statusCode() != 200) {
            return false;
        }
        if ($this->_isAjax()) {
            $this->RequestHandler->renderAs($this, 'json');
            $this->layout = 'default';
        } else {
            //TODO this hase to move in the relevant contollers
            $countryIndexedByPrefix = $this->Country->getNamesByPrefixes();
            $this->set(compact('countryIndexedByPrefix'));
        }
    }
    
    
    public function beforeRedirect($url, $status = null, $exit = true) 
    {
        if($this->_isAjax() || $this->_isCsv()) {
            return false;
        }
        return true;
    }
    
    
    public function _getViewVar($name)
    {
        if (isset($this->viewVars[$name])) {
            return $this->viewVars[$name];
        }
        return;
    }
    
}
