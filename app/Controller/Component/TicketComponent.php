<?php 
App::uses('Component', 'Controller');
App::uses('User', 'Model');
App::uses('CakeEmail', 'Network/Email');


class TicketComponent extends Component
{
    var $components = array('Email');
    var $sitename   = 'http://vusion.texttochange.org'; 
    //var $linkdomain = 'vusion.texttochange.org';
    var $linkdomain = '192.168.0.160:81';
   
    
    
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
    
    
    
    protected function _getTicketKey($userId, $hash)
    {
        return $this->redisTicketPrefix.':'.$userId.':'.$hash;
    }
    
	public function createMessage($token)
	{ 
		$ms='<html><head><title>Password Reset Request</title></head>';
		$ms .='<body>Your email has been used in a password reset request at '.$this->sitename.'<br/>';
		$ms .='If you did not initiate this request, then ignore this message.<br/>';
		$ms .='  Click the link below into your browser to reset your password.<br/>';
		$ms .='<a href="http://'.$this->linkdomain.'/users/useTicket/'.$token.'">Reset Password</a>';
		$ms .='</body></html>';
		$ms  = wordwrap($ms,70);
		return $ms;
 
	}
 
	
	public function sendEmail($userEmail, $userName, $message)
	{  
	    $email = new CakeEmail();
	    $email->config('default');
	    $email->from(array('mssembajjwe@texttochange.com' => 'vusion.com'));
	    $email->to($userEmail);
	    $email->subject('Message from '.$this->sitename.' for '.$userName);
	    
	    $email->send($message);
	    
	    /*
	    $to = $email;
	    $subject = 'Message from '.$this->sitename.' for '.$userName;
	    
	    
	    $headers  = 'MIME-Version: 1.0' . "\r\n";
	    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	    $headers .= "From: ".$this->sitename." <noreply@".$this->linkdomain.">\n";
	    //$headers .= "Cc: mssembajjwe@texttochange.com"."\r\n";
	    $headers .= 'X-Sender: <noreply@'.$this->linkdomain.'>\n';
	    $headers .= 'X-Mailer: PHP\n';
	    //mail('markphi119@gmail.com', 'My subject', 'hi kkl');
	    //mail($to, $subject, $message, $headers); 
	    if(mail($to, $subject, $message, $headers)) {
	    echo "mail sent";
	    } else {
	    echo "mail not sent";
	    }
	    */
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
