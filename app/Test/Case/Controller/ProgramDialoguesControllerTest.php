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
                'database' => 'testdbprogram',
                'status' => 'running'
                )
            )
        );
    
    var $otherProgramData = array(
        0 => array( 
            'Program' => array(
                'name' => 'Test Name 2',
                'url' => 'testurl2',
                'database' => 'testdbprogram2',
                'status' => 'running'
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
        //ClassRegistry::config(array('ds' => 'test'));
        
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
        //$options = array('database' => $this->programData[0]['Program']['database']);
        $dbName = $this->programData[0]['Program']['database'];
        
        $this->Dialogue = ProgramSpecificMongoModel::init(
            'Dialogue', $dbName, true);
        $this->ProgramSetting = ProgramSpecificMongoModel::init(
            'ProgramSetting', $dbName, true);
        $this->Request = ProgramSpecificMongoModel::init(
            'Request', $dbName, true);
        $this->Participant    = ProgramSpecificMongoModel::init(
            'Participant', $dbName, true);
    }
    
    protected function setupProgramSettings($shortcode, $timezone)
    {
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key' => 'shortcode',
                'value' => $shortcode
                )
            );
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key' => 'timezone',
                'value' => $timezone
                )
            );
    }
    
    protected function instanciateExternalModels($dbName)
    {
        /*$this->externalModels['dialogue']       = new Dialogue(array('database' => $databaseName));
        $this->externalModels['programSetting'] = new ProgramSetting(array('database' => $databaseName));
        $this->externalModels['request']        = new Request(array('database' => $databaseName));*/
        $this->externalModels['dialogue'] = ProgramSpecificMongoModel::init(
            'Dialogue', $dbName, true);
        $this->externalModels['programSetting'] = ProgramSpecificMongoModel::init(
            'ProgramSetting', $dbName, true);
        $this->externalModels['request'] = ProgramSpecificMongoModel::init(
            'Request', $dbName, true);
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
                    'Auth' => array('loggedIn', 'startup'),
                    'RequestHandler' => array(),
                    'Keyword' => array('areKeywordsUsedByOtherPrograms')
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array(),
                    'User' => array()
                    ),
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
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
        
        $dialogues->Auth
        ->expects($this->any())
        ->method('loggedIn')
        ->will($this->returnValue(true));

        $dialogues->Session
        ->expects($this->any())
        ->method('read')
        ->will($this->returnValue($this->programData[0]['Program']['database']));
        
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
    
    
    public function testSave()
    {
        $dialogues = $this->mockProgramAccess();
        $dialogues->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('keyword'))
        ->will($this->returnValue(array()));
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');        

        $dialogue = $this->Maker->getOneDialogue();
        $this->testAction(
            "/testurl/programDialogues/save.json", 
            array(
                'method' => 'post',
                'data' => $dialogue
                )
            );
        $this->assertTrue($this->vars['requestSuccess']);
        $this->assertTrue(isset($this->vars['dialogueObjectId']));        
    }
    
    
    public function testSave_fail_modelValidation()
    {
        $dialogues = $this->mockProgramAccess();       
        $dialogues->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('keyword'))
        ->will($this->returnValue(array()));

        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');        

        $dialogue = $this->Maker->getOneDialogue('keyword');
        unset($dialogue['Dialogue']['interactions'][0]['type-schedule']);
        
        $this->testAction(
            "/testurl/programDialogues/save.json", 
            array(
                'method' => 'post',
                'data' => $dialogue
                )
            );
        $this->assertFalse($this->vars['requestSuccess']);
    }


    public function testSave_fail_keywordUsedInOtherProgramDialogue()
    {    
        $dialogues = $this->mockProgramAccess();
        $dialogues->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('usedkeyword'))
        ->will($this->returnValue(array(
            'usedkeyword' => array(
                'program-db' => 'm6h',
                'program-name' => 'other program', 
                'by-type' => 'dialogue'))));

        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');        

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');

        $result = $this->testAction(
            '/testurl/programDialogue/save.json', array(
                'method' => 'post',
                'data' => $dialogue));
        
        $this->assertFalse($this->vars['requestSuccess']);
    }

    
    public function testSave_missingProgramSettings()
    {
        $dialogues = $this->mockProgramAccess();
        $dialogues->Session
            ->expects($this->once())
            ->method('setFlash')
            ->with('Please set the program settings then try again.');

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');
        
        $this->testAction(
            '/testurl/programDialogue/save.json', array(
                'method' => 'post',
                'data' => $dialogue));
        $this->assertFalse($this->vars['requestSuccess']);
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
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');        
        
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
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');        
        
        $testDialogue = $this->Maker->getOneDialogue();
        $testDialogue['Dialogue']['auto-enrollment'] = 'all';
        $savedDialogue = $this->Dialogue->saveDialogue($testDialogue);        

        $participant = array(
            'phone' => '+8',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        
        $this->testAction('/testurl/programDialogues/activate/'.$savedDialogue['Dialogue']['_id']);
   
    }
    
    
    public function testValidateKeyword_fail_usedInOtherProgramDialogue()
    {    
        $dialogues = $this->mockProgramAccess();
        $dialogues->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('usedkeyword'))
        ->will($this->returnValue(array(
            'usedkeyword' => array(
                'program-db' => 'm6h',
                'program-name' => 'other program', 
                'by-type' => 'Dialogue'))));
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');

        $this->testAction(
            '/testurl/scripts/validateKeyword.json', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'usedKeyword',
                    'dialogue-id' => '')
                )
            );
        
        $this->assertFalse($this->vars['requestSuccess']);
        $this->assertEquals(
            "'usedkeyword' already used by a Dialogue of program 'other program'.",
            $this->vars['foundMessage']);
        
    }
    
    
    public function testValidateKeyword_fail_usedInOtherProgramRequest()
    {    
        $dialogues = $this->mockProgramAccess();
        $dialogues->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('usedkeyword'))
        ->will($this->returnValue(array(
            'usedkeyword' => array(
                'program-db' => 'm6h',
                'program-name' => 'other program',
                'by-type' => 'Request'))));
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala'); 

        $this->testAction(
            '/testurl/scripts/validateKeyword.json', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'usedKeyword',
                    'dialogue-id' => '')
                )
            );
        
        $this->assertFalse($this->vars['requestSuccess']);
        $this->assertEquals(
            "'usedkeyword' already used by a Request of program 'other program'.",
            $this->vars['foundMessage']);
        
    }
    
    
    public function testValidateKeyword_ok_usedInOtherProgram_withDifferentShortcode()
    {
        $dialogues = $this->mockProgramAccess();
        $dialogues->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('usedkeyword'))
        ->will($this->returnValue(array()));
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');

        $this->testAction(
            '/testurl/programDialogues/validateKeyword.json', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'usedKeyword',
                    'dialogue-id' => '')
                )
            );
        
        $this->assertTrue($this->vars['requestSuccess']);
    }
    

    public function testValidateKeyword_ok_usedSameProgram_sameDialogue()
    {
        $dialogues = $this->mockProgramAccess();
        $dialogues->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('usedkeyword'))
        ->will($this->returnValue(array()));
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
                
        $this->testAction(
            '/testurl/programDialogues/validateKeyword.json', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'usedKeyword',
                    'dialogue-id' => $savedDialogue['Dialogue']['dialogue-id'])));
 
        $this->assertTrue($this->vars['requestSuccess']);
    }
  
    
    public function testValidateKeyword_fail_usedSameProgram_differentDialogue()
    {
        $dialogues = $this->mockProgramAccess();
        $dialogues->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('usedkeyword'))
        ->will($this->returnValue(array()));

        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');        
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        
        $this->testAction(
            '/testurl/programDialogues/validateKeyword.json', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'usedKeyword',
                    'dialogue-id'=> '')));
        
        $this->assertFalse($this->vars['requestSuccess']);
        $this->assertEquals(
            "'usedkeyword' already used in Dialogue 'my dialogue' of the same program.",
            $this->vars['foundMessage']);
    }
    
  
    public function testValidateKeyword_fail_usedSameProgram_request()
    {
        $dialogues = $this->mockProgramAccess(); 
        $dialogues->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('keyword'))
        ->will($this->returnValue(array()));
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');

        $this->Request->create();
        $this->Request->save($this->Maker->getOneRequest());
        
        $this->testAction(
            '/testurl/programDialogues/validateKeyword.json', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'KEYWORD',
                    'dialogue-id'=>'')));
        
        $this->assertFalse($this->vars['requestSuccess']);
        $this->assertEquals(
            "'keyword' already used in Request 'KEYWORD request' of the same program.",
            $this->vars['foundMessage']);
    }
    
    
    public function testValidateKeyword_ok_notUsed()
    {
        $dialogues = $this->mockProgramAccess();
        $dialogues->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('usinganotherkeyword'))
        ->will($this->returnValue(array()));        

        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        
        $this->testAction(
            '/testurl/scripts/validateKeyword.json', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'usingAnOtherKeyword',
                    'dialogue-id' => '')
                )
            );
        
        $this->assertTrue($this->vars['requestSuccess']);
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
        $dialogues->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('usedkeyword'))
        ->will($this->returnValue(array()));
  
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');
        
        $dialogue = $this->Maker->getOneDialogue('usedKeyword');
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        $newDialogue = $this->Maker->getOneDialogue('anotherKeyword');
        $newDialogue['Dialogue']['dialogue-id'] = $savedDialogue['Dialogue']['dialogue-id'];
        $newDialogue['Dialogue']['name'] = "my newer dialogue";
        $savedDialogue = $this->Dialogue->saveDialogue($newDialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        
        $this->testAction(
            '/testurl/scripts/validateKeyword.json', array(
                'method' => 'post',
                'data' => array(
                    'keyword' => 'usedKeyword',
                    'dialogue-id' => '')
                )
            );
        $this->assertTrue($this->vars['requestSuccess']);
    }    
    
    
    public function testTestSendAllMessages()
    {
        $dialogues = $this->mockProgramAccess();
        $dialogues
        ->expects($this->once())
        ->method('_notifySendAllMessagesBackendWorker')
        ->with('testurl')
        ->will($this->returnValue(true));
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');
        $saveDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($saveDialogue['Dialogue']['dialogue-id']);
                
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

        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');
        $dialogue = $this->Maker->getOneDialogue('usedKeyword');
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        
        $this->testAction('/testurl/programDialogues/delete/'.$savedDialogue['Dialogue']['dialogue-id']);
        
        $this->assertEqual(0, $this->Dialogue->find('count'));
    }

    
}
