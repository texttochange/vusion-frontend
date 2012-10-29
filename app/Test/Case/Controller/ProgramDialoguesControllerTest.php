<?php
App::uses('ProgramDialoguesController', 'Controller');
App::uses('Dialogue', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Request', 'Model');
App::uses('Participant', 'Model');
App::uses('ScriptMaker', 'Lib');


class TestProgramDialoguesController extends ProgramDialoguesController
{

    public $autoRender = false;


    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }


}


class ProgramDialoguesControllerTestCase extends ControllerTestCase
{

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

        $this->Dialogues = new TestProgramDialoguesController();
        ClassRegistry::config(array('ds' => 'test'));
        
        $this->externalModels = array();
                
        $this->Maker = new ScriptMaker();
    }


    protected function dropData()
    {
        //As this model is created on the fly, need to instantiate again
        $this->instanciateModels();
        $this->Dialogue->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
        $this->Request->deleteAll(true, false);
        $this->Participant->deleteAll(true, false);
        
        foreach ($this->externalModels as $model) {
            $model->deleteAll(true, false);
        }
        
    }

    
    protected function instanciateModels()
    {
        $options = array('database' => $this->programData[0]['Program']['database']);

        $this->Dialogue       = new Dialogue($options);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->Request        = new Request($options);
        $this->Participant    = new Participant($options);
    }


    protected function instanciateExternalModels($databaseName)
    {
        $this->externalModels['dialogue']       = new Dialogue(array('database' => $databaseName));
        $this->externalModels['programSetting'] = new ProgramSetting(array('database' => $databaseName));
        $this->externalModels['request'] = new Request(array('database' => $databaseName));
    }

    
    public function tearDown()
    {
        
        $this->dropData();
        
        unset($this->Dialogues);

        parent::tearDown();
    }


    protected function mockProgramAccess_withoutProgram()
    {
        $dialogues = $this->generate(
            'ProgramDialogues', array(
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
                    '_notifySendAllMessagesBackendWorker',
                    '_notifyUpdateRegisteredKeywords'
                    )
                )
            );
        
        $dialogues->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
                    
        $dialogues->Session
            ->expects($this->any())
            ->method('read')
            ->will(
                $this->onConsecutiveCalls(
                    '4',
                    '2',
                    $this->programData[0]['Program']['database'], 
                    $this->programData[0]['Program']['name'],
                    $this->programData[0]['Program']['name'],
                    'utc',
                    'testdbprogram'
                    )
                );
            
        return $dialogues;

    }

    protected function mockProgramAccess()
    {
        $dialogues = $this->mockProgramAccess_withoutProgram();
        $dialogues->Program
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

        return $dialogues;
    }


    public function testIndex()
    {
        $this->mockProgramAccess();
        $this->testAction("/testurl/programDialogues");
        $this->assertEquals(array(), $this->vars['dialogues']);
    }


    public function testAdd()
    {
        
        $this->mockProgramAccess();

        $dialogue = $this->Maker->getOneDialogue();
        $this->testAction(
            "/testurl/programDialogues/save", 
            array(
                'method' => 'post',
                'data' => $dialogue
                )
            );
        $this->assertEquals('ok', $this->vars['result']['status']);
        $this->assertTrue(isset($this->vars['result']['dialogue-obj-id']));        
    }


    public function testAdd_fail()
    {
        $dialogue = $this->Maker->getOneDialogue();
        unset($dialogue['Dialogue']['interactions'][0]['type-schedule']);
        
        $this->mockProgramAccess();
        $this->testAction(
            "/testurl/programDialogues/save", 
            array(
                'method' => 'post',
                'data' => $dialogue
                )
            );
        $this->assertEqual('fail', $this->vars['result']['status']);
        $this->assertEqual('type-schedule is missing in an Interaction.', $this->vars['result']['message']);        
    }


    public function testActivate_missingProgramSettings()
    {
        $dialogues = $this->mockProgramAccess();
        $dialogues->Session
            ->expects($this->once())
            ->method('setFlash')
            ->with('Please set the program settings then try again.');
        
        $this->testAction('/testurl/programDialogue/activate/wrongId');
    }


    public function testActivate_wrongDialogueId()
    {
        $dialogues = $this->mockProgramAccess();
        $dialogues->Session
            ->expects($this->once())
            ->method('setFlash')
            ->with('Dialogue unknown reload the page and try again.');        
        
        $this->instanciateModels();
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'timezone',
                'value'=>'Africa/Kampala'
                )
            );

        $this->testAction('/testurl/scripts/activate/wrongId'); 
    }


    public function testActivate_ok()
    {
        $dialogues = $this->mockProgramAccess();

        $regexId = $this->matchesRegularExpression('/^.{13}$/');
 
        $dialogues
            ->expects($this->once())
            ->method('_notifyUpdateBackendWorker')
            ->with('testurl', $regexId)
            ->will($this->returnValue(true));
        $dialogues->Session
            ->expects($this->once())
            ->method('setFlash')
            ->with('Dialogue activated.');  

        $this->instanciateModels();
        
        $testDialogue = $this->Maker->getOneDialogue();
        $testDialogue['Dialogue']['auto-enrollment'] = 'all';
        $savedDialogue = $this->Dialogue->saveDialogue($testDialogue);
        
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'timezone',
                'value'=>'Africa/Kampala'
                )
            );
        
        $participant = array(
            'phone' => '+8',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);

        $this->testAction('/testurl/programDialogues/activate/'.$savedDialogue['Dialogue']['_id']);
        
        $enrolledParticipant = $this->Participant->find('first');
        $this->assertEqual(
            $enrolledParticipant['Participant']['enrolled'][0]['dialogue-id'],
            $savedDialogue['Dialogue']['dialogue-id']
        );

    }

    
    public function testValidateKeyword_fail_usedInOtherProgramDialogue()
    {    
        $this->mockProgramAccess();
        
        $this->instanciateModels();
        $this->instanciateExternalModels('testdbprogram2');

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');

        $savedDialogue = $this->externalModels['dialogue']->saveDialogue($dialogue);
        $this->externalModels['dialogue']->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);

        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );
        
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            ); 

        $this->testAction(
            '/testurl/scripts/validateKeyword', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'usedKeyword',
                    'dialogue-id' => '')
                )
            );
        
        $this->assertEquals('fail', $this->vars['result']['status']);
        $this->assertEquals("'usedKeyword' already used by a dialogue of program 'Test Name 2'.", $this->vars['result']['message']);
              
    }


    public function testValidateKeyword_fail_usedInOtherProgramRequest()
    {    
        $this->mockProgramAccess();
        
        $this->instanciateModels();
        $this->instanciateExternalModels('testdbprogram2');

        $this->externalModels['request']->create();
        $this->externalModels['request']->save($this->Maker->getOneRequest());

        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );
        
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            ); 

        $this->testAction(
            '/testurl/scripts/validateKeyword', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'KEYWORD',
                    'dialogue-id' => '')
                )
            );
        
        $this->assertEquals('fail', $this->vars['result']['status']);
        $this->assertEquals("'KEYWORD' already used by a request of program 'Test Name 2'.", $this->vars['result']['message']);
              
    }
    

    public function testValidateKeyword_ok_usedInOtherProgram_withDifferentShortcode()
    {
        $this->mockProgramAccess();
        
        $this->instanciateModels(); 
        $this->instanciateExternalModels('testdbprogram2');

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');

        $savedDialogue = $this->externalModels['dialogue']->saveDialogue($dialogue);
        $this->externalModels['dialogue']->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);

        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );
        
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8181'
                )
            );


        $this->testAction(
            '/testurl/programDialogues/validateKeyword', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'usedKeyword',
                    'dialogue-id' => '')
                )
            );

        $this->assertEquals('ok', $this->vars['result']['status']);
    }


    public function testValidateKeyword_ok_usedSameProgram_sameDialogue()
    {
        $dialogues = $this->mockProgramAccess_withoutProgram();
        $dialogues->Program
            ->expects($this->any())
            ->method('find')
            ->will(
                $this->onConsecutiveCalls(
                    $this->programData, 
                    array(
                        $this->otherProgramData[0])
                    )
                );

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');

        $this->instanciateModels();
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);

        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );

        $this->testAction(
            '/testurl/programDialogues/validateKeyword', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'usedKeyword',
                    'dialogue-id' => $savedDialogue['Dialogue']['dialogue-id'])
                )
            );

        $this->assertEquals('ok', $this->vars['result']['status']);
    }

    
    public function testValidateKeyword_fail_usedSameProgram_differentDialogue()
    {
        $dialogues = $this->mockProgramAccess_withoutProgram();
        $dialogues->Program
            ->expects($this->any())
            ->method('find')
            ->will(
                $this->onConsecutiveCalls(
                    $this->programData, 
                    array(
                        $this->otherProgramData[0])
                    )
                );

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');

        $this->instanciateModels();
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);

        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8181'
                )
            );

        $this->testAction(
            '/testurl/programDialogues/validateKeyword', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'usedKeyword',
                    'dialogue-id'=>''),
                )
            );

        $this->assertEquals('fail', $this->vars['result']['status']);
        $this->assertEquals("'usedKeyword' already used in dialogue 'my dialogue' of the same program.", $this->vars['result']['message']);
    }


    public function testValidateKeyword_fail_usedSameProgram_request()
    {
        $dialogues = $this->mockProgramAccess_withoutProgram();
        $dialogues->Program
            ->expects($this->any())
            ->method('find')
            ->will(
                $this->onConsecutiveCalls(
                    $this->programData, 
                    array(
                        $this->otherProgramData[0])
                    )
                );

        $this->instanciateModels();
        $this->Request->create();
        $this->Request->save($this->Maker->getOneRequest());

        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8181'
                )
            );

        $this->testAction(
            '/testurl/programDialogues/validateKeyword', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'KEYWORD',
                    'dialogue-id'=>''),
                )
            );

        $this->assertEquals('fail', $this->vars['result']['status']);
        $this->assertEquals("'KEYWORD' already used by a request of the same program.", $this->vars['result']['message']);
    }


    public function testValidateKeyword_ok_notUsed()
    {
        $this->mockProgramAccess();

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');

        $this->instanciateModels();
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8181'
                )
            );

        $this->testAction(
            '/testurl/scripts/validateKeyword', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'usingAnOtherKeyword',
                    'dialogue-id' => '')
                )
            );

        $this->assertEquals('ok', $this->vars['result']['status']);
    }


    public function testValidateKeyword__ok_usedSameProgram_oldDifferentDialogue()
    {
        $dialogues = $this->mockProgramAccess_withoutProgram();
        $dialogues->Program
            ->expects($this->any())
            ->method('find')
            ->will(
                $this->onConsecutiveCalls(
                    $this->programData, 
                    array(
                        $this->otherProgramData[0])
                    ));
        $this->instanciateExternalModels('testdbprogram2');

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');

        $this->instanciateModels();
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);

        $newDialogue = $this->Maker->getOneDialogue('anotherKeyword');
        $newDialogue['Dialogue']['dialogue-id'] = $savedDialogue['Dialogue']['dialogue-id'];
        $newDialogue['Dialogue']['name'] = "my newer dialogue";
        $savedDialogue = $this->Dialogue->saveDialogue($newDialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8181'
                )
            );
        
        $this->testAction(
            '/testurl/scripts/validateKeyword', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'usedKeyword',
                    'dialogue-id' => '')
                )
            );
        $this->assertEquals('ok', $this->vars['result']['status']);
    }    


    public function testTestSendAllMessages()
    {
        $dialogues = $this->mockProgramAccess();
        $dialogues
            ->expects($this->once())
            ->method('_notifySendAllMessagesBackendWorker')
            ->with('testurl')
            ->will($this->returnValue(true));

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');

        $this->instanciateModels();
        $saveDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($saveDialogue['Dialogue']['dialogue-id']);
 
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'timezone',
                'value'=>'Africa/Kampala'
                )
            );
       
        $this->testAction(
            '/testurl/programDialogue/testSendAllMessages/',
            array(
                'method' => 'post',
                'data' => array(
                    'SendAllMessages' => array(
                        'dialogue-obj-id'=> $saveDialogue['Dialogue']['_id'],
                        'phone-number' => '06'
                        )
                    )
                )
            );
         
         $this->assertEquals(1, count($this->vars['dialogues']));

    }

    
    public function testDeleteDialogue()
    {        
        $dialogueController = $this->mockProgramAccess();
        $dialogueController
            ->expects($this->once())
            ->method('_notifyUpdateRegisteredKeywords')
            ->with('testurl')
            ->will($this->returnValue(true));

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');

        $this->instanciateModels();
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        
        $this->testAction('/testurl/programDialogues/delete/'.$savedDialogue['Dialogue']['dialogue-id']);

        $this->assertEqual(0, $this->Dialogue->find('count'));
    }


}
