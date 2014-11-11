<?php

class VumiSupervisord {
	
	var $groupName = 'ttc_multi_worker' ;

	public function getState(){
	
		//require_once('xmlrpc-3.0.0.beta/xmlrpc.inc');
		
		$f=new xmlrpcmsg('supervisor.getState');
		
		$c=new xmlrpc_client("/RPC2", "localhost",9010);
		
		//$c->setDebug(1);
		
		$r=&$c->send($f);
		
		 if(!$r->faultCode())
		 {
		 	 $arr = php_xmlrpc_decode($r->value());
		 	 return array(
		 	 	 'running' => true,
		 	 	 'msg' => $arr['statename']
		 	 	 );
		 }
		 else
		 {
		 	 return array(
		 	 	 'running' => false,
		 	 	 'code' => htmlspecialchars($r->faultCode()),
		 	 	 'msg' => htmlspecialchars($r->faultString())
		 	 	 );
		 }
	}
	
	function getAllProcessInfo() {
		//require_once('xmlrpc-3.0.0.beta/xmlrpc.inc');
		
		$f=new xmlrpcmsg('supervisor.getAllProcessInfo');
		
		$c=new xmlrpc_client("/RPC2", "localhost",9010);
		
		//$c->setDebug(1);
		
		$r=&$c->send($f);
		
		if(!$r->faultCode())
		{
			$arr = php_xmlrpc_decode($r->value());
			return $arr;
		}
		else
		{
			return "An error occurred, Code: " . htmlspecialchars($r->faultCode())
				. " Reason: '" . htmlspecialchars($r->faultString()) . "'";
		}
		
	}
	
	function getWorkerInfo($name) {
		//require_once('xmlrpc-3.0.0.beta/xmlrpc.inc');
		
		$val = array(new xmlrpcval($this->groupName.':'.$name));
		
		$f=new xmlrpcmsg('supervisor.getProcessInfo', $val);
		
		$c=new xmlrpc_client("/RPC2", "localhost",9010);
		
		//$c->setDebug(1);
		
		$r=&$c->send($f);
		
		 if(!$r->faultCode()) {
		 	 $arr = php_xmlrpc_decode($r->value());
		 	 return array(
		 	 	 'running' => true,
		 	 	 'msg' => $arr['statename']
		 	 	 );
		 } else {
		 	 return array(
		 	 	 'running' => false,
		 	 	 'msg' => 'Not registered'
		 	 	 );    
		 }   
		
	}
	
	
	function startWorker($worker_name){
		//require_once('xmlrpc-3.0.0.beta/xmlrpc.inc');
		
		$c=new xmlrpc_client("/RPC2", "localhost",9010);
		
		//print "Worker start: ".$worker_config->program->name;
		
		$val = array(
			new xmlrpcval($this->groupName),
			new xmlrpcval($worker_name), 
			new xmlrpcval (
				array( 
					'command' => new xmlrpcval("twistd 
						--pidfile=./tmp/pids/%(program_name)s_%(process_num)s.pid 
						-n start_worker 
						--vhost=/develop 
						--worker-class=vusion.TtcGenericWorker 
						--config=./ttc_generic_worker.yaml 
						--set-option=control_name:".$worker_name." 
						--set-option=transport_name:".$worker_name),
					
					//'command' => new xmlrpcval("ls -l"),
					'autostart' => new xmlrpcval("true"),
					'autorestart' => new xmlrpcval("true"),
					'startsecs' => new xmlrpcval("0"),
					'numprocs' => new xmlrpcval("1"),
					'stdout_logfile' => new xmlrpcval("./logs/%(program_name)s_%(process_num)s.log"),
					'stderr_logfile' => new xmlrpcval("./logs/%(program_name)s_%(process_num)s.err")
				),"struct")
			);
		
		
		$f=new xmlrpcmsg('twiddler.addProgramToGroup', $val);
		
		$r=&$c->send($f);
		
		 if(!$r->faultCode())
		 {
		 	 return array(
		 	 	 'running' => true,
		 	 	 );
		 	 
		 } else {
		 	 return array(
		 	 	 'running' => false,
		 	 	 'error_code' => htmlspecialchars($r->faultCode()),
		 	 	 'msg' => htmlspecialchars($r->faultString())
		 	 	 );
		 }
	}
	
	
	function removeWorker($name){
		//require_once('xmlrpc-3.0.0.beta/xmlrpc.inc');
		
		$c=new xmlrpc_client("/RPC2", "localhost",9010);
		
		//$c->setDebug(1);
		
		$val = array(
			new xmlrpcval($this->groupName),
			new xmlrpcval($name)
			);
		
		
		$f=new xmlrpcmsg('twiddler.removeProcessFromGroup', $val);
		
		$r=&$c->send($f);
		
		if(!$r->faultCode())
		{
			return array(
				'removed' => true
				);
		}
		else
		{
			return array(
				'removed' => false,
				'error_code' => htmlspecialchars($r->faultCode()),
				'msg' => htmlspecialchars($r->faultString())
				);
		}
	}
	
	function stopWorker($name){
		//require_once('xmlrpc-3.0.0.beta/xmlrpc.inc');
		
		$c=new xmlrpc_client("/RPC2", "localhost",9010);
		
		//$c->setDebug(1);
		
		$val = array(
			new xmlrpcval($this->groupName.':'.$name)
			);
		
		$f=new xmlrpcmsg('supervisor.stopProcess', $val);
		
		$r=&$c->send($f);
		
		if(!$r->faultCode())
		{
			return "Worker stoped";
			//echo php_xmlrpc_decode($r->value());
		}
		else
		{
			return "An error occurred, Code: " . htmlspecialchars($r->faultCode())
				. " Reason: '" . htmlspecialchars($r->faultString()) . "'";
		}
	}
}

?>
