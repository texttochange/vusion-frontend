<?php

App::uses('Schedule', 'Model');


class ScheduleTestCase extends CakeTestCase
{
    
    
    public function setUp()
    {
        parent::setUp();
        
        $options        = array('database' => 'test');
        $this->Schedule = new Schedule($options);
        
        $this->Schedule->setDataSource('mongo_test');
        
    }
    
    public function tearDown()
    {
        $this->Schedule->deleteAll(true, false);
        unset($this->Schedule);
        parent::tearDown();
    }
    
    public function testGetScheduleNextSummary()
    {
        
        $schedules = array(
            array(
                'object-type' => 'dialogue-schedule',
                'dialogue-id' => 'someId',
                'interaction-id' => 'someOtherId',
                'date-time' => '2013-04-12T12:00',
                'participant-phone' => '06'
                ),
            array(
                'object-type' => 'dialogue-schedule',
                'dialogue-id' => 'someId',
                'interaction-id' => 'someOtherId',
                'date-time' => '2013-04-12T12:00',
                'participant-phone' => '07'
                ),
            array(
                'object-type' => 'dialogue-schedule',
                'dialogue-id' => 'someId',
                'interaction-id' => 'someOtherId2',
                'date-time' => '2013-04-12T13:00',
                'participant-phone' => '07'
                ),
            array(
                'object-type' => 'unattach-schedule',
                'unattach-id' => 'someId2',
                'date-time' => '2013-04-12T11:00',
                ),
            array(
                'object-type' => 'feedback-schedule',
                'content-type' => 'feedback',
                ),
            array(
                'object-type' => 'reminder-schedule',
                'dialogue-id' => 'someId',
                'interaction-id' => 'someOtherId2',
                'date-time' => '2013-04-12T13:10',
                'participant-phone' => '07'
                )
            );
        
        foreach ($schedules as $schedule) {
            $this->Schedule->create($schedule['object-type']);
            $this->Schedule->save($schedule);
        }
        
        $dateTime = new DateTime("2014-04-12T12:00:00");
        
        $result = $this->Schedule->summary($dateTime);
        $this->assertEquals("2", $result[0]['csum']);
        $this->assertEquals("1", $result[1]['csum']);
        $this->assertEquals("1", $result[2]['csum']);
        
        $dateTime = new DateTime("2013-04-12T12:00:00");
        
        $result = $this->Schedule->summary($dateTime);
        $this->assertEquals("2", $result[0]['csum']);
        $this->assertEquals("1", $result[1]['csum']);
        $this->assertEquals(2, count($result)); 
        
    }
    
    public function testCountScheduleFromUnattachedMessage()
    {       
        $schedule =array(
            'object-type' => 'unattach-schedule',
            'unattach-id' => '6',
            'date-time' => '2013-04-12T11:00',
            );       
        
        $this->Schedule->create('unattach-schedule');
        $saveUnattachedSchedule = $this->Schedule->save($schedule);       
        
        $result = $this->Schedule->countScheduleFromUnattachedMessage('6');
        $this->assertEquals(1, $result);
        
        $result = $this->Schedule->countScheduleFromUnattachedMessage('7');
        $this->assertEquals(0, $result);
    }
    
    
}
