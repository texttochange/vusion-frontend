<?php
App::uses('WorkerMessageMaker', 'Lib');
App::uses('FieldValueIncorrect', 'Lib');

class WorkerMessageMakerTest extends CakeTestCase
{


    public function setUp() {
        $this->workerMessageMaker = new WorkerMessageMaker();
    }


    public function test_createWorker() 
    {
        $expected = array(
            'message_type' => 'add_worker',
            'worker_name' => 'dbname',
            'worker_class' => 'vusion.DialogueWorker',
            'config' => array(
                'application_name' =>  'appname',
                'transport_name' => 'appname',
                'control_name' => 'appname',
                'database_name' => 'dbname',
                'vusion_database_name' => 'vusion',
                'dispatcher_name' => 'dispatchername',
                'send_loop_period'=> '60',
                ) 
            );
        $msg = $this->workerMessageMaker->createWorker('appname', 'dbname', 'dispatchername', '60');
        $this->assertEqual($msg, $expected);
    }


    public function test_removeWorker()
    {
        $expected = array(
            'message_type' => 'remove_worker',
            'worker_name' => 'dbname',
            );
        $msg = $this->workerMessageMaker->removeWorker('appname', 'dbname');
        $this->assertEqual($msg, $expected);
    }


    public function test_updateSchedule()
    {
        $expected = array(
            'action' => 'update_schedule',
            'schedule_type' => 'dialogue',
            'object_id' => '1'
            );
        $msg = $this->workerMessageMaker->updateSchedule('dialogue', '1');
        $this->assertEqual($msg, $expected);
    }


    public function test_updateSchedule_failed()
    {
        $this->setExpectedException('FieldValueIncorrect');
        $msg = $this->workerMessageMaker->updateSchedule('something wired', '1');
    }

    public function test_updateRegisteredKeywords()
    {
        $expected = array(
            'action' => 'update_registered_keywords'
            );
        $msg = $this->workerMessageMaker->updateRegisteredKeywords();
        $this->assertEqual($msg, $expected);
    }


    public function test_reloadProgramSettings()
    {
        $expected = array(
            'action' => 'reload_program_settings'
            );
        $msg = $this->workerMessageMaker->reloadProgramSettings();
        $this->assertEqual($msg, $expected);
    }


    public function test_testSendAllMessages()
    {
        $expected = array(
            'action' => 'test_send_all_messages',
            'phone_number' => '256000',
            'dialogue_obj_id' => '1'
            );
        $msg = $this->workerMessageMaker->testSendAllMessages('256000', '1');
        $this->assertEqual($msg, $expected);
    }


    public function test_transportUserMessage() 
    {
        $expected = array(
            'content' => 'Hello', 
            'message_version' => '20110921',
            'message_type' => '', 
            'timestamp' =>'', 
            'message_id' => '', 
            'to_addr' => '0.0.0.0:9020',
            'from_addr' => '256000',
            'in_reply_to' => '',
            'session_event'=> null,
            'transport_name' =>'',
            'transport_type' => '',
            'transport_metadata' => '',
            'helper_metadata' => ''
            );
        $msg = $this->workerMessageMaker->transportUserMessage('256000', 'Hello');
        $this->assertEqual($msg, $expected);        
    }


}