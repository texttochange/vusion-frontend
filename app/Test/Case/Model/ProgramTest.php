<?php
/* Program Test cases generated on: 2012-01-24 15:57:36 : 1327409856*/
App::uses('Program', 'Model');


class ProgramTestCase extends CakeTestCase
{

    public $fixtures = array('app.program', 'app.user', 'app.programsUser');


    public function setUp()
    {
        parent::setUp();

        $this->Program = ClassRegistry::init('Program');
    }


    public function tearDown()
    {
        unset($this->Program);

        parent::tearDown();
    }

    
    public function testFind()
    {
        $result   = $this->Program->find();
        $expected = array(
            'Program' => array(
                'id' => 2,
                'name' => 'm6h',
                'url' => 'm6h',
                'database' => 'm6h',            
                'created' => '2012-01-24 15:29:24',
                'modified' => '2012-01-24 15:29:24'
                ),
            'Program' => array(
                'id' => 1,
                'name' => 'test',
                'url' => 'test',
                'database' => 'testdbprogram',
                'created' => '2012-01-24 15:29:24',
                'modified' => '2012-01-24 15:29:24'
                ),
            'User' => array(
                0 => array(
                    'id' => 1,
                    'username' => 'gerald',
                    'password' => 'geraldpassword',
                    'email' => 'gerald@here.com',
                    'group_id' => 1,
                    'created' => '2012-01-24 15:34:07',
                    'modified' => '2012-01-24 15:34:07',
                    'ProgramsUser' => array(
                        'id' => 1,
                        'program_id' => '1',
                        'user_id' => '1',
                        ),
                    ),
                )
            );
        
        $this->assertEquals($expected, $result);
    }
    
    
    public function testFindAuthorized()
    {
        $result   = $this->Program->find(
            'authorized',
            array(
                'specific_program_access' => 'true',
                'user_id' => 1
                )
            );
        $this->assertEquals(1, count($result));
    }
    
    
    public function testCountAuthorized()
    {
        $result   = $this->Program->find(
            'count',
            array(
                'specific_program_access' => 'true',
                'user_id' => 1
                )
            );
        $this->assertEquals(1, $result);
        
        $result   = $this->Program->find(
            'count'
            );
        $this->assertEquals(2, $result);
    }

    public function testDeleteProgram()
    {
        $this->Program->id = 1;
        $this->Program->deleteProgram();
        $this->assertEquals(1,$this->Program->find('count'));
    }


}
