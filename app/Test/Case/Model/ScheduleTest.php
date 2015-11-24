<?php
App::uses('Schedule', 'Model');
App::uses('ProgramSpecificMongoModel', 'Model');


class ScheduleTestCase extends CakeTestCase
{
    
    
    public function setUp()
    {
        parent::setUp();
        $dbName = 'testdbprogram';
        $this->Schedule = ProgramSpecificMongoModel::init(
            'Schedule', $dbName);
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


    public function testAggregateNVD3()
    {
        
        $schedules = array(
            array(
                'object-type' => 'reminder-schedule',
                'dialogue-id' => 'someId',
                'interaction-id' => 'someOtherId2',
                'date-time' => '2013-04-12T13:10',
                'participant-phone' => '07'
                ),
            array(
                'object-type' => 'deadline-schedule',
                'dialogue-id' => 'someId',
                'interaction-id' => 'someOtherId2',
                'date-time' => '2013-04-12T13:10',
                'participant-phone' => '07'
                ),
            array(
                'object-type' => 'dialogue-schedule',
                'dialogue-id' => 'someId',
                'interaction-id' => 'someOtherId',
                'date-time' => '2013-04-12T12:00',
                'participant-phone' => '06'
                ),
             array(
                'object-type' => 'reminder-schedule',
                'dialogue-id' => 'someId',
                'interaction-id' => 'someOtherId2',
                'date-time' => '2013-04-12T13:10',
                'participant-phone' => '07'
                ),
            array(
                'object-type' => 'dialogue-schedule',
                'dialogue-id' => 'someId',
                'interaction-id' => 'someOtherId',
                'date-time' => '2013-04-13T12:00',
                'participant-phone' => '07'
                ),
            array(
                'object-type' => 'unattach-schedule',
                'unattach-id' => 'someId2',
                'date-time' => '2013-04-13T11:00',
                ),
            array(
                'object-type' => 'action-schedule',
                'action' => 'feedback',
                'date-time' => '2013-04-12T11:00',
                ),
            array(
                'object-type' => 'dialogue-schedule',
                'dialogue-id' => 'someId',
                'interaction-id' => 'someOtherId2',
                'date-time' => '2013-04-14T13:00',
                'participant-phone' => '07'
                ),

            );
        
        foreach ($schedules as $schedule) {
            $this->Schedule->create($schedule['object-type']);
            $this->Schedule->save($schedule);
        }
        
        $results = $this->Schedule->aggregateStats("2013-04-14T00:00:00");
        $this->assertEquals(
            $results, 
            array(
                array(
                    'key' => 'messages',
                    'values' => array(
                        array(
                            'x' => '2013-04-12',
                            'y' => 3),
                        array(
                            'x' => '2013-04-13',
                            'y' => 2)
                        )),
                array(
                    'key' => 'actions',
                    'values' => array(
                        array(
                            'x' => '2013-04-12',
                            'y' => 2))
                    )));
    }

    
    public function testGetUniqueParticipantPhone()
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
                ));

        foreach ($schedules as $schedule) {
            $this->Schedule->create($schedule['object-type']);
            $this->Schedule->save($schedule);
        }

        $results = $this->Schedule->getUniqueParticipantPhone();
        $this->assertEqual(array('07', '06'), $results);
    }

    public function testGetUniqueParticipantPhoneCursor()
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
                ));

        foreach ($schedules as $schedule) {
            $this->Schedule->create($schedule['object-type']);
            $this->Schedule->save($schedule);
        }

        $cursorResults = $this->Schedule->getUniqueParticipantPhone(array('cursor' => true));
        $i = 0;
        foreach ($cursorResults as $item) {
            if ($i == 0) {
                $this->assertEqual('07', $item['_id']);
            } else {
                $this->assertEqual('06', $item['_id']);    
            }
            $i++;
        }
    }
    
}
