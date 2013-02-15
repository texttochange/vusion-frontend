<?php

class VumiRabbitMQ {

    
    function __construct($configRabbitmq) {
        require_once('php-amqplib/amqp.inc');   
    
        $this->vhost = $configRabbitmq['vhost'];
        $this->username = $configRabbitmq['username'];
        $this->password = $configRabbitmq['password'];
        
        $this->EXCHANGE = 'vumi';
        $BROKER_HOST   = 'localhost';
        $BROKER_PORT   = 5672;
        $this->conn = new AMQPConnection(
                            $BROKER_HOST, 
                            $BROKER_PORT,
                            $this->username,
                            $this->password,
                            $this->vhost);
        $this->ch = $this->conn->channel();
        $this->ch->access_request('/data', false, false, true, true);
        $this->ch->exchange_declare($this->EXCHANGE, 'direct', false, true, false);
    }
    
    function __destruct()
    {
        $this->ch->close();
        $this->conn->close();
    }

    public function sendMessageToCreateWorker($application_name, $database_name, $dispatcher_name="dispatcher", $send_loop_period="60")
    {
        return $this->sendMessageTo(
            'vusion.control', 
            array(
                'message_type' => 'add_worker',
                'worker_name' => $database_name,
                'worker_class' => 'vusion.DialogueWorker',
                'config' => array(
                    'application_name' =>  $application_name,
                    'transport_name' => $application_name,
                    'control_name' => $application_name,
                    'database_name' => $database_name,
                    'vusion_database_name' => 'vusion',
                    'dispatcher_name' => $dispatcher_name,
                    'send_loop_period'=> $send_loop_period,
                    )
                ) 
            );
    }

    public function sendMessageToRemoveWorker($application_name, $database_name)
    {
        return $this->sendMessageTo(
            'vusion.control', 
            array(
                'message_type' => 'remove_worker',
                'worker_name' => $database_name,
                )
            );
    }

    public function sendMessageToUpdateSchedule($to, $schedule_type, $object_id)
    {
        $message = array(
            'action' => 'update_schedule',
            'schedule_type' => $schedule_type,
            'object_id' => $object_id );
        
        return $this->sendMessageTo(
            $to.'.control',
            $message
            );
    }

    public function sendMessageToUpdateRegisteredKeywords($to)
    {
        $message = array(
            'action' => 'update_registered_keywords');
        
        return $this->sendMessageTo(
            $to.'.control',
            $message
            );
    }

    public function sendMessageToReloadProgramSettings($to)
    {
        $message = array(
            'action' => 'reload_program_settings');
        
        return $this->sendMessageTo(
            $to.'.control',
            $message
            );
    }

    public function sendMessageToSendAllMessages($to, $phone, $dialogueObjId)
    {
        return $this->sendMessageTo(
            $to.'.control',
            array(
                'action' => 'test-send-all-messages',
                'phone_number' => $phone,
                'dialogue_obj_id' => $dialogueObjId)
            );
    }


    public function sendMessageToWorker($to, $from, $msg)
    {
         return $this->sendMessageTo(
             $to.'.inbound',
             array(
                 "content" => $msg, 
                 "message_version" => '20110921',
                 "message_type" => '', 
                 "timestamp" =>"", 
                 "message_id" => "", 
                 "to_addr" => "0.0.0.0:9020",
                 "from_addr" => $from,
                 "in_reply_to" => "",
                 "session_event"=> null,
                 "transport_name" =>"",
                 "transport_type" => "",
                 "transport_metadata" => "",
                 "helper_metadata" => "")
             );
    }    


    public function sendMessageTo($to, $msg)
    {
        require_once('php-amqplib/amqp.inc');   
    
        $QUEUE    = $to; //'telnet.inbound';
        
        $msg_body = json_encode($msg);
        $msg = new AMQPMessage($msg_body, array('content_type' => 'text/plain'));
        $this->ch->basic_publish($msg, $this->EXCHANGE, $QUEUE);
               
        return ($msg_body);
    }

    public function getMessageFrom($from)
    {

        require_once('php-amqplib/amqp.inc');
        
        $EXCHANGE = 'vumi';
        $BROKER_HOST   = 'localhost';
        $BROKER_PORT   = 5672;
     
        $conn = new AMQPConnection(
                            $BROKER_HOST, 
                            $BROKER_PORT,
                            $this->username,
                            $this->password,
                            $this->vhost);
        $ch = $conn->channel();
        $ch->access_request('/data', false, false, true, true);
        
        $ch->queue_declare($from, false, true, false, false);
        
        $ch->exchange_declare($EXCHANGE, 'direct', false, true, false);

        $ch->queue_bind($from, $EXCHANGE, $from);
        
        $msg = $ch->basic_get($from, true);
        
        $ch->close();
        $conn->close();

        if ($msg)
            return $msg->body;
        else 
            return null;
    }


}

