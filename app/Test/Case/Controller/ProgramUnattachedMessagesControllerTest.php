<?php
/* ProgramUnattachedMessages Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ProgramUnattachedMessagesController', 'Controller');

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
        //ClassRegistry::config(array('ds' => 'mongo_test'));
        $this->instanciateProgramUnattachedMessagesModel();
        $this->dropData();
    }


    protected function instanciateProgramUnattachedMessagesModel() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);
        
        $this->ProgramUnattachedMessages->UnattachedMessage = new UnattachedMessage($options);
    }	
  
    
    protected function dropData()
    {
        $this->ProgramUnattachedMessages->UnattachedMessage->deleteAll(true, false);
    }
    

    public function tearDown() 
    {
        $this->dropData();

        unset($this->ProgramUnattachedMessages);

        parent::tearDown();
    }
    
    
    public function mock_program_access()
    {
        $unattachedMessages = $this->generate('ProgramUnattachedMessages', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
            ),
            'models' => array(
                'Program' => array('find', 'count'),
                'Group' => array()
                )
            ));
    
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
                $this->programData[0]['Program']['database']
                //$this->programData[0]['Program']['name']
                ));	    
 
    }
    

    public function testIndex()
    {
        $this->mock_program_access();
        
        $this->instanciateProgramUnattachedMessagesModel();
        $this->ProgramUnattachedMessages->UnattachedMessage->create();
        $this->ProgramUnattachedMessages->UnattachedMessage->save(array(
                'to' => 'all participants',
                'content' => 'Hello!!!!',
                'schedule' => '05/04/2012 16:00'
            ));
        
        $this->testAction("/testurl/programUnattachedMessages/index");
        
        $this->assertEquals(1, count($this->vars['unattachedMessages']));	
    }


    public function testAdd()
    {
        $this->mock_program_access();
        
        $unattachedMessages = array(
            'ProgramUnattachedMessages' => array(
                'to' => 'all participants',
                'content' => 'Hello!!!!',
                'schedule' => '05/04/2012 16:00'
             )
        );
        $this->testAction("/testurl/programUnattachedMessages/add", array(
            'method' => 'post',
            'data' => $unattachedMessages
            )
        );
        $this->assertEquals(1,
            $this->ProgramUnattachedMessages->UnattachedMessage->find('count')
        );
    }


    public function testEdit()
    {
        $this->mock_program_access();
        
        $unattachedMessages = array(
            'ProgramUnattachedMessages' => array(
                'to' => 'all participants',
                'content' => 'Hello!!!!',
                'schedule' => '05/04/2012 16:00'
             )
        );
        $this->ProgramUnattachedMessages->UnattachedMessage->create();
        $data = $this->ProgramUnattachedMessages->UnattachedMessage->save($unattachedMessages);
        
        $this->testAction("/testurl/programUnattachedMessages/edit/".$data['UnattachedMessage']['_id'],
            array(
            'method' => 'post',
            'data' => array(
                'ProgramUnattachedMessages' => array(
                    'to' => 'all participants',
                    'content' => 'Bye!!!!',
                    'schedule' => '05/04/2012 16:00'
                    )
                )
            )
        );
        //print_r($this->result);
        $this->assertEquals('Bye!!!!',
            $this->result['ProgramUnattachedMessages']['content']
        );            
    }


    public function testDelete()
    {
        $this->mock_program_access();
        
        $unattachedMessages = array(
            'ProgramUnattachedMessages' => array(
                'to' => 'all participants',
                'content' => 'Hello!!!!',
                'schedule' => '05/04/2012 16:00'
             )
        );
        $this->ProgramUnattachedMessages->UnattachedMessage->create();
        $data = $this->ProgramUnattachedMessages->UnattachedMessage->save($unattachedMessages);
        
        $this->testAction("/testurl/programUnattachedMessages/delete/".$data['UnattachedMessage']['_id']);
        
        $this->assertEquals(0,
            $this->ProgramUnattachedMessages->UnattachedMessage->find('count')
        );
    }


}
