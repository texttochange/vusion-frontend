<?php 
App::uses('Component', 'Controller');
App::uses('User', 'Model');
App::uses('CakeEmail', 'Network/Email');


class TicketComponent extends Component
{
    var $components = array('Email');
    var $sitename   = 'http://vusion.texttochange.org'; 
    var $linkdomain = 'vusion.texttochange.org';
    //var $linkdomain = '192.168.0.160:81';
   
    
    
    public function initialize(Controller $controller)
    {
        parent::startup($controller);
        CakeEmail::transport();
        $this->Controller = $controller;
        
        
        if(isset($this->Controller->redis)){
            $this->redis = $this->Controller->redis;
        }else{ 
            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1');
        }
        
        if(isset($this->Controller->redisTicketPrefix)){
            $this->redisTicketPrefix = $this->Controller->redisTicketPrefix;
        }else{
            $this->redisTicketPrefix = 'vusion:passwordreset';
        }
    }


	public function createMessage($token)
	{   
		$ms  = '<html><head><title>Password Reset Request</title></head>';
		$ms .= '<body>Your email has been used in a password reset request at '.$this->sitename.'<br/>';
		$ms .= 'If you did not initiate this request, then ignore this message.<br/>';
		$ms .= '  Click the link below into your browser to reset your password.<br/>';
		$ms .= '<a href="http://'.$this->linkdomain.'/users/useTicket/'.$token.'">Reset Password</a>';
		$ms .= '</body></html>';
		$ms  = wordwrap($ms,70);
		
		return $ms;
	}
 
	
	public function sendEmail($userEmail, $userName, $message)
	{  
	    $email = new CakeEmail();
	    $email->config('default');
	    $email->from(array('admin@vusion.texttochange.org' => 'vusion.com'));
	    $email->to($userEmail);
	    $email->subject('Message from '.$this->sitename.' for '.$userName);
	    $email->send($message);
	}
	
	
	protected function _getTicketKey($hash)
    {
        return $this->redisTicketPrefix.':'.$hash;
    }
    
    
    public function saveToken($token)
    {
        $ticketKey = $this->_getTicketKey($token);
        $this->redis->setex($ticketKey, 100, $token);
    }
    
    
	public function checkTicket($hash)
	{
		$result = null;
		$ticketKey = $this->_getTicketKey($hash);
		$ticket = $this->redis->get($ticketKey);
		
		if (!empty($ticket)) {
		    $result = $ticket;
		    $this->redis->delete($ticketKey);
		} 
		
		return $result;
	}
	
}
?>
