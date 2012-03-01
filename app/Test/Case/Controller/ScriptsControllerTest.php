<?php
App::uses('ScriptsController', 'Controller');
App::uses('Program', 'Model');

/**
 * TestScriptsControllerController *
 */
class TestScriptsController extends ScriptsController 
{
/**
 * Auto render
 *
 * @var boolean
 */
    public $autoRender = false;


/**
 * Redirect action
 *
 * @param mixed $url
 * @param mixed $status
 * @param boolean $exit
 * @return void
 */
    public function redirect($url, $status = null, $exit = true) {
        $this->redirectUrl = $url;
    }


}

/**
 * ScriptsController Test Case
 *
 */
class ScriptsControllerTestCase extends ControllerTestCase
{
/**
 * Data
 *
 */
    
    var $programData = array(
            0 => array( 
                'Program' => array(
                    'name' => 'Test Name',
                    'country' => 'Test Country',
                    'timezone' => 'UTC',
                    'url' => 'testurl',
                    'database' => 'testdbprogram'
                )
            ));
    
/**
 * setUp methods
 *
 */
    public function setUp()
    {
        parent::setUp();

        $this->Scripts = new TestScriptsController();
        ClassRegistry::config(array('ds' => 'test'));
        
        $this->dropData();
        
    }

    protected function dropData()
    {
        //As this model is created on the fly, need to instantiate again
        $this->instanciateScriptModel();
        $this->Scripts->Script->deleteAll(true, false);
    }
    
    protected function instanciateScriptModel()
    {
        $options = array('database' => $this->programData[0]['Program']['database']);
        $this->Scripts->Script = new Script($options);
    }
    
    public function tearDown()
    {
        
        $this->dropData();
        
        unset($this->Scripts);

        parent::tearDown();
    }

    protected function mockProgramAccess()
    {
        $Scripts = $this->generate('Scripts', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
            ),
            'models' => array(
                'Program' => array('find', 'count'),
                'Group' => array()
            ),
        ));
        
        $Scripts->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
        
        $Scripts->Program
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($this->programData));
            
        $Scripts->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls('4','2',$this->programData[0]['Program']['database'], $this->programData[0]['Program']['name']));
    }

/**
 * test methods
 *
 */
    public function testIndex()
    {
        $this->mockProgramAccess();
        
        $this->testAction("/testurl/scripts", array('method' => 'get'));

        $this->assertEquals($this->vars['programName'], $this->programData[0]['Program']['name']);
    }
    
    
    public function testIndex_returnDraft()
    {
        $this->mockProgramAccess();
            
        $draft = array('somescript' => 'do something');
        $this->instanciateScriptModel();
        $this->Scripts->Script->create();
        $this->Scripts->Script->save($draft);
        
        $this->testAction("/testurl/scripts", array('method' => 'get'));
        $this->assertEquals($this->vars['script']['somescript'], $draft['somescript']);
    }


    public function testView()
    {
        $this->mockProgramAccess();
            
        $this->testAction('/testurl/scripts/draft', array('method' => 'get'));
        
    }


    public function testAdd()
    {
            
        $this->mockProgramAccess();
        
        $draft = array(
        	'script' => array(
        		'do' => 'something',
        		)
        	);
        
        $this->testAction('/testurl/scripts/add', array('data' => $draft, 'method' => 'post'));
        
        //For the second testAction, need to remock the models
        $this->mockProgramAccess();
        
        $updateDraft = array(
        	'script' => array(
        		'do' => 'something else',
        		)
        	);
        
        $this->testAction('/testurl/scripts/add', array('data' => $updateDraft, 'method' => 'post'));
        
        $this->instanciateScriptModel();
        $currentDraft = $this->Scripts->Script->find('draft');
        $this->assertEquals(count($draft), 1);
        $this->assertEquals($currentDraft[0]['Script']['script']['do'], $updateDraft['script']['do']);
    }

        
    public function testEdit() 
    {
        

    }


    public function testDelete() 
    {

    }


}
