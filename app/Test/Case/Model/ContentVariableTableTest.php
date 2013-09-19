<?php
App::uses('ContentVariableTable', 'Model');
App::uses('ContentVariable', 'Model');


class ContentVariableTableTestCase extends CakeTestCase
{


    public function setUp()
    {
        parent::setUp();

        $option        = array('database'=>'test');
        $this->ContentVariableTable = new ContentVariableTable($option);
        $this->ContentVariable = new ContentVariable($option);

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
                    'values' => array('300 Ksh', '400 Ksh')
                    )
                )
            );

        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertTrue(isset($result));

        $mombasaContentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('mombasa', 'Chicken price'))));
        $this->assertEqual(
                '300 Ksh',
                $mombasaContentVariable[0]['ContentVariable']['value']);

        $nairobiContentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('nairobi', 'Chicken price'))));
        $this->assertEqual(
                '400 Ksh',
                $nairobiContentVariable[0]['ContentVariable']['value']);      
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
            $this->ContentVariableTable->validationErrors['columns'][0][0]['values']['0']
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
                    'values' => array('$300', '400')
                    )
                )
            );
        
        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertFalse($result);
        $this->assertEqual(
            'The variable $300 can only be made of letter, digit, space, dot and comma.',
            $this->ContentVariableTable->validationErrors['columns'][0][1]['values']['0']
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
            "The table cannot have duplicate headers.",
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
            'The key mombasa.Chicken price is already used by a keys/value.',
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
            'The key mombasa.Chicken price is already used by the table my table.',
            $this->ContentVariableTable->validationErrors['columns'][0]
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
    }


    public function testGetAllKeysValue() 
    {
        $expected = array(
            array('keys' => array('mombasa', 'central', 'Chicken price'), 'value' => '300 Ksh'),
            array('keys' => array('mombasa', 'bamburi', 'Chicken price'), 'value' => '350 Ksh'),
            array('keys' => array('nairobi', 'central', 'Chicken price'), 'value' => '400 Ksh'),
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
                )
            );

        $result = $this->ContentVariableTable->getAllKeysValue($columns);
        $this->assertEqual($result, $expected);
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


}