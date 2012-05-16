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
                    'Auth' => array()
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array()
                    ),
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
            
        return $requests;
    }
   

    public function testIndex()
    {
        $this->mockProgramAccess();
        $this->testAction("testurl/programRequests/index");   
        $this->assertEqual(array(), $this->vars['requests']);
    }


    public function testAdd()
    {
        $requests = $this->mockProgramAccess();
      
        $request = $this->Maker->getOneRequest();
       
        $this->testAction(
            "testurl/programRequests/add",
            array(
                'method' => 'post',
                'data' => $request
                )
            );
    }


    public function testEdit()
    {
        $requests = $this->mockProgramAccess();
      
        $request = $this->Maker->getOneRequest();

        $this->instanciateModels();
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $savedRequest['Request']['keyword'] = 'OTHERKEYWORD';
        
        $this->testAction(
            "testurl/programRequests/edit/" . $savedRequest['Request']['_id'],
            array(
                'method' => 'post',
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
                'method' => 'post',
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

        $request = $this->Maker->getOneRequest();

        $this->instanciateModels();
        $this->Request->create();
        $savedRequest = $this->Request->save($request);

        $this->testAction("testurl/programRequests/delete/" . $savedRequest['Request']['_id']);     
    }

    public function testValidateKeyword(){
        
        $request = $this->Maker->getOneRequest();
        $dialogue = $this->Maker->getOneDialogue();

        $this->instanciateModels();
        $saveDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeActive($saveDialogue['Dialogue']['_id']);

        $this->ProgramSetting->create();
        $this->ProgramSetting->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );

        /**Test a keyword used in an active dialogue in the same program*/
        $this->mockProgramAccess();

        $this->testAction(
            "testurl/programRequests/validateKeyword",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'keyword request')
                )
            );
         $this->assertEquals('fail', $this->vars['result']['status']);
        
        /**Test a simple keyword not used*/
         $this->mockProgramAccess();

         $this->testAction(
            "testurl/programRequests/validateKeyword",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'otherkeyword')
                )
            );
         $this->assertEquals('ok', $this->vars['result']['status']);
      

         /**Test a simple keyword is used by another program Dialogue*/
         $requests = $this->mockProgramAccess_withoutProgram();
         $requests->Program
            ->expects($this->any())
            ->method('find')
            ->will(
                $this->onConsecutiveCalls(
                    $this->programData, 
                    array(
                        $this->otherProgramData[0])
                    )
                );

         $this->instanciateExternalModels("testdbprogram2");
         $dialogue['Dialogue']['interactions'][0]['keyword'] = "otherkeyword";
         $savedDialogue = $this->externalModels['dialogue']->saveDialogue($dialogue);
         $this->externalModels['dialogue']->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);

         $this->externalModels['programSetting']->create();
         $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'8282'
                )
            );

         $this->testAction(
            "testurl/programRequests/validateKeyword",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'otherkeyword')
                )
            );
         $this->assertEquals('fail', $this->vars['result']['status']);   

         /**Test a keyword is not used by another Request in another program.*/
         $request['Request']['keyword'] = 'key join';
         $this->externalModels['request']->create();
         $this->externalModels['request']->save($request);

         $requests = $this->mockProgramAccess_withoutProgram();
         $requests->Program
            ->expects($this->any())
            ->method('find')
            ->will(
                $this->onConsecutiveCalls(
                    $this->programData, 
                    array(
                        $this->otherProgramData[0])
                    )
                );
         $this->testAction(
            "testurl/programRequests/validateKeyword",
            array(
                'method' => 'post',
                'data' => array ('keyword'=>'key')
                )
            );
         $this->assertEquals('fail', $this->vars['result']['status']);

    }

    


}
