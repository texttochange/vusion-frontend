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
        'PhoneNumber',
        'BackendLog',
        'Stats',
        'CreditManager',
        'UserLogMonitor');
    
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
    
    
    function beforeFilter()
    {
        if ($this->_isAjax()) {
            $this->Auth->unauthorizedRedirect = false;
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
    
    
    public function beforeRender(){
        if ($this->_isAjax() && $this->response->statusCode() != 200) {
            return false;
        }
        if ($this->_isAjax()) {
            $this->RequestHandler->renderAs($this, 'json');
            $this->layout = 'default';
        } else {
            //TODO this hase to move in the relevant contollers
            $countryIndexedByPrefix = $this->PhoneNumber->getCountriesByPrefixes();
            $this->set(compact('countryIndexedByPrefix'));
        }
    }
    
    
    public function beforeRedirect($url, $status = null, $exit = true) 
    {
        if($this->_isAjax()) {
            return false;
        }
        return true;
    }
    
    
    public function getViewVar($name)
    {
        if (isset($this->viewVars[$name])) {
            return $this->viewVars[$name];
        }
        return;
    }
    
}
