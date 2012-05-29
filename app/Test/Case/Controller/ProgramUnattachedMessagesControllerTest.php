<?php
/* ProgramUnattachedMessages Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ProgramUnattachedMessagesController', 'Controller');
App::uses('Schedule', 'Model');
App::uses('ProgramSetting', 'Model');

/**
 * TestProgramUnattachedMessagesController *
 */
class TestProgramUnattachedMessagesController extends ProgramUnattachedMessagesController
{

    public $autoRender = false;


    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }

    
}

/**
 * ProgramUnattachedMessagesController Test Case
 *
 */
class ProgramUnattachedMessagesControllerTestCase extends ControllerTestCase
{
    var $programData = array(
        0 => array( 
            'Program' => array(
            'name' => 'Test Name',
            'url' => 'testurl',
            'timezone' => 'utc',
            'database' => 'testdbprogram'
        )
     ));

    
    public function setUp() 
    {
        Configure::write("mongo_db",$this->programData[0]['Program']['database']);
        parent::setUp();

        $this->ProgramUnattachedMessages = new TestProgramUnattachedMessagesController();
        $this->instanciateModels();
        $this->dropData();
    }


    protected function instanciateModels() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);    
        $this->UnattachedMessage = new UnattachedMessage($options);
        $this->Schedule = new Schedule($options);
        $this->ProgramSetting = new ProgramSetting($options);
    }	
  
    
    protected function dropData()
    {
        $this->UnattachedMessage->deleteAll(true, false);
        $this->Schedule->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
    }
    

    public function tearDown() 
    {
        $this->dropData();

        unset($this->ProgramUnattachedMessages);

        parent::tearDown();
    }
    
    
    public function mock_program_access()
    {
        $unattachedMessages = $this->generate(
            'ProgramUnattachedMessages', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read')
                    ),
                'models' => array(
                   'Program' => array('find', 'count'),
                   'Group' => array()
                   ),
                'methods' => array(
                    '_notifyUpdateBackendWorker'
                    )
                )
            );
    
        $unattachedMessages->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
            
        $unattachedMessages->Program
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($this->programData));

        $unattachedMessages->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls(
                '4', 
                '2',
                $this->programData[0]['Program']['database'],
                $this->programData[0]['Program']['name'],
                'utc',
                'testdbprogram'
                ));	    
 
        return $unattachedMessages;

    }
    
/**
 * Test methods
 *
 */
    public function testIndex()
    {
        $this->mock_program_access();
        
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow');
        $this->UnattachedMessage->create();
        $this->UnattachedMessage->save(array(
                'to' => 'all participants',
                'content' => 'Hello!!!!',
                'schedule' => $date->format('d/m/Y H:i')
            ));
        
        $this->testAction("/testurl/programUnattachedMessages/index");
        
        $this->assertEquals(1, count($this->vars['unattachedMessages']));	
    }


    public function testAdd()
    {
        $unattachedMessages = $this->mock_program_access();
        
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $unattachedMessages
            ->expects($this->once())
            ->method('_notifyUpdateBackendWorker')
            ->will($this->returnValue(true));

        $date = new DateTime('tomorrow');    
        $unattachedMessage = array(
            'UnattachedMessage' => array(
                'to' => 'all participants',
                'content' => 'Hello!!!!',
                'schedule' => $date->format('d/m/Y H:i')
             )
        );
        $this->testAction("/testurl/programUnattachedMessages/add", array(
            'method' => 'post',
            'data' => $unattachedMessage
            )
        );

        $this->assertEquals(1,
            $this->UnattachedMessage->find('count')
        );
    }


    public function testEdit()
    {
        $unattachedMessages = $this->mock_program_access();
        
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $unattachedMessages
            ->expects($this->once())
            ->method('_notifyUpdateBackendWorker')
            ->will($this->returnValue(true));
        
        $date = new DateTime('tomorrow');    
        $unattachedMessage = array(
            'UnattachedMessage' => array(
                'to' => 'all participants',
                'content' => 'Hello!!!!',
                'schedule' => $date->format('d/m/Y H:i')
             )
        );
        $this->UnattachedMessage->create();
        $data = $this->UnattachedMessage->save($unattachedMessage);
        
        $this->testAction("/testurl/programUnattachedMessages/edit/".$data['UnattachedMessage']['_id'],
            array(
            'method' => 'post',
            'data' => array(
                'UnattachedMessage' => array(
                    'to' => 'all participants',
                    'content' => 'Bye!!!!',
                    'schedule' => $date->format('d/m/Y H:i')
                    )
                )
            )
        );
        //print_r($this->result);
        $this->assertEquals('Bye!!!!',
            $this->result['UnattachedMessage']['content']
        );            
    }


    public function testDelete()
    {
        $this->mock_program_access();
        
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow');
        $unattachedMessage = array(
            'UnattachedMessage' => array(
                'to' => 'all participants',
                'content' => 'Hello!!!!',
                'schedule' => $date->format('d/m/Y H:i')
             )
        );
        $this->UnattachedMessage->create();
        $data = $this->UnattachedMessage->save($unattachedMessage);
  
        $scheduleToBeDeleted= array(
            'Schedule' => array(
                'unattach-id' => $data['UnattachedMessage']['_id'],
                )
            );

        $this->Schedule->create();
        $this->Schedule->save($scheduleToBeDeleted);
      
        $this->testAction("/testurl/programUnattachedMessages/delete/".$data['UnattachedMessage']['_id']);
        
        $this->assertEquals(
            0,
            $this->UnattachedMessage->find('count')
            );
        $this->assertEquals(
            0,
            $this->Schedule->find('count')
            );

    }


}
