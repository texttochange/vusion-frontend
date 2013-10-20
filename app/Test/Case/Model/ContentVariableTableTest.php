<?php
App::uses('ContentVariableTable', 'Model');
App::uses('ContentVariable', 'Model');


class ContentVariableTableTestCase extends CakeTestCase
{


    public function setUp()
    {
        parent::setUp();

        $option                     = array('database' => 'testdbprogram');
        $this->ContentVariableTable = new ContentVariableTable($option);
        $this->ContentVariable      = new ContentVariable($option);

        $this->clearData();       
    }


    public function tearDown()
    {
        $this->clearData();
        unset($this->ContentVariableTable);
        unset($this->ContentVariable);
        parent::tearDown();
    }


    public function clearData() 
    {
        $this->ContentVariableTable->deleteAll(true, false);
        $this->ContentVariable->deleteAll(true, false);
    }


    public function testSave_ok()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh', null)
                    ),
                array(
                    'header' => null,
                    'values' => array(null, null)
                    )
                )
            );

        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertTrue(isset($result));
        $this->assertEqual(
            2,
            count($result['ContentVariableTable']['columns'])
            );

        $mombasaContentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('mombasa', 'Chicken price'))));
        $this->assertEqual(
                '300 Ksh',
                $mombasaContentVariable[0]['ContentVariable']['value']);
        $this->assertEqual(
                $this->ContentVariableTable->id,
                $mombasaContentVariable[0]['ContentVariable']['table']);

        $nairobiContentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('nairobi', 'Chicken price'))));
        $this->assertEqual(
                '',
                $nairobiContentVariable[0]['ContentVariable']['value']);     
        $this->assertEqual(
                $this->ContentVariableTable->id,
                $mombasaContentVariable[0]['ContentVariable']['table']); 
    }


    public function testSave_ok_miniTable()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh')
                    ),
                )
            );

        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertTrue(isset($result));
        $this->assertEqual(
            2,
            count($result['ContentVariableTable']['columns'])
            );

        $mombasaContentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('mombasa', 'Chicken price'))));
        $this->assertEqual(
                '300 Ksh',
                $mombasaContentVariable[0]['ContentVariable']['value']);      
    }


    public function testSave_fail_nameEmpty()
    {
        $contentVariableTable = array(
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'nair.obi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh', '400 Ksh')
                    )
                )
            );
        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertFalse($result);
        $this->assertEqual(
            'The table name cannot be empty.',
            $this->ContentVariableTable->validationErrors['name'][0]
            );
    }


    public function testSave_fail_columnValues_key()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'nair.obi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh', '400 Ksh')
                    )
                )
            );

        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertFalse($result);
        $this->assertEqual(
            'The key nair.obi can only be made of letter, digit and space.',
            $this->ContentVariableTable->validationErrors['columns'][0][0]['values'][0][1]
            );
    }

    
    public function testSave_fail_columnValues_contentVariable()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('$300', '#@!400')
                    )
                )
            );
        
        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertFalse($result);
        $this->assertEqual(
            'The variable $300 can only be made of letter, digit, space, dot and comma.',
            $this->ContentVariableTable->validationErrors['columns'][0][1]['values'][0][0]
            );
        $this->assertEqual(
            'The variable #@!400 can only be made of letter, digit, space, dot and comma.',
            $this->ContentVariableTable->validationErrors['columns'][0][1]['values'][0][1]
            );
    }


    public function testSave_fail_columnValues_contentVariable_validation()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ks', '400 Ksh'),
                    'validation' => '/^\d+ Ksh$/'
                    )
                )
            );
        
        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertFalse($result);
        $this->assertEqual(
            'The value 300 Ks is not matchin validation /^d+ Ksh$/.',
            $this->ContentVariableTable->validationErrors['columns'][0][1]['values']['0']
            );
    }


    public function testSave_fail_columnHeader()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Chic.ken price',
                    'values' => array('300 Ksh', '400 Ksh')
                    )
                )
            );

        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertFalse($result);
        
        $this->assertEqual(
            "The header Chic.ken price can only be made of letter, digit and space.",
            $this->ContentVariableTable->validationErrors['columns'][0][1]['header'][0]
            );
    }


    public function testSave_fail_oneColumn()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'nairobi')
                    ),
                )
            );

        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertFalse($result);
        $this->assertEqual(
            "A table must have at least 2 columns.",
            $this->ContentVariableTable->validationErrors['columns'][0]
            );
    }


    public function testSave_fail_atLeastOneContentVariableColumn()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'mombasa')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh', '400 Ksh')
                    ),
                )
            );

        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertFalse($result);
        $this->assertEqual(
            "Not able to identify unique set of keys.",
            $this->ContentVariableTable->validationErrors['columns'][0]
            );
    }


    public function testSave_fail_columnDuplicateHeader()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Town',
                    'values' => array('300 Ksh', '400 Ksh')
                    )
                )
            );

        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertFalse($result);
        $this->assertEqual(
            "The table cannot have duplicate headers \"Town\".",
            $this->ContentVariableTable->validationErrors['columns'][0]
            );
    }


    public function testSave_fail_columnValidation()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh', '400 Ksh'),
                    'validation' => 'number'
                    )
                )
            );

        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertFalse($result);
        $this->assertEqual(
            "The validation can only be a valid regular expression.",
            $this->ContentVariableTable->validationErrors['columns'][0][1]['validation'][0]
            );
    }


    public function testSave_fail_maxKeyColumn()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'mombasa')
                    ),
                array(
                    'header' => 'Market',
                    'values' => array('central', 'central')
                    ),
                array(
                    'header' => 'Item',
                    'values' => array('chicken', 'fish')
                    ),
                array(
                    'header' => 'price',
                    'values' => array('300 Ksh', '400 Ksh'),
                    )
                )
            );

        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertFalse($result);
        $this->assertEqual(
            "A maximum of 2 column can be keys, this table needs 3 keys.",
            $this->ContentVariableTable->validationErrors['columns'][0]
            );
    }


    public function testSave_fail_keysNotUnique_keysValue() 
    {
        $contentVariable = array(
            'keys' => 'mombasa.Chicken price',
            'value' => '200 Ksk'
            );
        $this->ContentVariable->create();
        $this->ContentVariable->save($contentVariable);

        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh', '400 Ksh'),
                    )
                )
            );

        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertFalse($result);
        $this->assertEqual(
            'The keys mombasa.Chicken price is already used by a keys/value.',
            $this->ContentVariableTable->validationErrors['columns'][0]
            );
    }


    public function testSave_fail_keysNotUnique_table() 
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'District',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh', '400 Ksh'),
                    )
                )
            );

        $this->ContentVariableTable->create();
        $this->ContentVariableTable->save($contentVariableTable);

        $contentVariableTable = array(
            'name' => 'my other table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh', '400 Ksh'),
                    )
                )
            );

        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertFalse($result);
        $this->assertEqual(
            'The keys mombasa.Chicken price is already used by the table my table.',
            $this->ContentVariableTable->validationErrors['columns'][0]
            );
    }


   public function testSave_fail_notUniqueName() 
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'District',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh', '400 Ksh'),
                    )
                )
            );

        $this->ContentVariableTable->create();
        $this->ContentVariableTable->save($contentVariableTable);

        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'District',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh', '400 Ksh'),
                    )
                )
            );

        $this->ContentVariableTable->create();
        $this->assertFalse($this->ContentVariableTable->save($contentVariableTable));

        $this->assertEqual(
            'Another table already exist with this name.',
            $this->ContentVariableTable->validationErrors['name'][0]
            );
    }


    public function testSelectColumnsForKeys()
    {
        
        $columns = array(   
            array(  
                'header' => 'Town',
                'values' => array('mombasa', 'nairobi')
                ),
            array(
                'header' => 'Chicken price',
                'values' => array('300 Ksh', '400 Ksh')
                )
            );

        $result = $this->ContentVariableTable->selectColumnsForKeys($columns);
        $this->assertEqual($columns[0]['type'], 'key');
        $this->assertEqual($columns[1]['type'], 'contentvariable');

        $columns = array(   
            array(  
                'header' => 'Town',
                'values' => array('mombasa', 'mombasa', 'nairobi')
                ),
            array(  
                'header' => 'market',
                'values' => array('central','bamburi', 'central')
                ),
            array(
                'header' => 'Chicken price',
                'values' => array('300 Ksh', '350 Ksh', '400 Ksh')
                )
            );

        $result = $this->ContentVariableTable->selectColumnsForKeys($columns);
        $this->assertEqual($columns[0]['type'], 'key');
        $this->assertEqual($columns[1]['type'], 'key');
        $this->assertEqual($columns[2]['type'], 'contentvariable');


        $columns = array(
            array(
                'header' => 'Town',
                'values' => array('mombasa', 'mombasa')
                ),
            array(
                'header' => 'Market',
                'values' => array('central', 'central')
                ),
            array(
                'header' => 'Item',
                'values' => array('chicken', 'fish')
                ),
            array(
                'header' => 'price',
                'values' => array('300 Ksh', '400 Ksh'),
                )
            );

        $result = $this->ContentVariableTable->selectColumnsForKeys($columns);
        $this->assertEqual($columns[0]['type'], 'key');
        $this->assertEqual($columns[1]['type'], 'key');
        $this->assertEqual($columns[2]['type'], 'key');
        $this->assertEqual($columns[3]['type'], 'contentvariable');

    }


    public function testRemoveEmptyCell()
    {
        $columns = array(   
            array(  
                'header' => 'Town',
                'values' => array('mombasa', 'nairobi', null, null)
                ),
            array(
                'header' => 'Chicken price',
                'values' => array('300 Ksh', '400 Ksh', null, null, null)
                ),
            array(
                'header' => null,
                'values' => array(null, null, null, '200 Ksh', null)
                ),
            array(
                'header' => null,
                'values' => array(null, '300 Ksh', null, null, null)
                ),
            array(
                'header' => null,
                'values' => array(null, null, null, null, null)
                ),
            array(
                'header' => null,
                'values' => array(null, null, null, null, null)
                ),
            array(
                'header' => null,
                'values' => array(null, null, null, null, null)
                ),
            array(
                'header' => null,
                'values' => array(null, null, null, null, null)
                )
            );

        $result = $this->ContentVariableTable->removeEmptyCells($columns);
        $this->assertEqual(count($columns), 4);
        for ($i=0; $i<4; $i++) {
            $this->assertEqual(count($columns[$i]['values']), 4);
        }
    }


    public function testGetAllKeysValue() 
    {
        $expected = array(
            array('keys' => array('mombasa', 'central', 'Chicken price'), 'value' => '300 Ksh'),
            array('keys' => array('mombasa', 'bamburi', 'Chicken price'), 'value' => '350 Ksh'),
            array('keys' => array('nairobi', 'central', 'Chicken price'), 'value' => '400 Ksh'),
            array('keys' => array('mombasa', 'central', 'Fish price'), 'value' => '600 Ksh'),
            array('keys' => array('mombasa', 'bamburi', 'Fish price'), 'value' => '500 Ksh'),
            array('keys' => array('nairobi', 'central', 'Fish price'), 'value' => '400 Ksh'),
            );

        $columns = array(   
            array(  
                'header' => 'Town',
                'values' => array('mombasa', 'mombasa', 'nairobi'),
                'type' => 'key'
                ),
            array(  
                'header' => 'market',
                'values' => array('central','bamburi', 'central'),
                'type' => 'key'
                ),
            array(
                'header' => 'Chicken price',
                'values' => array('300 Ksh', '350 Ksh', '400 Ksh'),
                'type' => 'contentvariable'
                ),
            array(
                'header' => 'Fish price',
                'values' => array('600 Ksh', '500 Ksh', '400 Ksh'),
                'type' => 'contentvariable'
                )
            );

        $result = $this->ContentVariableTable->getAllKeysValue($columns);
        $this->assertEqual($result, $expected);
    }


    public function testDelete()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'District',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh', '400 Ksh'),
                    )
                )
            );

        $this->ContentVariableTable->create();
        $savedTable = $this->ContentVariableTable->save($contentVariableTable);

        $this->ContentVariableTable->deleteTableAndValues($savedTable['ContentVariableTable']['_id']);
        $this->assertEqual(
            0,
            $this->ContentVariableTable->find('count')
            );
        $this->assertEqual(
            0,
            $this->ContentVariable->find('count')
            );
    }


    public function testUpdateKeysValue()
    {
        $contentVariableTable = array(
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
            );
        $this->ContentVariableTable->create();
        $this->ContentVariableTable->save($contentVariableTable);
        
        $savedKeysValue = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('nairobi', 'Chicken price'))));
        $savedKeysValue[0]['ContentVariable']['value'] = "600 Ksh";
        $this->ContentVariable->id = $savedKeysValue[0]['ContentVariable']['_id'];
        $updatedKeysValue = $this->ContentVariable->save($savedKeysValue[0]['ContentVariable']);
       
        $result = $this->ContentVariableTable->updateTableFromKeysValue($updatedKeysValue);
        $this->assertTrue($result != false);
        
        $savedTable = $this->ContentVariableTable->find('first');
        $this->assertEquals(
            array('300 Ksh', '600 Ksh'),
            $savedTable['ContentVariableTable']['columns'][1]['values']    
            );
    }


    public function testUpdateTable()
    {
        $contentVariableTable = array(
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
            );
        $this->ContentVariableTable->create();
        $savedTable = $this->ContentVariableTable->save($contentVariableTable);

        $savedTable['ContentVariableTable']['name'] = 'another table';
        $savedTable['ContentVariableTable']['columns'][1]['values'][1] = '200 Ksh';
        $this->ContentVariableTable->create();
        $this->ContentVariableTable->id = $savedTable['ContentVariableTable']['_id']; 
        $updateResult = $this->ContentVariableTable->save($savedTable);
        $this->assertTrue(isset($updateResult['ContentVariableTable']));
        
        $savedTable = $this->ContentVariableTable->find('first');
        $this->assertEquals(
            array('300 Ksh', '200 Ksh'),
            $savedTable['ContentVariableTable']['columns'][1]['values']    
            );
        $this->assertEquals(
            'another table',
            $savedTable['ContentVariableTable']['name']    
            );

        $this->assertEquals(
            2,
            $this->ContentVariable->find('count'));
    }


}