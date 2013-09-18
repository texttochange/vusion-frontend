<?php
App::uses('ContentVariableTable', 'Model');


class ContentVariableTableTestCase extends CakeTestCase
{


    public function setUp()
    {
        parent::setUp();

        $option        = array('database'=>'test');
        $this->ContentVariableTable = new ContentVariableTable($option);

        $this->ContentVariableTable->setDataSource('mongo_test');
        $this->ContentVariableTable->deleteAll(true, false);
    }


    public function tearDown()
    {
        $this->ContentVariableTable->deleteAll(true, false);
        unset($this->ContentVariableTable);
        parent::tearDown();
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
                    'values' => array('300 Ksh', '400Ksh')
                    )
                )
            );

        $this->ContentVariableTable->create();
        $result = $this->ContentVariableTable->save($contentVariableTable);
        $this->assertTrue(isset($result));
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


}