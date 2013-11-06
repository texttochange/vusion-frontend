<?php 
App::uses('Component', 'Controller');
//App::uses('User', 'Model')

class TicketComponent extends Component
{
    var $sitename   = 'http://vusion.texttochange.org'; 
    var $linkdomain = 'vusion.texttochange.org';
    var $hours = 24;
    
    
    public function startup(Controller $controller)
    {
        parent::startup($controller);
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
            $this->redisTicketPrefix = 'vusion:ticket';
        }
    }
    
    
    
    
    
	public function createMessage($token)
	{
 
		$ms = '<html><head><title>Password Reset Request</title></head>'+
		 '<body>Your email has been used in a password reset request at '.$this->sitename.'<br/>'+
		 'If you did not initiate this request, then ignore this message.<br/>'+
		 '  Copy the link below into your browser to reset your password.<br/>'+
		 '<a href="http://'.$this->linkdomain.'/users/useticket/'.$token.'">Reset Password</a>'+
		 '</body></html>';
 
		$ms  = wordwrap($ms,70);
 
		return $ms;
 
	}
 
	
	
	public function checkTicket($hash)
	{
		$this->purgeTickets();
		$ret=false;
		$tick=$this->controller->Ticket->findByHash($hash);
 
		if(empty($tick)){
			//no more ticket			
		}else{
			$ret=$tick;
		}
		return $ret;
	}
}
?>
}
?>
