<?php 

class GroupFixture extends CakeTestFixture 
{
    
    public $fields = array(
        'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 11, 'key' => 'primary'),
        'name' => array('type' => 'string', 'null' => false),
        'specific_program_access' => array('type' => 'boolean', 'null' => false));
    
    public $records = array(
        array(
            'id' => 1,
            'name' => 'Admin Group',
            'specific_program_access' => false
        ),
        array(
            'id' => 2,
            'name' => 'Program Manager Group',
            'specific_program_access' => true
        )
    );
    
}
