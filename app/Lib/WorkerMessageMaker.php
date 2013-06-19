<?php 
App::uses('FieldValueIncorrect', 'Lib');


class WorkerMessageMaker 
{


    public function createWorker($application_name, $database_name, $dispatcher_name, $send_loop_period)
    {
        return array(
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
            );
    }


    public function removeWorker($application_name, $database_name)
    {
        return array(
            'message_type' => 'remove_worker',
            'worker_name' => $database_name,
            );  
    }


    public function updateSchedule($schedule_type, $object_id)
    {
        if (!in_array($schedule_type, array('dialogue', 'unattach', 'participant'))) {
            throw new FieldValueIncorrect('Schedule type not valide: '.$schedule_type);            
        }
        return array(
            'action' => 'update_schedule',
            'schedule_type' => $schedule_type,
            'object_id' => $object_id 
            );
    }


    public function updateRegisteredKeywords() 
    {
        return array(
            'action' => 'update_registered_keywords'
            );
    }


    public function reloadProgramSettings()
    {
        return array(
            'action' => 'reload_program_settings'
            );
    }

    
    public function testSendAllMessages($phone, $dialogue_object_id)
    {
        return array(
            'action' => 'test_send_all_messages',
            'phone_number' => $phone,
            'dialogue_obj_id' => $dialogue_object_id
            );
    }

    public function transportUserMessage($from, $content) 
    {
        return array(
            'content' => $content, 
            'message_version' => '20110921',
            'message_type' => '', 
            'timestamp' =>'', 
            'message_id' => '', 
            'to_addr' => '0.0.0.0:9020',
            'from_addr' => $from,
            'in_reply_to' => '',
            'session_event'=> null,
            'transport_name' =>'',
            'transport_type' => '',
            'transport_metadata' => '',
            'helper_metadata' => ''
            );
    }
    
    
}