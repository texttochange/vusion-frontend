<?php
App::uses('Component', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class TicketComponent extends Component
{

    var $components          = array('Email');    
    var $REDIS_TICKETS       = 'vusion:ticket';
    var $EXPIRE_TOKEN        = 86400; #in seconds 24h
    var $EXPIRE_INVITE_TOKEN = 604800;  #in seconds 7days


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
            $this->redisTicketPrefix = $this->REDIS_TICKETS;
        }
    }
    
    
    public function sendEmail($userEmail, $userName, $subject, $template, $token)
    {  
        $linkdomain  = Configure::read('vusion.domain');
        $email       = new CakeEmail();
        $email->config('default');
        $email->from(array(Configure::read('vusion.email') => 'Vusion'));
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
    
    
    protected function _getTokenKey($hash)
    {
        return $this->redisTicketPrefix.':'.$hash;
    }
    
    
    public function saveToken($token)
    {
        $ticketKey = $this->_getTokenKey($token);
        $this->redis->setex($ticketKey, $this->EXPIRE_TOKEN, $token);
    }


    public function saveInvitedToken($email, $token, $invite)
    {
        $this->_checkInvitedEmailUniqueInRedis($email, $token);

        $ticketKey = $this->_getTokenKey($token);
        $this->redis->setex($ticketKey, $this->EXPIRE_INVITE_TOKEN, json_encode($invite));
    }
    
    
    public function checkTicket($ticketHash)
    {
        $result    = null;
        $ticketKey = $this->_getTokenKey($ticketHash);
        $ticket    = $this->redis->get($ticketKey);       

        if (empty($ticket)) {
            return $result;
        }

        # Ticket is used only once
        $this->redis->delete($ticketKey);
        
        $result = $ticket;
        if (is_array(json_decode($result, true))) {
            return json_decode($result, true);
        }
        return $result;
    }


    protected function _checkInvitedEmailUniqueInRedis($email, $token)
    {
        $ticket = $this->redis->get($email);

        if ($ticket) {
            $this->redis->delete($email);

            $ticketKey = $this->_getTokenKey($ticket);
            $this->redis->delete($ticketKey);
        }
        $this->redis->setex($email, $this->EXPIRE_INVITE_TOKEN, $token);
    }
    
    
 }
