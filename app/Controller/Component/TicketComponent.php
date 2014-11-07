<?php
App::uses('Component', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class TicketComponent extends Component
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
            $this->redisTicketPrefix = 'vusion:ticket';
        }
    }
    
    
    public function sendEmail($userEmail, $userName, $subject, $template, $token)
    {  
        //$linkdomain  = Configure::read('vusion.domain');
        $linkdomain  = 'localhost:4567'; 
        $email       = new CakeEmail();
        $email->config('default');
        $email->from(array('admin@vusion.texttochange.org' => 'Vusion'));
        $email->to($userEmail);
        $email->subject('Vusion '. $subject .' Request');
        $email->template($template);
        $email->emailFormat('html');
        $email->viewVars(array(
            'token' => $token,
            'linkdomain' => $linkdomain,
            'userName' => $userName));
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


    public function saveInvitedToken($email, $token, $invite)
    {
        $this->_checkInvitedEmailUniqueInRedis($email, $token);

        $ticketKey = $this->_getTicketKey($token);
        $this->redis->setex($ticketKey, 604800, json_encode($invite));
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
        
        if (is_array(json_decode($result, true))) {
            # adding the option true ensures that an array is returned.
            return json_decode($result, true);
        }
        
        return $result;
    }


    protected function _checkInvitedEmailUniqueInRedis($email, $token)
    {
        $ticket = $this->redis->get($email);

        if ($ticket) {
            $this->redis->delete($email);

            $ticketKey = $this->_getTicketKey($ticket);
            $this->redis->delete($ticketKey);
        }
        $this->redis->setex($email, 604800, $token);
    }
    
    
 }