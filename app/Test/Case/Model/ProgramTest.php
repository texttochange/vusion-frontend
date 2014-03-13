<?php
/* Program Test cases generated on: 2012-01-24 15:57:36 : 1327409856*/
App::uses('Program', 'Model');
App::uses('ProgramSetting', 'Model');


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
/*    
    
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
        $this->assertEquals(3, $result);
    }
    
    public function testSaveProgram_ok()
    {
        $program['Program'] = array(
            'id' => 3,
            'name' => 'M4h',
            'url' => 'm4h',
            'database' => 'm4h',
            'created' => '2012-01-24 15:29:24',
            'modified' => '2012-01-24 15:29:24'
            );
        
        $this->Program->create();
        $savedProgram = $this->Program->save($program);
        $this->assertEqual($this->Program->validationErrors, array());
    }
    
    
    public function testSaveProgram_fail_url_database_format()
    {
        $program = array(
            'id' => 5,
            'name' => 'M7h',
            'url' => 'm7H',
            'database' => 'm7h',            
            'created' => '2012-01-24 15:29:24',
            'modified' => '2012-01-24 15:29:24'
            );
        
        $this->Program->create();
        $this->assertFalse($this->Program->save($program));
        $this->assertEqual(
            $this->Program->validationErrors['url'], 
            array('Minimum of 3 characters, can only be composed of lowercase letters and digits.'));
        
        $program['url'] = 'm 7h';
        
        $this->Program->create();
        $this->assertFalse($this->Program->save($program));
        $this->assertEqual(
            $this->Program->validationErrors['url'], 
            array('Minimum of 3 characters, can only be composed of lowercase letters and digits.'));
        
        $program['url'] = 'm7h';
        $program['database'] = 'M7h';
        
        $this->Program->create();
        $this->assertFalse($this->Program->save($program));
        $this->assertEqual(
            $this->Program->validationErrors['database'], 
            array('Minimum of 3 characters, can only be composed of lowercase letters and digits.'));
        
        $program['database'] = 'm7 h';
        
        $this->Program->create();
        $this->assertFalse($this->Program->save($program));
        $this->assertEqual(
            $this->Program->validationErrors['database'], 
            array('Minimum of 3 characters, can only be composed of lowercase letters and digits.'));
    }
    
    
    public function testSaveProgram_fail_url_database_static()
    {
        $program = array(
            'id' => 5,
            'name' => 'something new',
            'url' => 'img',
            'database' => 'vusion',            
            'created' => '2012-01-24 15:29:24',
            'modified' => '2012-01-24 15:29:24'
            );
        
        $this->Program->create();
        $this->assertFalse($this->Program->save($program));
        $this->assertEqual(
            $this->Program->validationErrors['url'][0], 
            'This url is not allowed to avoid overwriting a static Vusion url, please choose a different one.');
        $this->assertEqual(
            $this->Program->validationErrors['database'][0], 
            'This database name is not allowed to avoid overwriting a static Vusion database, please choose a different one.');
    }
    
    
    public function testDeleteProgram()
    {
        $this->Program->id = 1;
        $this->Program->deleteProgram();
        $this->assertEquals(2,$this->Program->find('count'));
    }
*/    
    
    public function testMatchProgramConditions()
    {
        $programDetailM4H = array(
            'Program' => array(
                'id' => 3,
                'name' => 'M4h',
                'url' => 'm4h',
                'database' => 'm4h',
                'shortcode' => '8282',
                'country' => 'uganda',
                'created' => '2012-01-24 15:29:24',
                'modified' => '2012-01-24 15:29:24'),
            'shortcode' => array(
                'shortcode' => '8282',
                'international-prefix' => '256',
                'country' => 'uganda',
                'supported-internationally' => '0',
                'support-customized-id' => '1'),
            'settings' => array(
                'timezone' => 'Africa/Kampala',
                'shortcode' => '256-8282')
            );
        
        $programDetailTester = array(
            'Program' => array(
                'id' => 4,
                'name' => 'tester',
                'url' => 'tester',
                'database' => 'tester',
                'shortcode' => '8181',
                'country' => 'uganda',
                'created' => '2012-01-24 15:29:24',
                'modified' => '2012-01-24 15:29:24'),
            'shortcode' => array(
                'shortcode' => '8181',
                'international-prefix' => '256',
                'country' => 'uganda',
                'supported-internationally' => '0',
                'support-customized-id' => '1'),
            'settings' => array(
                'timezone' => 'Africa/Kampala',
                'shortcode' => '256-8181')
            );
                        
        //Test simple condition        
        $conditions = array('shortcode' => '8282');
        $this->assertTrue(
            Program::matchProgramConditions($programDetailM4H, $conditions));
        $this->assertFalse(
            Program::matchProgramConditions($programDetailTester, $conditions));

        //Test OR
        $conditions = array(
            '$or' => array(
                array('shortcode' => '8234'),
                array('shortcode' => '8181')
                )
            );
        $this->assertFalse(
            Program::matchProgramConditions($programDetailM4H, $conditions));
        $this->assertTrue(
            Program::matchProgramConditions($programDetailTester, $conditions));
        
        //Test AND
        $conditions = array(
            '$and' => array(
                array('shortcode' => '8181'),
                array('country' => 'uganda')
                )
            );
        $this->assertFalse(
            Program::matchProgramConditions($programDetailM4H, $conditions));
        $this->assertTrue(
            Program::matchProgramConditions($programDetailTester, $conditions));
    }

    public function testValidateProgramCondition()
    {
        $programDetailM4H = array(
            'Program' => array(
                'id' => 3,
                'name' => 'M4h',
                'url' => 'm4h',
                'database' => 'm4h',
                'shortcode' => '8282',
                'country' => 'uganda',
                'created' => '2012-01-24 15:29:24',
                'modified' => '2012-01-24 15:29:24'),
            );
                        
        $this->assertTrue(
            Program::validProgramCondition($programDetailM4H, 'shortcode', '8282'));
        $this->assertFalse(
            Program::validProgramCondition($programDetailM4H, 'shortcode', '8181'));

        $this->assertTrue(
            Program::validProgramCondition($programDetailM4H, 'country', 'Uganda'));
        $this->assertFalse(
            Program::validProgramCondition($programDetailM4H, 'country', 'kenya'));

        $this->assertTrue(
            Program::validProgramCondition($programDetailM4H, 'name', 'm4h'));
        $this->assertFalse(
            Program::validProgramCondition($programDetailM4H, 'name', 'm6h'));

        $this->assertTrue(
            Program::validProgramCondition($programDetailM4H, 'name LIKE', 'm%'));
        $this->assertFalse(
            Program::validProgramCondition($programDetailM4H, 'name LIKE', 't%'));
    }
    

    public function testValidateProgramCondition_missingShortcodeSettings()
    {
        $programDetails = array(
            'Program' => array(
                'id' => 3,
                'name' => 'M4h',
                'url' => 'm4h',
                'database' => 'm4h',
                'created' => '2012-01-24 15:29:24',
                'modified' => '2012-01-24 15:29:24'),
            );
                        
        $this->assertFalse(
            Program::validProgramCondition($programDetails, 'shortcode', '8282'));
        $this->assertFalse(
            Program::validProgramCondition($programDetails, 'country', 'Uganda'));

        $programDetails = array(
            'Program' => array(
                'id' => 3,
                'name' => 'M4h',
                'url' => 'm4h',
                'database' => 'm4h',
                'country' => 'uganda',
                'created' => '2012-01-24 15:29:24',
                'modified' => '2012-01-24 15:29:24'),
            );

        $this->assertTrue(
            Program::validProgramCondition($programDetails, 'country', 'Uganda'));
        $this->assertFalse(
            Program::validProgramCondition($programDetails, 'country', 'kenya'));
    }
 
}
