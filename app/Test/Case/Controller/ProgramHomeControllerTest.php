<?php
/* Programs Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ProgramHomeController', 'Controller');


/**
 * TestProgramScriptsControllerController *
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
        
        $this->dropData();        
    }


    protected function dropData()
    {
        //As this model is created on the fly, need to instantiate again
        $this->instanciateScriptModel();
        $this->Home->Script->deleteAll(true, false);
    }


    protected function instanciateScriptModel() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);
        
        $this->Home->Script = new Script($options);
    }


    public function tearDown()
    {
        
        $this->dropData();
        
        unset($this->Scripts);

        parent::tearDown();
    }

    
    protected function mockProgramAccess()
    {
        $Home = $this->generate('ProgramHome', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
            ),
            'models' => array(
                'Program' => array('find', 'count'),
                'Group' => array()
            ),
        ));
        
        $Home->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
        
        $Home->Program
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($this->programData));
                    
        $Home->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls(
                '4',
                '2',
                $this->programData[0]['Program']['database'], 
                $this->programData[0]['Program']['name']
                ));
    }


    /** Test methods */

    public function testIndex_emptyProgram_asManager()
    {
        $this->mockProgramAccess();
            
        $this->testAction("/testurl/home", array('method' => 'get'));

        $this->assertEquals($this->vars['programName'], $this->programData[0]['Program']['name']);
        $this->assertEquals($this->vars['programUrl'], $this->programData[0]['Program']['url']);
        $this->assertEquals($this->vars['isScriptEdit'], 'true');
        $this->assertEquals($this->vars['isParticipantAdd'], 'true');
        $this->assertEquals($this->vars['hasScriptActive'], '0');
        $this->assertEquals($this->vars['hasScriptDraft'], '0');
    }

    
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
        $this->assertEquals($this->vars['isScriptEdit'], 'true');
        $this->assertEquals($this->vars['isParticipantAdd'], 'true');
        $this->assertEquals($this->vars['hasScriptActive'], '0');
        $this->assertEquals($this->vars['hasScriptDraft'], '1');
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
        $this->assertEquals($this->vars['isScriptEdit'], 'true');
        $this->assertEquals($this->vars['isParticipantAdd'], 'true');
        $this->assertEquals($this->vars['hasScriptActive'], '0');
        $this->assertEquals($this->vars['hasScriptDraft'], '1');
    }

}
