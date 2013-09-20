<?php
App::uses('ProgramContentVariablesController', 'Controller');
App::uses('ContentVariable', 'Model');
App::uses('ContentVariableTable', 'Model');

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
        $this->ContentVariableTable->deleteAll(true, false);
    }


    protected function instanciateContentVariableModel() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);
        
        $this->ContentVariable = new ContentVariable($options);
        $this->ContentVariableTable = new ContentVariableTable($options);
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
    
    public function testIndex_keysValue_table()
    {
        $contentVariable =  array(
            'ContentVariable' => array(
                'keys' => 'program.weather',
                'value' => '30C'
                )
            ); 
        $this->ContentVariable->create();
        $savedKeysValue = $this->ContentVariable->save($contentVariable);     
        
        $contentVariableTable = array(
            'ContentVariableTable' => array(
                'name' => 'my table',
                'columns' => array(
                    array(
                        'header' => 'Town',
                        'values' => array('mombasa', 'nairobi')
                        ),
                    array(
                        'header' => 'Chicken price',
                        'values' => array('300 Ksh', '400 Ksh')
                        )
                    )
                )
            );
        $this->ContentVariableTable->create();
        $savedTable = $this->ContentVariableTable->save($contentVariableTable);
       
        $contentVariables = $this->mock_program_access();  
        $this->testAction("/testurl/programContentVariables/index");
        $indexedContentVariables = $this->vars['contentVariables'];
        $this->assertEquals(1, count($indexedContentVariables));
        $this->assertEquals(
            $savedKeysValue['ContentVariable']['_id'], 
            $indexedContentVariables[0]['ContentVariable']['_id']);

        $contentVariables = $this->mock_program_access();  
        $this->testAction("/testurl/programContentVariables/indexTable");
        $indexedContentVariables = $this->vars['contentVariablesTable'];
        $this->assertEquals(1, count($indexedContentVariables));
        $this->assertEquals(
            $savedTable['ContentVariableTable']['_id'], 
            $indexedContentVariables[0]['ContentVariableTable']['_id']);

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
    

    public function testAddTable()
    {
        $contentVariables = $this->mock_program_access();  
        
        $contentVariableTable =  array(
            'ContentVariableTable' => array(
                'name' => 'my table',
                'columns' => array(
                    array(
                        'header' => 'Town',
                        'values' => array('mombasa', 'nairobi')
                        ),
                    array(
                        'header' => 'Chicken price',
                        'values' => array('300 Ksh', '400 Ksh')
                        )
                    )
                )
            );

        $this->testAction(
            "/testurl/programContentVariables/addTable", 
            array(
                'method' => 'post',
                'data' => $contentVariableTable
                )
            );
        $this->assertEquals(2, $this->ContentVariable->find('count'));
        $this->assertEquals(1, $this->ContentVariableTable->find('count'));
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


    public function testEdit_belongToTable()
    {
        $contentVariables = $this->mock_program_access();  
        
        $contentVariableTable =  array(
            'ContentVariableTable' => array(
                'name' => 'my table',
                'columns' => array(
                    array(
                        'header' => 'Town',
                        'values' => array('mombasa', 'nairobi')
                        ),
                    array(
                        'header' => 'Chicken price',
                        'values' => array('300 Ksh', '400 Ksh')
                        )
                    )
                )
            );
        $this->ContentVariableTable->create();
        $savedTable = $this->ContentVariableTable->save($contentVariableTable);
        $contentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('mombasa', 'Chicken price')))); 

        $this->testAction(
            "/testurl/programContentVariables/edit/".$contentVariable[0]['ContentVariable']['_id'], 
            array(
                'method' => 'post',
                'data' => array(
                    'ContentVariable' => array(
                        'keys' => 'mombasa.Chicken price',
                        'table' => 'my table',
                        'value' => '200 Ksh'
                        )
                    )
                )
            );
        $contentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('mombasa', 'Chicken price')))); 
        $this->assertEquals(
           '200 Ksh',
            $contentVariable[0]['ContentVariable']['value']
        );
        $contentVariableTable = $this->ContentVariableTable->find('first');
        $this->assertEquals(
           array('200 Ksh', '400 Ksh'),
            $contentVariableTable['ContentVariableTable']['columns'][1]['values']
        );
    }


    public function testEdit_fail_editKeys()
    {
        $contentVariables = $this->mock_program_access();  
        
        $contentVariableTable =  array(
            'ContentVariableTable' => array(
                'name' => 'my table',
                'columns' => array(
                    array(
                        'header' => 'Town',
                        'values' => array('mombasa', 'nairobi')
                        ),
                    array(
                        'header' => 'Chicken price',
                        'values' => array('300 Ksh', '400 Ksh')
                        )
                    )
                )
            );
        $this->ContentVariableTable->create();
        $this->ContentVariableTable->save($contentVariableTable);
        $savedContentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('mombasa', 'Chicken price')))); 

        $this->testAction(
            "/testurl/programContentVariables/edit/".$savedContentVariable[0]['ContentVariable']['_id'], 
            array(
                'method' => 'post',
                'data' => array(
                    'ContentVariable' => array(
                        'keys' => 'mombasa.price',
                        'value' => '200 Ksh'
                        )
                    )
                )
            );
        $contentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('mombasa', 'price')))); 
        $this->assertEquals(0, count($contentVariable));
    }


    public function testEditTable()
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


