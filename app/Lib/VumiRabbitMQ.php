<?php
App::uses('WorkerMessageMaker', 'Lib');


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

        $this->workerMessageMaker = new WorkerMessageMaker();
    }


    public function sendMessageToCreateWorker($application_name, $database_name, $dispatcher_name="dispatcher", $send_loop_period="60")
    {
        $msg = $this->workerMessageMaker->createWorker($application_name, $database_name, $dispatcher_name, $send_loop_period);
        return $this->sendMessageTo('vusion.control', $msg);
    }


    public function sendMessageToRemoveWorker($application_name, $database_name)
    {
        $msg = $this->workerMessageMaker->removeWorker($application_name, $database_name);
        return $this->sendMessageTo('vusion.control', $msg);
    }


    public function sendMessageToExport($export_id)
    {
        $msg = $this->workerMessageMaker->export($export_id);
        return $this->sendMessageTo('export.control', $msg);
    }


    // Program Specific calls
    public function sendMessageToUpdateSchedule($to, $schedule_type, $object_id)
    {
        $msg = $this->workerMessageMaker->updateSchedule($schedule_type, $object_id);
        return $this->sendMessageTo($to.'.control', $msg);
    }


    public function sendMessageToUpdateRegisteredKeywords($to)
    {
        $msg = $this->workerMessageMaker->updateRegisteredKeywords();
        return $this->sendMessageTo($to.'.control', $msg);
    }


    public function sendMessageToReloadRequest($to, $object_id)
    {
        $msg = $this->workerMessageMaker->reloadRequest($object_id);
        return $this->sendMessageTo($to.'.control', $msg);
    }


    public function sendMessageToReloadProgramSettings($to)
    {
        $msg = $this->workerMessageMaker->reloadProgramSettings();
        return $this->sendMessageTo($to.'.control', $msg);
    }


    public function sendMessageToSendAllMessages($to, $phone, $dialogue_object_id)
    {
        $msg = $this->workerMessageMaker->testSendAllMessages($phone, $dialogue_object_id);
        return $this->sendMessageTo($to.'.control', $msg);
    }


    public function sendMessageMassTag($to, $tag, $query)
    {
        $msg = $this->workerMessageMaker->massTag($tag, $query);
        return $this->sendMessageTo($to.'.control', $msg);
    }


    public function sendMessageMassUntag($to, $tag)
    {
        $msg = $this->workerMessageMaker->massUntag($tag);
        return $this->sendMessageTo($to.'.control', $msg);
    }


    public function sendMessageRunActions($to, $runActions)
    {
        $msg = $this->workerMessageMaker->runActions(
            $runActions['phone'],
            $runActions['dialogue-id'],
            $runActions['interaction-id'],
            $runActions['answer']);
        return $this->sendMessageTo($to.'.control', $msg);
    }


    public function sendMessageToWorker($to, $from, $content)
    {
        $msg = $this->workerMessageMaker->transportUserMessage($from, $content);
        return $this->sendMessageTo($to.'.inbound', $msg);
    }
    
    
    public function sendMessageToSimulateMO($to, $from, $content)
    {
        $msg = $this->workerMessageMaker->transportUserMessage($from, $content);
        return $this->sendMessageTo($to.'.inbound', $msg);
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

