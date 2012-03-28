<?php
App::uses('ScriptsController', 'Controller');
App::uses('Program', 'Model');
App::uses('ProgramSetting', 'Model');

/**
 * TestScriptsControllerController *
 */
class TestScriptsController extends ScriptsController
{

    public $autoRender = false;


    public function redirect($url, $status = null, $exit = true)
    {
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
                    'url' => 'testurl',
                    'database' => 'testdbprogram'
                    )
                )
            );

    var $otherProgramData = array(
            0 => array( 
                'Program' => array(
                    'name' => 'Test Name 2',
                    'url' => 'testurl2',
                    'database' => 'testdbprogram2'
                    )
                )
            );
  
    
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


    protected function instanciateScriptMultiModel($databaseName)
    {
        return new Script(array('database' => $databaseName));
    }

    
    public function tearDown()
    {
        
        $this->dropData();
        
        unset($this->Scripts);

        parent::tearDown();
    }


    protected function mockProgramAccess()
    {
        $scripts = $this->generate(
            'Scripts', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read')
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array()
                    ),
                )
            );
        
        $scripts->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
        
        $scripts->Program
            ->expects($this->any())
            ->method('find')
            ->will(
                $this->onConsecutiveCalls(
                    $this->programData, 
                    array(
                        $this->programData[0],
                        $this->otherProgramData[0])
                    )
                );
            
        $scripts->Session
            ->expects($this->any())
            ->method('read')
            ->will(
                $this->onConsecutiveCalls(
                    '4',
                    '2',
                    $this->programData[0]['Program']['database'], 
                    $this->programData[0]['Program']['database'],
                    $this->programData[0]['Program']['name']
                    )
                );
    
        return $scripts;
    }


    protected function getOneScript($keyword)
    {
        $script['Script'] = array(
            'script' => array(
                'dialogues' => array(
                    array(
                        'dialogue-id'=> 'script.dialogues[0]',
                        'interactions'=> array(
                            array(
                                'type-interaction' => 'question-answer', 
                                'content' => 'how are you', 
                                'keyword' => $keyword, 
                                'interaction-id' => 'script.dialogues[0].interactions[0]'
                                )
                            )
                        )
                    )
                )
            );

        return $script;
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
        
        $this->testAction(
            '/testurl/scripts/add', array(
                'data' => $updateDraft, 
                'method' => 'post'
                )
            );
        
        $this->instanciateScriptModel();
        $currentDraft = $this->Scripts->Script->find('draft');
        $this->assertEquals(count($draft), 1);
        $this->assertEquals($currentDraft[0]['Script']['script']['do'], $updateDraft['script']['do']);
    }


    //TODO  
    public function testEdit() 
    {
    
    }


    //TODO
    public function testDelete() 
    {

    }


    public function testValidateKeyword_UsedInOtherScriptWithSameShortcode()
    {    
        $this->mockProgramAccess();

        $otherProgramScriptModel = $this->instanciateScriptMultiModel('testdbprogram2');
        $otherProgramScriptModel->deleteAll(true, false);

        $otherProgramScriptModel->create();
        $otherProgramScriptModel->save($this->getOneScript('usedKeyword'));
        $otherProgramScriptModel->makeDraftActive();

        $otherProgramSettingModel = new ProgramSetting(array('database' => 'testdbprogram2'));
        $otherProgramSettingModel->deleteAll(true,false);
        $otherProgramSettingModel->create();
        $otherProgramSettingModel->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );
        
        $programSettingModel = new ProgramSetting(array('database' => $this->programData[0]['Program']['database']));
        $programSettingModel->deleteAll(true,false);
        $programSettingModel->create();
        $programSettingModel->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            ); 

        $this->testAction(
            '/testurl/scripts/validateKeyword', array(
                'method' => 'post',
                'data' => array('keyword' => 'usedKeyword')
                )
            );

        $this->assertEquals(0, $this->vars['result']['status']);
        $this->assertEquals('already used by: Test Name 2', $this->vars['result']['message']);

        $otherProgramScriptModel->deleteAll(true, false);
    }


    public function testValidateKeyword_UsedInActiveScriptAndDraft_SameProgram()
    {
        $scripts = $this->generate(
            'Scripts', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read')
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array()
                    ),
                )
            );
        
        $scripts->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
        
        $scripts->Program
            ->expects($this->any())
            ->method('find')
            ->will(
                $this->onConsecutiveCalls(
                    $this->programData, 
                    array(
                        $this->otherProgramData[0])
                    )
                );
            
        $scripts->Session
            ->expects($this->any())
            ->method('read')
            ->will(
                $this->onConsecutiveCalls(
                    '4',
                    '2',
                    $this->programData[0]['Program']['database'], 
                    $this->programData[0]['Program']['database'],
                    $this->programData[0]['Program']['name']
                    )
                );

        $otherProgramScriptModel = $this->instanciateScriptMultiModel('testdbprogram');
        $otherProgramScriptModel->deleteAll(true, false);

        $otherProgramScriptModel->create();
        $otherProgramScriptModel->save($this->getOneScript('usedKeyword'));
        $otherProgramScriptModel->makeDraftActive();

        $programSettingModel = new ProgramSetting(
        	array('database' => $this->programData[0]['Program']['database']));
        $programSettingModel->deleteAll(true,false);
        $programSettingModel->create();
        $programSettingModel->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );

        $this->testAction(
            '/testurl/scripts/validateKeyword', array(
                'method' => 'post',
                'data' => array('keyword' => 'usedKeyword')
                )
            );

        $this->assertEquals(1, $this->vars['result']['status']);
    }


    public function testValidateKeyword_UsedInOtherScriptWithDifferentShortcode()
    {
        $this->mockProgramAccess();
    	            
        $otherProgramScriptModel = $this->instanciateScriptMultiModel('testdbprogram2');
        $otherProgramScriptModel->deleteAll(true, false);
        $otherProgramScriptModel->create();
        $otherProgramScriptModel->save($this->getOneScript('usedKeyword'));
        $otherProgramScriptModel->makeDraftActive();

        $otherProgramSettingModel = new ProgramSetting(array('database' => 'testdbprogram2'));
        $otherProgramSettingModel->deleteAll(true,false);
        $otherProgramSettingModel->create();
        $otherProgramSettingModel->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );
        
        $programSettingModel = new ProgramSetting(array('database' => $this->programData[0]['Program']['database']));
        $programSettingModel->deleteAll(true,false);
        $programSettingModel->create();
        $programSettingModel->save(
            array(
                'key'=>'shortcode',
                'value'=>'8181'
                )
            );


        $this->testAction(
            '/testurl/scripts/validateKeyword', array(
                'method' => 'post',
                'data' => array('keyword' => 'usedKeyword')
                )
            );

        $this->assertEquals(1, $this->vars['result']['status']);
    }


    public function testValidateKeyword_UsedInSameScript()
    {
        $this->mockProgramAccess();

        $otherProgramScriptModel = $this->instanciateScriptMultiModel('testdbprogram');
        $otherProgramScriptModel->deleteAll(true, false);
        $this->instanciateScriptMultiModel('testdbprogram2')->deleteAll(true, false);
        
        $otherProgramScriptModel->create();
        $otherProgramScriptModel->save($this->getOneScript('usedKeyword'));
        $otherProgramScriptModel->makeDraftActive();

        $programSettingModel = new ProgramSetting(array('database' => $this->programData[0]['Program']['database']));
        $programSettingModel->deleteAll(true,false);
        $programSettingModel->create();
        $programSettingModel->save(
            array(
                'key'=>'shortcode',
                'value'=>'8181'
                )
            );

        $this->testAction(
            '/testurl/scripts/validateKeyword', array(
                'method' => 'post',
                'data' => array('keyword' => 'usedKeyword')
                )
            );

        $this->assertEquals(0, $this->vars['result']['status']);
        $this->assertEquals('already used by: Test Name', $this->vars['result']['message']);
    }


    public function testValidateKeyword_notUsed()
    {
        $this->mockProgramAccess();

        $otherProgramScriptModel = $this->instanciateScriptMultiModel('testdbprogram');
        $otherProgramScriptModel->deleteAll(true, false);

        $otherProgramScriptModel->create();
        $otherProgramScriptModel->save($this->getOneScript('usedKeyword'));
        $otherProgramScriptModel->makeDraftActive();

        $this->testAction(
            '/testurl/scripts/validateKeyword', array(
                'method' => 'post',
                'data' => array('keyword' => 'usingAnOtherKeyword')
                )
            );

        $this->assertEquals(1, $this->vars['result']['status']);
    }


    public function testValidateKeyword_usedInDeactivedScript()
    {
        $this->mockProgramAccess();

        $otherProgramScriptModel = $this->instanciateScriptMultiModel('testdbprogram2');
        $otherProgramScriptModel->deleteAll(true, false);

        $otherProgramScriptModel->create();
        $otherProgramScriptModel->save($this->getOneScript('usedKeyword'));
        $otherProgramScriptModel->makeDraftActive();

        $otherProgramScriptModel->create();
        $otherProgramScriptModel->save($this->getOneScript('anotherKeyword'));
        $otherProgramScriptModel->makeDraftActive();
        
        $this->testAction(
            '/testurl/scripts/validateKeyword', array(
                'method' => 'post',
                'data' => array('keyword' => 'usedKeyword')
                )
            );

        $this->assertEquals(1, $this->vars['result']['status']);
    }


}
