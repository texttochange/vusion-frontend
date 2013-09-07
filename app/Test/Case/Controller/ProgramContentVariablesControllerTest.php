<?php
App::uses('ProgramContentVariablesController', 'Controller');

class TestProgramContentVariablesController extends ProgramContentVariablesController
{

    public $autoRender = false;


    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }


}


class ProgramContentVariablesControllerTestCase extends ControllerTestCase
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
            'timezone' => 'utc',
            'database' => 'testdbprogram'
        )
     ));


    public function setUp()
    {
        parent::setUp();
        $this->ProgramContentVariables = new TestProgramContentVariablesController();
        $this->dropData();
    }


    protected function dropData()
    {
        $this->instanciateContentVariableModel();
        $this->ContentVariable->deleteAll(true, false);
    }


    protected function instanciateContentVariableModel() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);
        
        $this->ContentVariable = new ContentVariable($options);
    }
    

    public function tearDown()
    {
        $this->dropData();
        unset($this->ContentVariable);
        parent::tearDown();
    }

    
    public function mock_program_access()
    {
        $contentVariables = $this->generate(
            'ProgramContentVariables', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read', 'setFlash'),
                    'Auth' => array()
                    ),
                'models' => array(
                   'Program' => array('find', 'count'),
                   'Group' => array()
                   )
                )
            );

        $contentVariables->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
            
        $contentVariables->Program
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue($this->programData));

        $contentVariables->Session
            ->expects($this->any())
            ->method('read')
            ->will(
                $this->returnValue($this->programData[0]['Program']['database'])
                );
 
        return $contentVariables;

    }
    
/**
 * Test methods
 *
 */    
    
    public function testIndex()
    {
        $contentVariables = $this->mock_program_access();  

        $contentVariable =  array(
            'ContentVariable' => array(
                'keys' => 'program.weather',
                'value' => '30C'
             )
        );
        $this->ContentVariable->create();
        $savedMessage = $this->ContentVariable->save($contentVariable);
        
        $this->testAction("/testurl/programContentVariables/index");
        $this->assertEquals(1, count($this->vars['contentVariables']));
    }
    
    
    public function testAdd()
    {
        $contentVariables = $this->mock_program_access();  

        $contentVariable =  array(
            'ContentVariable' => array(
                'keys' => 'my.Key',
                'value' => 'my value'
             )
        );
        $this->testAction(
            "/testurl/programContentVariables/add", 
            array(
                'method' => 'post',
                'data' => $contentVariable
                )
            );
        $this->assertEquals(1, $this->ContentVariable->find('count'));
    }
    
  
    public function testEdit()
    {
        $contentVariables = $this->mock_program_access();  

        $contentVariable =  array(
            'ContentVariable' => array(
                'keys' => 'your.key',
                'value' => 'your value'
             )
        );
        $this->ContentVariable->create();
        $savedMessage = $this->ContentVariable->save($contentVariable);

        $this->testAction(
            "/testurl/programContentVariables/edit/".$savedMessage['ContentVariable']['_id'], 
            array(
                'method' => 'post',
                'data' => array(
                    'ContentVariable' => array(
                        'keys' => 'a.Key',
                        'value' => 'a value'
                        )
                    )
                )
            );
        $this->ContentVariable->id = $savedMessage['ContentVariable']['_id']."";
        $contentVariable = $this->ContentVariable->read(); 
        $this->assertEquals(
           'a value',
            $contentVariable['ContentVariable']['value']
        );
    }
    
  
    public function testDelete()
    {
        $contentVariables = $this->mock_program_access();  

        $contentVariable =  array(
            'ContentVariable' => array(
                'keys' => 'my.Key',
                'value' => 'my value'
             )
        );
        $this->ContentVariable->create();
        $savedMessage = $this->ContentVariable->save($contentVariable);
        
        $this->testAction(
            "/testurl/programContentVariables/delete/".$savedMessage['ContentVariable']['_id']);
        $this->assertEquals(0, $this->ContentVariable->find('count'));        
    }
 
}


