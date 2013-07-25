<?php
App::uses('ProgramDynamicContentsController', 'Controller');

class TestProgramDynamicContentsController extends ProgramDynamicContentsController
{

    public $autoRender = false;


    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }


}


class ProgramDynamicContentsControllerTestCase extends ControllerTestCase
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
        $this->ProgramDynamicContents = new ProgramDynamicContentsController();
        $this->dropData();
    }


    protected function dropData()
    {
        $this->instanciateDynamicContentModel();
        $this->DynamicContent->deleteAll(true, false);
    }


    protected function instanciateDynamicContentModel() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);
        
        $this->DynamicContent = new DynamicContent($options);
    }
    

    public function tearDown()
    {
        $this->dropData();
        unset($this->DynamicContent);
        parent::tearDown();
    }

    
    public function mock_program_access()
    {
        $dynamicContents = $this->generate(
            'ProgramDynamicContents', array(
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

        $dynamicContents->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
            
        $dynamicContents->Program
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue($this->programData));

        $dynamicContents->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls(
                '4', 
                '2',
                $this->programData[0]['Program']['database'],
                $this->programData[0]['Program']['name'],
                'utc',
                'testdbprogram'
                ));
 
        return $dynamicContents;

    }
    
/**
 * Test methods
 *
 */    
    
    public function testIndex()
    {
        $dynamicContents = $this->mock_program_access();  

        $dynamicContent =  array(
            'DynamicContent' => array(
                'key' => 'weather',
                'value' => '30C'
             )
        );
        $this->DynamicContent->create();
        $savedMessage = $this->DynamicContent->save($dynamicContent);
        
        $this->testAction("/testurl/programDynamicContents/index");
        $this->assertEquals(1, count($this->vars['dynamicContents']));
    }
    
    
    public function testAdd()
    {
        $dynamicContents = $this->mock_program_access();  

        $dynamicContent =  array(
            'DynamicContent' => array(
                'key' => 'myKey',
                'value' => 'my value!!!!'
             )
        );
        $this->testAction(
            "/testurl/programDynamicContents/add", 
            array(
                'method' => 'post',
                'data' => $dynamicContent
                )
            );
        $this->assertEquals(1, $this->DynamicContent->find('count'));
    }
    
    
    public function testEdit()
    {
        $dynamicContents = $this->mock_program_access();  

        $dynamicContent =  array(
            'DynamicContent' => array(
                'key' => 'your key',
                'value' => 'your value'
             )
        );
        $this->DynamicContent->create();
        $savedMessage = $this->DynamicContent->save($dynamicContent);
        
        $this->testAction(
            "/testurl/programDynamicContents/edit/".$savedMessage['DynamicContent']['_id'], 
            array(
                'method' => 'post',
                'data' => array(
                    'DynamicContent' => array(
                        'key' => 'a Key',
                        'value' => 'a value'
                        )
                    )
                )
            );
        $this->DynamicContent->id = $savedMessage['DynamicContent']['_id']."";
        $dynamicContent = $this->DynamicContent->read(); 
        $this->assertEquals(
           'a value',
            $dynamicContent['DynamicContent']['value']
        );
    }
    
    
    public function testDelete()
    {
        $dynamicContents = $this->mock_program_access();  

        $dynamicContent =  array(
            'DynamicContent' => array(
                'key' => 'myKey',
                'value' => 'my value!!!!'
             )
        );
        $this->DynamicContent->create();
        $savedMessage = $this->DynamicContent->save($dynamicContent);
        
        $this->testAction(
            "/testurl/programDynamicContents/delete/".$savedMessage['DynamicContent']['_id']);
        $this->assertEquals(0, $this->DynamicContent->find('count'));        
    }
    
}


