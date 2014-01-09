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
                    '_notifyUpdateRegisteredKeywords',
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

    
    public function testAdd_ok()
    {
        $requests = $this->mockProgramAccess();
        $requests
        ->expects($this->once())
        ->method('_notifyUpdateRegisteredKeywords')
        ->with('testurl')
        ->will($this->returnValue(true));
        
        $request = $this->Maker->getOneRequest();
        
        $this->testAction(
            "testurl/programRequests/add",
            array(
                'method' => 'post',
                'data' => $request));
    }


    public function testAdd_fail_noProgramSettings()
    {
        $requests = $this->mockProgramAccess();
        
        $request = $this->Maker->getOneRequest();
        
        $this->testAction(
            "testurl/programRequests/add",
            array(
                'method' => 'post',
                'data' => $request));

         $this->assertEquals('fail', $this->vars['result']['status']);
    }
    
    
    public function testEdit_ok()
    {
        $requests = $this->mockProgramAccess();
        $requests
        ->expects($this->once())
        ->method('_notifyUpdateRegisteredKeywords')
        ->with('testurl')
        ->will($this->returnValue(true));
        
        $request = $this->Maker->getOneRequest();
        
        $this->instanciateModels();
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $savedRequest['Request']['keyword'] = 'OTHERKEYWORD';
        
        $this->testAction(
            "testurl/programRequests/edit/" . $savedRequest['Request']['_id'],
            array(
                'method' => 'POST',
                'data' => $savedRequest
                )
            );
        
        $this->assertEquals('OTHERKEYWORD', $requests->data['Request']['keyword']);
    }


    public function testEdit_fail_()
    {
        $requests = $this->mockProgramAccess();
        $requests
        ->expects($this->once())
        ->method('_notifyUpdateRegisteredKeywords')
        ->with('testurl')
        ->will($this->returnValue(true));
        
        $request = $this->Maker->getOneRequest();
        
        $this->instanciateModels();
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $savedRequest['Request']['keyword'] = 'OTHERKEYWORD';
        
        $this->testAction(
            "testurl/programRequests/edit/" . $savedRequest['Request']['_id'],
            array(
                'method' => 'POST',
                'data' => $savedRequest
                )
            );
        
        $this->assertEquals('OTHERKEYWORD', $requests->data['Request']['keyword']);
    }
    
    
    public function testEdit_json()
    {
        $requests = $this->mockProgramAccess();
        
        $request = $this->Maker->getOneRequest();
        
        $this->instanciateModels();
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $savedRequest['Request']['keyword'] = 'OTHERKEYWORD';
        
        $this->testAction(
            "testurl/programRequests/edit.json",
            array(
                'method' => 'POST',
                'data' => $savedRequest
                )
            );
        
        $this->assertEquals('OTHERKEYWORD', $requests->data['Request']['keyword']);
    }
    
    
    public function testDelete()
    {
        $requests = $this->mockProgramAccess();
        $requests->Session
        ->expects($this->once())
        ->method('setFlash')
        ->with('The request has been deleted.');
        $requests
        ->expects($this->once())
        ->method('_notifyUpdateRegisteredKeywords')
        ->with('testurl')
        ->will($this->returnValue(true));
        
        $request = $this->Maker->getOneRequest();
        
        $this->instanciateModels();
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        
        $this->testAction("testurl/programRequests/delete/" . $savedRequest['Request']['_id']);     
    }
    
    public function testValidateKeyword_fail_sameProgram_dialogueUse()
    {
        $this->mockProgramAccess();

        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');    

        $dialogue = $this->Maker->getOneDialogue();        
        $saveDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeActive($saveDialogue['Dialogue']['_id']);
        
        $this->testAction(
            "testurl/programRequests/validateKeyword",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'keyword request')
                )
            );
        $this->assertEquals('fail', $this->vars['result']['status']);
    }
    
    
    public function testValidateKeyword_fail_sameProgramDifferentRequest()
    {
        $requestes = $this->mockProgramAccess();

        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala'); 

        $request = $this->Maker->getOneRequest('otherkeyword request');
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
                
        $this->testAction(
            "testurl/programRequests/validateKeyword",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'otherkeyword request')
                )
            );
        $this->assertEquals('fail', $this->vars['result']['status']);
        $this->assertEquals(
            "'otherkeyword request' already used in the same program by a request.", 
            $this->vars['result']['message']
            );
    }

    public function testValidateKeyword_ok_sameProgramSameRequest()
    {
        $requests = $this->mockProgramAccess_withoutProgram();
        $requests->Program
        ->expects($this->any())
        ->method('find')
        ->will(
            $this->onConsecutiveCalls(
                $this->programData, 
                array($this->otherProgramData[0])));
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
            "testurl/programRequests/validateKeyword",
            array(
                'method' => 'post',
                'data' => array (
                    'keyword'=>'otherkeyword request',
                    'object-id' => $savedRequest['Request']['_id'])
                )
            );
        
        $this->assertEquals('ok', $this->vars['result']['status']);
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
            "testurl/programRequests/validateKeyword",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'otherkeyword')
                )
            );
        $this->assertEquals('ok', $this->vars['result']['status']);
        
    }

   
    public function testValidateKeyword_fail_otherProgramUseDialogue()
    {
        
        $requests = $this->mockProgramAccess_withoutProgram();
        $requests->Program
        ->expects($this->any())
        ->method('find')
        ->will(
            $this->onConsecutiveCalls(
                $this->programData, 
                array($this->otherProgramData[0])));
         $requests->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('otherkeyword'))
        ->will($this->returnValue(array(
            'otherkeyword' => array('programName' => 'other program', 'type' => 'request'))));
        
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala'); 
        
        $this->testAction(
            "testurl/programRequests/validateKeyword",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'otherkeyword')
                )
            );
        $this->assertEquals(
            'fail', 
            $this->vars['result']['status']);
    }

    public function testValidationKeyword_fail_otherProgramUserRequest()
    {        
        $requests = $this->mockProgramAccess_withoutProgram();
        $requests->Program
        ->expects($this->any())
        ->method('find')
        ->will(
            $this->onConsecutiveCalls(
                $this->programData, 
                array($this->otherProgramData[0])));
        $requests->Keyword
        ->expects($this->once())
        ->method('areKeywordsUsedByOtherPrograms')
        ->with('testdbprogram', '256-8282', array('otherkeyword'))
        ->will($this->returnValue(array(
            'otherkeyword' => array('programName' => 'other program', 'type' => 'dialogue'))));
    
        $this->instanciateModels();
        $this->setupProgramSettings('256-8282', 'Africa/Kampala');

        $this->testAction(
            "testurl/programRequests/validateKeyword",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'otherkeyword')
                )
            );
        $this->assertEquals('fail', $this->vars['result']['status']);
        
    }

  
}
