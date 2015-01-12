<?php
App::uses('Component', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class TicketComponent extends Component
{

    var $components          = array('Email');
    var $EXPIRE_TICKET        = 86400; #in seconds 24h
    var $EXPIRE_INVITE_TICKET = 604800;  #in seconds 7days


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
            throw new InternalErrorException("The Ticket needs a redis instance from his controller.");
        }
    }
    
    
    public function sendEmail($userEmail, $userName, $subject, $template, $ticket)
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
            'ticket' => $ticket,
            'linkdomain' => $linkdomain,
            'userName' => $userName));
        $email->send();
    }
    
    
    protected function _getTicketKey($hash)
    {
        return $this->redisTicketPrefix.':'.$hash;
    }
    
    
    public function saveTicket($ticket, $email = null, $invite = array())
    {
        if ($email)
            $this->_checkInvitedEmailUniqueInRedis($email, $ticket);

        $ticketKey = $this->_getTicketKey($ticket);
        if ($invite != array())
            $this->redis->setex($ticketKey, $this->EXPIRE_INVITE_TICKET, json_encode($invite));
        else
            $this->redis->setex($ticketKey, $this->EXPIRE_TICKET, $ticket);
    }
    
    
    public function checkTicket($ticketHash)
    {
        $result    = null;
        $ticketKey = $this->_getTicketKey($ticketHash);
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


    protected function _checkInvitedEmailUniqueInRedis($email, $ticket)
    {
        $ticketInRedis = $this->redis->get($email);

        if ($ticketInRedis) {
            $this->redis->delete($email);

            $ticketKey = $this->_getTicketKey($ticketInRedis);
            $this->redis->delete($ticketKey);
        }
        $this->redis->setex($email, $this->EXPIRE_INVITE_TICKET, $ticket);
    }
    
    
 }
