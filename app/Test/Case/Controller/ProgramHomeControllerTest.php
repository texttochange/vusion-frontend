<?php
/* Programs Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ProgramHomeController', 'Controller');
App::uses('ScriptMaker', 'Lib');
App::uses('UnattachedMessage','Model');

/**
 * TestProgramHomeControllerController *
 */
class TestProgramHomeController extends ProgramHomeController
{


    public $autoRender = false;


    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
    

}


class ProgramHomeControllerTestCase extends ControllerTestCase
{

    
    var $programData = array(
            0 => array( 
                'Program' => array(
                    'name' => 'Test Name',
                    'country' => 'Test Country',
                    'url' => 'testurl',
                    'timezone' => 'UTC',
                    'database' => 'testdbprogram'
                )
            ));
    

    public function setUp() 
    {
        parent::setUp();

        $this->Home = new TestProgramHomeController();
        $this->instanciateModels(); 

        $this->ScriptMaker = new ScriptMaker();

    }


    protected function dropData()
    {
        $this->Home->Dialogue->deleteAll(true, false);
        $this->Home->Schedule->deleteAll(true, false);
        $this->Home->UnattachedMessage->deleteAll(true, false);
    }


    protected function instanciateModels() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);
        
        $this->Home->Dialogue          = new Dialogue($options);
        $this->Home->Schedule          = new Schedule($options);
        $this->Home->UnattachedMessage = new UnattachedMessage($options);
    }


    public function tearDown()
    {
        
        $this->dropData();
        
        unset($this->Scripts);

        parent::tearDown();
    }

    
    protected function mockProgramAccess()
    {
        $home = $this->generate('ProgramHome', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
            ),
            'models' => array(
                'Program' => array('find', 'count'),
                'Group' => array()
            ),
        ));
        
        $home->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
        
        $home->Program
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($this->programData));
                    
        $home->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls(
                '4',
                '2',
                $this->programData[0]['Program']['database'], 
                $this->programData[0]['Program']['name'],
                'Africa/Kampala',
                'testdbprogram'
                ));

        return $home;
    }


    /** Test methods */

    public function testIndex_emptyProgram_asManager()
    {
        $this->mockProgramAccess();
            
        $this->testAction("/testurl/home", array('method' => 'get'));

        $this->assertEquals($this->vars['programName'], $this->programData[0]['Program']['name']);
        $this->assertEquals($this->vars['programUrl'], $this->programData[0]['Program']['url']);
    }

/* to be used in testing access write    
    public function testIndex_existingDraftScript_asManager()
    {
        $this->mockProgramAccess();
                
        $script['Script'] = array(
            'script' => array(
                'do' => 'something'
            )
            );
        $this->instanciateScriptModel();
        $this->Home->Script->create();
        $this->Home->Script->save($script);
        
        $this->testAction("/testurl/home", array('method' => 'get'));
        
        $this->assertEquals($this->vars['programName'], $this->programData[0]['Program']['name']);
        $this->assertEquals($this->vars['programUrl'], $this->programData[0]['Program']['url']);
    }

    
    public function testIndex_existingScripts_asManager()
    {
        $this->mockProgramAccess();
            
        $script = array('script' => 'do something');
        $this->instanciateScriptModel();
        $this->Home->Script->create();
        $this->Home->Script->save($script);
        
        $this->testAction("/testurl/home", array('method' => 'get'));

        $this->assertEquals($this->vars['programName'], $this->programData[0]['Program']['name']);
        $this->assertEquals($this->vars['programUrl'], $this->programData[0]['Program']['url']);
    }
*/

    public function testIndex_displayScheduled()
    {
        $this->mockProgramAccess();

        $dialogue = $this->ScriptMaker->getOneDialogue(); 

        $savedDialogue = $this->Home->Dialogue->saveDialogue($dialogue);
        $this->Home->Dialogue->makeActive($savedDialogue['Dialogue']['_id']);
        
        $unattachedMessage = array(
            'schedule' => '2021-06-12T12:30:00',
            'content' => 'Hello',
            );        $this->Home->UnattachedMessage->create();
        $this->Home->UnattachedMessage->save($unattachedMessage);

        $schedules = array(
            array(
                'datetime' => '2021-06-12T12:30',
                'dialogue-id' => $savedDialogue['Dialogue']['dialogue-id'],
                'interaction-id' => $savedDialogue['Dialogue']['interactions'][0]['interaction-id'],
                ),
            array(
                'datetime' => '2021-06-12T12:30',
                'unattach-id' => $this->Home->UnattachedMessage->id,
                )
            );

        $this->Home->Schedule->create();
        $this->Home->Schedule->saveMany($schedules);

        $this->testAction("/testurl/home", array('method' => 'get'));

        $this->assertEquals(2, count($this->vars['schedules']));
        $this->assertEquals("how are you", $this->vars['schedules'][0]['content']);
        $this->assertEquals(1, $this->vars['schedules'][0]['csum']);
         $this->assertEquals("Hello", $this->vars['schedules'][1]['content']);
        $this->assertEquals(1, $this->vars['schedules'][1]['csum']);
    }

}
