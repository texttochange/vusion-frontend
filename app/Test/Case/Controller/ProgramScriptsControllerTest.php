<?php
App::uses('ProgramScriptsController', 'Controller');
App::uses('Program', 'Model');
App::uses('ProgramSetting', 'Model');

/**
 * TestProgramScriptsControllerController *
 */
class TestProgramScriptsController extends ProgramScriptsController
{

    public $autoRender = false;


    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }


}

/**
 * ProgramScriptsController Test Case
 *
 */
class ProgramScriptsControllerTestCase extends ControllerTestCase
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

        $this->Scripts = new TestProgramScriptsController();
        ClassRegistry::config(array('ds' => 'test'));
        
        $this->externalModels = array();
        
        $this->dropData();
        
    }


    protected function dropData()
    {
        //As this model is created on the fly, need to instantiate again
        $this->instanciateModels();
        $this->Scripts->Script->deleteAll(true, false);
        $this->Scripts->ProgramSetting->deleteAll(true, false);
        
        foreach ($this->externalModels as $model) {
            $model->deleteAll(true, false);
        }
        
    }

    
    protected function instanciateModels()
    {
        $options = array('database' => $this->programData[0]['Program']['database']);

        $this->Scripts->Script         = new Script($options);
        $this->Scripts->ProgramSetting = new ProgramSetting($options);
    }


    protected function instanciateExternalModels($databaseName)
    {
        $this->externalModels['script']         = new Script(array('database' => $databaseName));
        $this->externalModels['programSetting'] = new ProgramSetting(array('database' => $databaseName));
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
            'ProgramScripts', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read', 'setFlash'),
                    'Auth' => array()
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array()
                    ),
                'methods' => array(
                    '_notifyUpdateBackendWorker',
                    '_notifySendAllMessagesBackendWorker'
                    )
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
                    $this->programData[0]['Program']['name'],
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
            
        $draft = array(
            'script' => array(
                'do' => 'something'
                )
            );
        $this->instanciateModels();
        $this->Scripts->Script->create();
        $this->Scripts->Script->save($draft);
        
        $this->testAction("/testurl/scripts", array('method' => 'get'));
        $this->assertEquals($draft['script'], $this->vars['script']['script']);
    }


    public function testView()
    {
        $this->mockProgramAccess();
        
        $draft = array(
            'script' => array(
                'do' => 'something'
                )
            );
        $this->instanciateModels();
        $this->Scripts->Script->create();
        $this->Scripts->Script->save($draft);
        
        $this->testAction('/testurl/scripts/draft', array('method' => 'get'));
        $this->assertEquals($draft['script'], $this->vars['script']['script']);
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
        
        $this->instanciateModels();
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
        
        $this->instanciateExternalModels('testdbprogram2');

        $this->externalModels['script']->create();    
        $this->externalModels['script']->save($this->getOneScript('usedKeyword'));
        $this->externalModels['script']->makeDraftActive();

        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );
        
        $this->Scripts->ProgramSetting->create();
        $this->Scripts->ProgramSetting->save(
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
        
        $this->assertEquals('fail', $this->vars['result']['status']);
        $this->assertEquals('usedKeyword already used by: Test Name 2', $this->vars['result']['message']);
              
    }


    public function testValidateKeyword_UsedInActiveScriptAndDraft_SameProgram()
    {
        $scripts = $this->generate(
            'ProgramScripts', array(
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

        $this->Scripts->Script->create();
        $this->Scripts->Script->save($this->getOneScript('usedKeyword'));
        $this->Scripts->Script->makeDraftActive();

        $this->Scripts->ProgramSetting->create();
        $this->Scripts->ProgramSetting->save(
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

        $this->assertEquals('ok', $this->vars['result']['status']);
    }


    public function testValidateKeyword_UsedInOtherScriptWithDifferentShortcode()
    {
        $this->mockProgramAccess();
        
        $this->instanciateExternalModels('testdbprogram2');

        $this->externalModels['script']->create();
        $this->externalModels['script']->save($this->getOneScript('usedKeyword'));
        $this->externalModels['script']->makeDraftActive();

        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );
        
        $this->Scripts->ProgramSetting->create();
        $this->Scripts->ProgramSetting->save(
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

        $this->assertEquals('ok', $this->vars['result']['status']);
    }


    public function testValidateKeyword_UsedInSameScript()
    {
        $this->mockProgramAccess();

        $this->Scripts->Script->create();
        $this->Scripts->Script->save($this->getOneScript('usedKeyword'));
        $this->Scripts->Script->makeDraftActive();

        $this->Scripts->ProgramSetting->create();
        $this->Scripts->ProgramSetting->save(
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

        $this->assertEquals('fail', $this->vars['result']['status']);
        $this->assertEquals('usedKeyword already used by: Test Name', $this->vars['result']['message']);
    }


    public function testValidateKeyword_notUsed()
    {
        $this->mockProgramAccess();

        $this->Scripts->Script->create();
        $this->Scripts->Script->save($this->getOneScript('usedKeyword'));
        $this->Scripts->Script->makeDraftActive();
        
        $this->Scripts->ProgramSetting->create();
        $this->Scripts->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8181'
                )
            );

        $this->testAction(
            '/testurl/scripts/validateKeyword', array(
                'method' => 'post',
                'data' => array('keyword' => 'usingAnOtherKeyword')
                )
            );

        $this->assertEquals('ok', $this->vars['result']['status']);
    }


    public function testValidateKeyword_usedInDeactivedScript()
    {
        $this->mockProgramAccess();

        $this->instanciateExternalModels('testdbprogram2');

        $this->externalModels['script']->create();
        $this->externalModels['script']->save($this->getOneScript('usedKeyword'));
        $this->externalModels['script']->makeDraftActive();

        $this->externalModels['script']->create();
        $this->externalModels['script']->save($this->getOneScript('anotherKeyword'));
        $this->externalModels['script']->makeDraftActive();
        
        $this->Scripts->ProgramSetting->create();
        $this->Scripts->ProgramSetting->save(
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

        $this->assertEquals('ok', $this->vars['result']['status']);
    }


    public function testActivateDraft()
    {
        $scripts = $this->mockProgramAccess();
        $scripts
            ->expects($this->once())
            ->method('_notifyUpdateBackendWorker')
            ->will($this->returnValue(true));
            
        $this->Scripts->ProgramSetting->create();
        $this->Scripts->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );
        $this->Scripts->ProgramSetting->create();
        $this->Scripts->ProgramSetting->save(
            array(
                'key'=>'timezone',
                'value'=>'Africa/Kampala'
                )
            );

        $this->testAction('/testurl/scripts/activateDraft');       
    }
    
    
    public function testActivateDraft_failSomeProgramSettingsMissing()
    {
        $scripts = $this->mockProgramAccess();      
        
        $scripts->Session
            ->expects($this->once())
            ->method('setFlash')
            ->with('Please set the program settings then try again.');
                    
        $this->Scripts->ProgramSetting->create();
        $this->Scripts->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );

        $this->testAction('/testurl/scripts/activateDraft'); 
    }


    public function testTestSendAllMessage()
    {
        $scripts = $this->mockProgramAccess();
        $scripts
            ->expects($this->once())
            ->method('_notifySendAllMessagesBackendWorker')
            ->will($this->returnValue(true));
            
        $this->Scripts->ProgramSetting->create();
        $this->Scripts->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );
        $this->Scripts->ProgramSetting->create();
        $this->Scripts->ProgramSetting->save(
            array(
                'key'=>'timezone',
                'value'=>'Africa/Kampala'
                )
            );

        $this->Scripts->Script->create();
        $this->Scripts->Script->save($this->getOneScript('usedKeyword'));
        $this->Scripts->Script->makeDraftActive();
        $this->Scripts->Script->create();
        $this->Scripts->Script->save($this->getOneScript('usedKeyword'));

        $this->testAction(
            '/testurl/scripts/testSendAllMessages',
            array(
                'method' => 'post',
                'data' => array(
                    'SendAllMessages' => array(
                        'script-id'=> 'whatever',
                        'phone-number' => '06'
                        )
                    )
                )
            );

        $this->assertEquals(2, count($this->vars['scripts']));
    }


}
