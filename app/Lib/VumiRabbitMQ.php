<?php

class VumiRabbitMQ {


    function IsRabbitMQRunning(){
        //AMQP PHP library test
        
        require_once('php-amqplib/amqp.inc');
        
        $EXCHANGE = 'test';
        $BROKER_HOST   = 'localhost';
        $BROKER_PORT   = 5672;
        $QUEUE    = 'echo';
        $USER     ='guest';
        $PASSWORD ='guest';
        
        $msg_body = NULL;
        
    //    try
    //    {
    //echo "Creating connection\n";
            $conn = new AMQPConnection($BROKER_HOST, $BROKER_PORT,
                           $USER,
                           $PASSWORD);
            
            //echo "Getting channel\n";
            $ch = $conn->channel();
            //echo "Requesting access\n";
            $ch->access_request('/data', false, false, true, true);
            
            //echo "Declaring exchange\n";
            $ch->exchange_declare($EXCHANGE, 'direct', false, false, false);
            //echo "Creating message\n";
            $msg = new AMQPMessage($msg_body, array('content_type' => 'text/plain'));
            
            //echo "Publishing message\n";
            $ch->basic_publish($msg, $EXCHANGE, $QUEUE);
            
            //echo "Closing channel\n";
            $ch->close();
            //echo "Closing connection\n";
            $conn->close();
            return (true);
            //echo "Done.\n";
    //    } catch (Exception $e) {
            //echo 'Caught exception: ',  $e->getMessage();
            //echo "\nTrace:\n" . $e->getTraceAsString();
    //        return(false);
    //    }
    }


    public function sendMessageToCreateWorker($application_name, $database_name, $dispatcher_name="dispatch", $send_loop_period="60")
    {
        return $this->sendMessageTo(
            'vusion.control', 
            array(
                'message_type' => 'add_worker',
                'worker_name' => $database_name,
                'worker_class' => 'vusion.TtcGenericWorker',
                'config' => array(
                    'application_name' =>  $database_name,
                    'transport_name' => $database_name,
                    'control_name' => $database_name,
                    'database_name' => $database_name,
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

    public function sendMessageToUpdateSchedule($to)
    {
        return $this->sendMessageTo(
            $to.'.control',
            array('action' => 'update-schedule')
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
        //$ROUTING_KEY = 'telnet.event';
        $USER     ='vumi';
        $PASSWORD ='vumi';
        $VHOST = "/develop";
        
        //vumi use the JSON fromat for his messages
        /*if (strpos($to,"inbound")) {
            $arr = array("content" => $msg, 
                "message_version" => '20110921',
                "message_type" => '', 
                "timestamp" =>"", 
                "message_id" => "", 
                "to_addr" => "0.0.0.0:9020",
                "from_addr" => "",
                "in_reply_to" => "",
                "session_event"=> null,
                "transport_name" =>"",
                "transport_type" => "",
                "transport_metadata" => "",
                "helper_metadata" => "");
        } elseif (strpos($to,"event")) {
            $arr = array(
                "message_version"=>"20110921",
                "message_type"=>"event",
                "user_message_id"=>"",
                "event_id"=>"",
                "event_type"=>"ack",
                "timestamp"=>"",
                "sent_message_id"=>"");    
        } else {
            $arr = json_decode($msg);
            //return ("no message send, not type matching");    
        }*/
        
        $msg_body = json_encode($msg);
        
        //echo "sending '".$msg_body."' to '".$to."'";
    
        //echo "starting...";
        
        $conn = new AMQPConnection($BROKER_HOST, $BROKER_PORT,
                           $USER,
                           $PASSWORD,"/develop");
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
        //$ROUTING_KEY = 'telnet.event';
        $USER     ='vumi';
        $PASSWORD ='vumi';
        $VHOST = "/develop";
     
        $conn = new AMQPConnection($BROKER_HOST, $BROKER_PORT,
                           $USER,
                           $PASSWORD,"/develop");
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

