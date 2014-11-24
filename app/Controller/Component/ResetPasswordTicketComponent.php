<?php 
App::uses('Component', 'Controller');
App::uses('User', 'Model');
App::uses('CakeEmail', 'Network/Email');


class ResetPasswordTicketComponent extends Component
{

    var $components = array('Email');    
    
    
    public function initialize(Controller $controller)
    {
        parent::startup($controller);
        CakeEmail::transport();
        $this->Controller = $controller;
        
        
        if (isset($this->Controller->redis)) {
            $this->redis = $this->Controller->redis;
        } else { 
            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1');
        }
        
        if (isset($this->Controller->redisTicketPrefix)) {
            $this->redisTicketPrefix = $this->Controller->redisTicketPrefix;
        } else {
            $this->redisTicketPrefix = 'vusion:passwordreset';
        }
    }
    
    
    public function sendEmail($userEmail, $userName, $token)
    {  
        $linkdomain  = Configure::read('vusion.domain'); 
        $email       = new CakeEmail();
        $email->config('default');
        $email->from(array('admin@vusion.texttochange.org' => 'Vusion'));
        $email->to($userEmail);
        $email->subject('Vusion Password Reset Request');
        $email->template('reset_password_template');
        $email->emailFormat('html');
        $email->viewVars(array(
            'token' => $token,
            'linkdomain' => $linkdomain));
        $email->send();
    }
    
    
    protected function _getTicketKey($hash)
    {
        return $this->redisTicketPrefix.':'.$hash;
    }
    
    
    public function saveToken($token)
    {
        $ticketKey = $this->_getTicketKey($token);
        $this->redis->setex($ticketKey, 86400, $token);
    }
    
    
    public function checkTicket($ticketHash)
    {
        $result    = null;
        $ticketKey = $this->_getTicketKey($ticketHash);
        $ticket    = $this->redis->get($ticketKey);
        
        if (!empty($ticket)) {
            $result = $ticket;
            $this->redis->delete($ticketKey);
        } 
        
        return $result;
    }
    
    
}
