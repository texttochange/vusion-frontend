<?php
App::uses('ProgramDialoguesController', 'Controller');

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
                
    }


    protected function dropData()
    {
        //As this model is created on the fly, need to instantiate again
        $this->instanciateModels();
        $this->Dialogue->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
        
        foreach ($this->externalModels as $model) {
            $model->deleteAll(true, false);
        }
        
    }

    
    protected function instanciateModels()
    {
        $options = array('database' => $this->programData[0]['Program']['database']);

        $this->Dialogue       = new Dialogue($options);
        $this->ProgramSetting = new ProgramSetting($options);
    }


    protected function instanciateExternalModels($databaseName)
    {
        $this->externalModels['dialogue']       = new Dialogue(array('database' => $databaseName));
        $this->externalModels['programSetting'] = new ProgramSetting(array('database' => $databaseName));
    }

    
    public function tearDown()
    {
        
        $this->dropData();
        
        unset($this->Dialogues);

        parent::tearDown();
    }

    
    protected function getOneDialogue($keyword)
    {
        $dialogue['Dialogue'] = array(
            'name' => 'my dialogue',
            'dialogue' => array(
                array(       
                    'interactions'=> array(
                        array(
                            'type-interaction' => 'question-answer', 
                            'content' => 'how are you', 
                            'keyword' => $keyword, 
                            )
                        )
                    )
                )
            );

        return $dialogue;
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
                    '_notifySendAllMessagesBackendWorker'
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
        $dialogue = array(
            'dialogue' => array(
                'do' => 'something',
                )
            );

        $this->mockProgramAccess();
        $this->testAction(
            "/testurl/programDialogues/save", 
            array(
                'method' => 'post',
                'data' => $dialogue
                )
            );
        $this->assertEquals('ok', $this->vars['result']['status']);
        $this->assertTrue(isset($this->vars['result']['object-id']));        
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
        $dialogues
            ->expects($this->once())
            ->method('_notifyUpdateBackendWorker')
            ->will($this->returnValue(true));
        $dialogues->Session
            ->expects($this->once())
            ->method('setFlash')
            ->with('Dialogue activated.');  

        $this->instanciateModels();
        $savedDialogue = $this->Dialogue->saveDialogue(
            array('Dialogue' => 
                array('dialogue' => 
                    array('do' => 'something')
                    )
                )
            );
        
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

        $this->testAction('/testurl/programDialogues/activate/'.$savedDialogue['Dialogue']['_id']); 

    }

    
    public function testValidateKeyword_fail_usedInOtherProgram_WithSameShortcode()
    {    
        $this->mockProgramAccess();
        
        $this->instanciateModels();
        $this->instanciateExternalModels('testdbprogram2');

        $savedDialogue = $this->externalModels['dialogue']->saveDialogue($this->getOneDialogue('usedKeyword'));
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
        $this->assertEquals('usedKeyword already used by: Test Name 2', $this->vars['result']['message']);
              
    }
    

    public function testValidateKeyword_ok_usedInOtherProgram_withDifferentShortcode()
    {
        $this->mockProgramAccess();
        
        $this->instanciateModels(); 
        $this->instanciateExternalModels('testdbprogram2');

        $savedDialogue = $this->externalModels['dialogue']->saveDialogue($this->getOneDialogue('usedKeyword'));
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

        $this->instanciateModels();
        $savedDialogue = $this->Dialogue->saveDialogue($this->getOneDialogue('usedKeyword'));
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

        $this->instanciateModels();
        $savedDialogue = $this->Dialogue->saveDialogue($this->getOneDialogue('usedKeyword'));
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
        $this->assertEquals('usedKeyword already used in same program by: '.$savedDialogue['Dialogue']['name'], $this->vars['result']['message']);
    }


    public function testValidateKeyword_ok_notUsed()
    {
        $this->mockProgramAccess();

        $this->instanciateModels();
        $savedDialogue = $this->Dialogue->saveDialogue($this->getOneDialogue('usedKeyword'));
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
                    )
                );
        $this->instanciateExternalModels('testdbprogram2');

        $this->instanciateModels();
        $savedDialogue = $this->Dialogue->saveDialogue($this->getOneDialogue('usedKeyword'));
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);

        $newDialogue = $this->getOneDialogue('anotherKeyword');
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
            ->will($this->returnValue(true));

        $this->instanciateModels();
        $saveDialogue = $this->Dialogue->saveDialogue($this->getOneDialogue('usedKeyword'));
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
                        '_id'=> $saveDialogue['Dialogue']['_id'],
                        'phone-number' => '06'
                        )
                    )
                )
            );
         
         $this->assertEquals(1, count($this->vars['dialogues']));

    }

    

}
