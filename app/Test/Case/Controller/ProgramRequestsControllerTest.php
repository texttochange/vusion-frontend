<?php
App::uses('ProgramRequestsController', 'Controller');
App::uses('Request', 'Model');
App::uses('ScriptMaker', 'Lib');
App::uses('Dialogue', 'Model');
App::uses('ProgramSetting', 'Model');


class TestProgramRequestsController extends ProgramRequestsController
{
    
    public $autoRender = false;
    
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
}


class ProgramRequestsControllerTestCase extends ControllerTestCase
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
  
  
    public function setUp()
    {
        parent::setUp();
        
        $this->Requests = new TestProgramRequestsController();
        ClassRegistry::config(array('ds' => 'test'));               
        
        $this->Maker = new ScriptMaker();
        
        //$this->externalModels = array();
    }
    
    
    protected function dropData()
    {
        //As this model is created on the fly, need to instantiate again
        $this->instanciateModels();
        $this->Request->deleteAll(true, false);
        $this->Dialogue->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
        
        $this->instanciateExternalModels('testdbprogram2');
        foreach ($this->externalModels as $name=>$model) {
            $model->deleteAll(true, false);
        }
    }
    
    
    protected function instanciateModels()
    {
        $options = array('database' => $this->programData[0]['Program']['database']);
        
        $this->Request        = new Request($options);
        $this->Dialogue       = new Dialogue($options);
        $this->ProgramSetting = new ProgramSetting($options);
    }
    

    protected function setupProgramSettings($shortcode, $timezone)
    {
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key' => 'shortcode',
                'value' => $shortcode));
        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key' => 'timezone',
                'value' => $timezone));
    }

 
    protected function instanciateExternalModels($databaseName)
    {
        $this->externalModels['request']        = new Request(array('database' => $databaseName)); 
        $this->externalModels['dialogue']       = new Dialogue(array('database' => $databaseName));
        $this->externalModels['programSetting'] = new ProgramSetting(array('database' => $databaseName));
    }
    
    
    public function tearDown()
    {
        
        $this->dropData();
        
        unset($this->Requests);
        
        parent::tearDown();
    }
    
    public function mockProgramAccess()
    {
        $requests = $this->mockProgramAccess_withoutProgram();
        
        $requests->Program
        ->expects($this->any())
        ->method('find')
        ->will($this->returnValue($this->programData));
        
        return $requests;
    }
    
    public function mockProgramAccess_withoutProgram()
    {
        $requests = $this->generate(
            'ProgramRequests', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read', 'setFlash'),
                    'Auth' => array(),
                    'RequestHandler' => array(),
                    'Keyword' => array('areKeywordsUsedByOtherPrograms')
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array()
                    ),
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    '_notifyReloadRequest',
                    )
                )
            );
        
        $requests->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $requests->Session
        ->expects($this->any())
        ->method('read')
        ->will(
            $this->returnValue($this->programData[0]['Program']['database'])
            );
        
        return $requests;
    }


    public function testSave_ok()
    {
        $requests = $this->mockProgramAccess();
        $requests
        ->expects($this->once())
        ->method('_notifyReloadRequest')
        ->with('testurl',  $this->matchesRegularExpression('/^.{24}$/'))
        ->will($this->returnValue(true));
        $requests->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('keyword'))
        ->will($this->returnValue(array()));
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');
  
        $request = $this->Maker->getOneRequest();
        
        $this->testAction(
            "testurl/programRequests/save.json",
            array(
                'method' => 'post',
                'data' => $request));
    }


    public function testSave_fail_noProgramSettings()
    {
        $requests = $this->mockProgramAccess();
        
        $request = $this->Maker->getOneRequest();
        
        $this->testAction(
            "testurl/programRequests/save.json",
            array(
                'method' => 'post',
                'data' => $request));

         $this->assertFalse($this->vars['requestSuccess']);
    }
    
   
    public function testSave_edit_ok()
    {
        $requests = $this->mockProgramAccess();
        $requests->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('otherkeyword'))
        ->will($this->returnValue(array()));
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');    

        $request = $this->Maker->getOneRequest('KEYWORD');
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $savedRequest['Request']['keyword'] = 'OTHERKEYWORD';
        
        $requests
        ->expects($this->once())
        ->method('_notifyReloadRequest')
        ->with('testurl', $savedRequest['Request']['_id'])
        ->will($this->returnValue(true));

        $this->testAction(
            "testurl/programRequests/save.json",
            array(
                'method' => 'POST',
                'data' => $savedRequest
                )
            );
        
        $this->assertEquals('OTHERKEYWORD', $requests->data['Request']['keyword']);
    }


    public function testSave_edit_fail_validationKeyword()
    {
        $requests = $this->mockProgramAccess();
        $requests->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('keyword'))
        ->will($this->returnValue(array()));
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');    

        #First request
        $request = $this->Maker->getOneRequest('KEYWORD 1');
        $this->Request->create();
        $this->Request->save($request);

        #Second request
        $request = $this->Maker->getOneRequest('KEYWORD 2');
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $savedRequest['Request']['keyword'] = 'KEYWORD 1';
        
        $this->testAction(
            "testurl/programRequests/save.json",
            array(
                'method' => 'POST',
                'data' => $savedRequest));
        $this->assertFalse($this->vars['requestSuccess']);
    }
   

    public function testSave_fail_validationKeyword_numeric()
    {
        $requests = $this->mockProgramAccess();
        $requests->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('11'))
        ->will($this->returnValue(array()));
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');    

        #First request
        $request = $this->Maker->getOneRequest('11');
        $this->Request->create();
        $this->Request->save($request);

        #Second request
        $request = $this->Maker->getOneRequest('11');
        
        $this->testAction(
            "testurl/programRequests/save.json",
            array(
                'method' => 'POST',
                'data' => $request));
        $this->assertFalse($this->vars['requestSuccess']);
    }

    
    public function testDelete()
    {
        $requests = $this->mockProgramAccess();
        $requests->Session
        ->expects($this->once())
        ->method('setFlash')
        ->with('The request has been deleted.');
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');    

        $request = $this->Maker->getOneRequest();
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        
        $requests
        ->expects($this->once())
        ->method('_notifyReloadRequest')
        ->with('testurl',  $savedRequest['Request']['_id'])
        ->will($this->returnValue(true));

        $this->testAction("testurl/programRequests/delete/" . $savedRequest['Request']['_id']);     
    }
   

    public function testValidateKeyword_fail_sameProgram_dialogueUse()
    {
        $requests = $this->mockProgramAccess();
        $requests->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('keyword'))
        ->will($this->returnValue(array()));

        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');    

        $dialogue = $this->Maker->getOneDialogue();        
        $saveDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeActive($saveDialogue['Dialogue']['_id']);
        
        $this->testAction(
            "testurl/programRequests/validateKeyword.json",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'keyword request', 'object-id' =>'')));
        $this->assertFalse($this->vars['requestSuccess']);
    }
    
    
    public function testValidateKeyword_fail_sameProgram_differentRequest()
    {
        $requests = $this->mockProgramAccess();
        $requests->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('keyword'))
        ->will($this->returnValue(array()));

        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala'); 

        $request = $this->Maker->getOneRequest('keyword request');
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
                
        $this->testAction(
            "testurl/programRequests/validateKeyword.json",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'keyword request', 'object-id' =>'')));

        $this->assertFalse($this->vars['requestSuccess']);
        $this->assertEquals(
            "'keyword request' already used in Request 'keyword request' of the same program.", 
            $this->vars['foundMessage']);
    }


    public function testValidateKeyword_fail_sameProgram_differentRequest_numeric()
    {
        $requests = $this->mockProgramAccess();
        $requests->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('11'))
        ->will($this->returnValue(array()));

        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala'); 

        $request = $this->Maker->getOneRequest('11');
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
                
        $this->testAction(
            "testurl/programRequests/validateKeyword.json",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'11', 'object-id' =>'')));

        $this->assertFalse($this->vars['requestSuccess']);
        $this->assertEquals(
            "'11' already used in Request '11' of the same program.", 
            $this->vars['foundMessage']);
    }


    public function testValidateKeyword_ok_sameProgram_sameRequest()
    {
        $requests = $this->mockProgramAccess();
        $requests->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('otherkeyword'))
        ->will($this->returnValue(array()));

        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');     

        $request = $this->Maker->getOneRequest('otherkeyword request');
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
     
        $this->testAction(
            "testurl/programRequests/validateKeyword.json",
            array(
                'method' => 'post',
                'data' => array (
                    'keyword'=>'otherkeyword request',
                    'object-id' => $savedRequest['Request']['_id'].'')));
        
        $this->assertTrue($this->vars['requestSuccess']);
    }
    

    public function testValidateKeyword_ok()
    {
        $requests = $this->mockProgramAccess();
        $requests->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('otherkeyword'))
        ->will($this->returnValue(array()));

        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala'); 
                
        $this->testAction(
            "testurl/programRequests/validateKeyword.json",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'otherkeyword', 'object-id' => null)));

        $this->assertTrue($this->vars['requestSuccess']);        
    }

   
    public function testValidateKeyword_fail_otherProgramUseDialogue()
    {        
        $requests = $this->mockProgramAccess();
        $requests->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('otherkeyword'))
        ->will($this->returnValue(array(
            'otherkeyword' => array(
                'program-db' => 'm6h',
                'program-name' => 'other program', 
                'by-type' => 'Request'))));
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala'); 
        
        $this->testAction(
            "testurl/programRequests/validateKeyword.json",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'otherkeyword stuff', 'object-id' => null)));
        $this->assertFalse($this->vars['requestSuccess']);
        $this->assertEquals(
            "'otherkeyword' already used by a Request of program 'other program'.", 
            $this->vars['foundMessage']);
    }


    public function testValidationKeyword_fail_otherProgramUserRequest()
    {        
        $requests = $this->mockProgramAccess();
        $requests->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('otherkeyword'))
        ->will($this->returnValue(array(
            'otherkeyword' => array(
                'program-db' => 'm6h',
                'program-name' => 'other program', 
                'by-type' => 'Dialogue'))));
    
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');

        $this->testAction(
            "testurl/programRequests/validateKeyword.json",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'otherkeyword', 'object-id' => null)));
        $this->assertFalse($this->vars['requestSuccess']);
        $this->assertEquals(
            "'otherkeyword' already used by a Dialogue of program 'other program'.", 
            $this->vars['foundMessage']);
    }

  
}
