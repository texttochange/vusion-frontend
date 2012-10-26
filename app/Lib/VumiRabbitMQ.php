<?php

class VumiRabbitMQ {

    function __construct($configRabbitmq) {
        $this->vhost = $configRabbitmq['vhost'];
        $this->username = $configRabbitmq['username'];
        $this->password = $configRabbitmq['password'];
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
                    'application_name' =>  $database_name,
                    'transport_name' => $database_name,
                    'control_name' => $database_name,
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
        //echo "Send RabbitMQ message to:".$to;
        //print_r($msg);
        require_once('php-amqplib/amqp.inc');
        
        $EXCHANGE = 'vumi';
        $BROKER_HOST   = 'localhost';
        $BROKER_PORT   = 5672;
        $QUEUE    = $to; //'telnet.inbound';
        
        $msg_body = json_encode($msg);
        
        //echo "sending '".$msg_body."' to '".$to."'";
    
        //echo "starting...";
        
        $conn = new AMQPConnection(
                            $BROKER_HOST, 
                            $BROKER_PORT,
                            $this->username,
                            $this->password,
                            $this->vhost);
        //echo "Getting channel\n";
        $ch = $conn->channel();
        //echo "Requesting access\n";
        $ch->access_request('/data', false, false, true, true);
        
        //echo "<br>Declaring exchange";
        $ch->exchange_declare($EXCHANGE, 'direct', false, true, false);
        //echo "<br>Creating message\n";
        $msg = new AMQPMessage($msg_body, array('content_type' => 'text/plain'));
        //print_r($msg_body);
        //echo "<br>Publishing message";
        $ch->basic_publish($msg, $EXCHANGE, $QUEUE);
        
        //echo "<br>Closing channel\n";
        $ch->close();
        //echo "<br>Closing connection\n";
        $conn->close();
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

