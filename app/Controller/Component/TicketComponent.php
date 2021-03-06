<?php
App::uses('Component', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class TicketComponent extends Component
{
    
    var $components          = array('Email', 'Redis');
    var $EXPIRE_TICKET        = 86400; #in seconds 24h
    var $EXPIRE_INVITE_TICKET = 604800;  #in seconds 7days
    
    
    public function initialize(Controller $controller)
    {
        /*$this->Controller = $controller;
        
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
        }*/
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
        return $this->Redis->getTicketPrefix($hash);
    }
    
    
    public function saveTicket($ticket, $email = null, $invite = array())
    {
        $redis = $this->Redis->redisConnect();
        if ($email) {
            $this->_checkInvitedEmailUniqueInRedis($email, $ticket);
        }
        
        $ticketKey = $this->_getTicketKey($ticket);
        if ($invite != array()) {
            $redis->setex($ticketKey, $this->EXPIRE_INVITE_TICKET, json_encode($invite));
        } else {
            $redis->setex($ticketKey, $this->EXPIRE_TICKET, $ticket);
        }
    }
    
    
    public function checkTicket($ticketHash)
    {
        $redis = $this->Redis->redisConnect();
        $result    = null;
        $ticketKey = $this->_getTicketKey($ticketHash);
        $ticket    = $redis->get($ticketKey);       
        
        if (empty($ticket)) {
            return $result;
        }
        
        # Ticket is used only once
        $redis->delete($ticketKey);
        
        $result = $ticket;
        if (is_array(json_decode($result, true))) {
            return json_decode($result, true);
        }
        return $result;
    }
    
    
    protected function _checkInvitedEmailUniqueInRedis($email, $ticket)
    {
        $redis = $this->Redis->redisConnect();
        $ticketInRedis = $redis->get($email);
        
        if ($ticketInRedis) {
            $redis->delete($email);
            
            $ticketKey = $this->_getTicketKey($ticketInRedis);
            $redis->delete($ticketKey);
        }
        $redis->setex($email, $this->EXPIRE_INVITE_TICKET, $ticket);
    }
    
    
}
