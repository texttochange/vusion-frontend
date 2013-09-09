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


    public function testSaveProgram_fail()
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


    public function testDeleteProgram()
    {
        $this->Program->id = 1;
        $this->Program->deleteProgram();
        $this->assertEquals(2,$this->Program->find('count'));
    }
    
    
    public function testMatchProgramByShortcodeAndCountry()
    {
        $programM4H['Program'] = array(
            'id' => 3,
            'name' => 'M4h',
            'url' => 'm4h',
            'database' => 'm4h',
            'created' => '2012-01-24 15:29:24',
            'modified' => '2012-01-24 15:29:24'
            );
        
        $this->Program->create();
        $savedProgramM4H = $this->Program->save($programM4H);
        
        $programTester['Program'] = array(
            'id' => 4,
            'name' => 'tester',
            'url' => 'tester',
            'database' => 'tester',
            'created' => '2012-01-24 15:29:24',
            'modified' => '2012-01-24 15:29:24'
            );
        
        $this->Program->create();
        $savedProgramTester = $this->Program->save($programTester);
        
        $codes = array(
            array(
                'shortcode' => '8181',
                'international-prefix' => '256',
                'country' => 'uganda',
                'supported-internationally' => '0',
                'support-customized-id' => '1'
                ),
            array(
                'shortcode' => '8282',
                'international-prefix' => '256',
                'country' => 'uganda',
                'supported-internationally' => '0',
                'support-customized-id' => '1'
                )
            );
        
        $this->ProgramSettingM4H = new ProgramSetting($programM4H['Program']['database']);
        $this->ProgramSettingM4H->saveProgramSetting('timezone', 'Africa/Kampala');
        $this->ProgramSettingM4H->saveProgramSetting('shortcode', '256-8282');
        
        $this->ProgramSettingTester = new ProgramSetting($programTester['Program']['database']);
        $this->ProgramSettingTester->saveProgramSetting('timezone', 'Africa/Kampala');
        $this->ProgramSettingTester->saveProgramSetting('shortcode', '256-8181');

        //Test simple condition        
        $conditions = array('shortcode' => '8282');
        $result = $this->Program->matchProgramByShortcodeAndCountry($savedProgramM4H, $conditions, $codes);
        $this->assertEquals($result[0]['Program']['name'], 'M4h');
        $this->assertEquals(1, count($result));        

        //Test Or condtions
        $conditions = array(
            '$or' => array(
                array('shortcode' => '8234'),
                array('shortcode' => '8181')
                )
            );
        $result = $this->Program->matchProgramByShortcodeAndCountry($savedProgramM4H, $conditions, $codes);
        $this->assertEquals(1, count($result));

        //Test And conditions
        $conditions = array(
            '$and' => array(
                array('shortcode' => '8181'),
                array('country' => 'uganda')
                )
            );
        $result = $this->Program->matchProgramByShortcodeAndCountry($savedProgramTester, $conditions, $codes);
        $this->assertEquals($result[0]['Program']['name'], 'tester');
        $this->assertEquals(1, count($result));
        
        //Clear data in program settings
        $this->ProgramSettingM4H->deleteAll(true, false);
        $this->ProgramSettingTester->deleteAll(true, false);
    }


}
