<?php
App::uses('ProgramContentVariablesController', 'Controller');
App::uses('ContentVariable', 'Model');
App::uses('ContentVariableTable', 'Model');
App::uses('ProgramSpecificMongoModel', 'Model');


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
    
    var $programData = array(
        0 => array( 
            'Program' => array(
                'name' => 'Test Name',
                'url' => 'testurl',
                'timezone' => 'utc',
                'database' => 'testdbprogram',
                'status' => 'running'
                )
            ));
    
    
    public function setUp()
    {
        parent::setUp();
        $this->ProgramContentVariables = new TestProgramContentVariablesController();
        
        $dbName = $this->programData[0]['Program']['database'];
        $this->ContentVariable = ProgramSpecificMongoModel::init(
            'ContentVariable', $dbName, true);
        $this->ContentVariableTable = ProgramSpecificMongoModel::init(
            'ContentVariableTable', $dbName, true);
        
        $this->dropData();
    }
    
    
    protected function dropData()
    {
        $this->ContentVariable->deleteAll(true, false);
        $this->ContentVariableTable->deleteAll(true, false);
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        unset($this->ContentVariable);
        unset($this->ContentVariableTable);
        parent::tearDown();
    }
    
    
    public function mockProgramAccess()
    {
        $contentVariables = $this->generate(
            'ProgramContentVariables', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read', 'setFlash'),
                    'Auth' => array('loggedIn')
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
        
        $contentVariables->Auth
        ->expects($this->any())
        ->method('loggedIn')
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
        
        $contentVariables = $this->mockProgramAccess();  
        $this->testAction("/testurl/programContentVariables/index");
        $indexedContentVariables = $this->vars['contentVariables'];
        $this->assertEquals(1, count($indexedContentVariables));
        $this->assertEquals(
            $savedKeysValue['ContentVariable']['_id'], 
            $indexedContentVariables[0]['ContentVariable']['_id']);
        
        $contentVariables = $this->mockProgramAccess();  
        $this->testAction("/testurl/programContentVariables/indexTable");
        $indexedContentVariables = $this->vars['contentVariableTables'];
        $this->assertEquals(1, count($indexedContentVariables));
        $this->assertEquals(
            $savedTable['ContentVariableTable']['_id'], 
            $indexedContentVariables[0]['ContentVariableTable']['_id']);
        
    }
    
    
    public function testAdd()
    {
        $contentVariables = $this->mockProgramAccess();  
        
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
        $contentVariables = $this->mockProgramAccess();  
        
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
            "/testurl/programContentVariables/addTable.json", 
            array(
                'method' => 'post',
                'data' => $contentVariableTable
                )
            );
        $this->assertEquals(2, $this->ContentVariable->find('count'));
        $this->assertEquals(1, $this->ContentVariableTable->find('count'));
        
        $savedTable = $this->ContentVariableTable->find('first');
        $contentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('mombasa', 'Chicken price')))); 
        $this->assertEquals(1, count($contentVariable));
        $this->assertEquals('300 Ksh', $contentVariable[0]['ContentVariable']['value']);
        $this->assertEquals(
            $savedTable['ContentVariableTable']['_id']."",
            $contentVariable[0]['ContentVariable']['table']);
    }
    
    
    public function testEdit()
    {
        $contentVariables = $this->mockProgramAccess();  
        
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
        $contentVariables = $this->mockProgramAccess();  
        
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
                        'table' => $savedTable['ContentVariableTable']['_id']."",
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
        $contentVariables = $this->mockProgramAccess();  
        
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
    
    
    public function testEditTableValue()
    {
        $contentVariables = $this->mockProgramAccess();  
        
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
        $savedContentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('mombasa', 'Chicken price')))); 
        
        $this->testAction(
            "/testurl/programContentVariables/editTableValue.json", 
            array(
                'method' => 'post',
                'data' => array(
                    'ContentVariable' => array(
                        'keys' => array(
                            'mombasa',
                            'Chicken price'
                            ),
                        'value' => '200 Ksh'
                        )
                    )
                )
            );
        $contentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('mombasa', 'Chicken price')))); 
        $this->assertEquals(1, count($contentVariable));
        $this->assertEquals('200 Ksh', $contentVariable[0]['ContentVariable']['value']);
        $this->assertEquals(
            $savedTable['ContentVariableTable']['_id']."", 
            $contentVariable[0]['ContentVariable']['table']);
        
        $contentVariableTable = $this->ContentVariableTable->find('first');
        $this->assertEquals('200 Ksh', $contentVariableTable['ContentVariableTable']['columns'][1]['values'][0]);
    }
    
    
    public function testEditTable()
    {
        $contentVariables = $this->mockProgramAccess();  
        
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
        
        $contentVariableTable['ContentVariableTable']['name'] = "another table";
        $contentVariableTable['ContentVariableTable']['columns'][0]['values'] = array('kisumu', 'mombasa');
        
        $this->testAction(
            "/testurl/programContentVariables/editTable/".$savedTable['ContentVariableTable']['_id'].".json", 
            array(
                'method' => 'post',
                'data' => $contentVariableTable
                )
            );
        
        $this->assertEquals(2, $this->ContentVariable->find('count'));
        $this->assertEquals(1, $this->ContentVariableTable->find('count'));
        $contentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('mombasa', 'Chicken price')))); 
        $this->assertEquals('400 Ksh', $contentVariable[0]['ContentVariable']['value']);
        $this->assertEquals(
            $savedTable['ContentVariableTable']['_id']."", 
            $contentVariable[0]['ContentVariable']['table']);
        $contentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('kisumu', 'Chicken price')))); 
        $this->assertEquals('300 Ksh', $contentVariable[0]['ContentVariable']['value']);
    }
    
    
    public function testDelete()
    {
        $contentVariables = $this->mockProgramAccess();  
        
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
    
    
    public function testExport()
    {
        $contentVariables = $this->mockProgramAccess();
        
        $contentVariableTable =  array(
            'ContentVariableTable' => array(
                'name' => 'my table',
                'columns' => array(
                    array(
                        'header' => 'Town',
                        'values' => array('mombasa', 'nairobi','kla','Jinja')
                        ),
                    array(
                        'header' => 'Chicken price',
                        'values' => array('300 Ksh', '400 Ksh', ' ', '500 Ugx')
                        )
                    )
                )
            );
        $this->ContentVariableTable->create();
        $savedcontentVariableTable = $this->ContentVariableTable->save($contentVariableTable);
        
        $this->testAction("/testurl/programContentVariables/export/".$savedcontentVariableTable['ContentVariableTable']['_id']);
        
        $this->assertTrue(isset($this->vars['fileName']));
        
        $this->assertFileEquals(
            TESTS . 'files/testdbprogram_my_table_table.csv',
            WWW_ROOT . 'files/programs/testurl/' . $this->vars['fileName']);
        
    }
    
    
}


