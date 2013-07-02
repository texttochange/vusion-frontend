<?php
/* Programs Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ProgramsController', 'Controller');
App::uses('Dialogue', 'Model');
App::uses('ScriptMaker', 'Lib');


class TestProgramsController extends ProgramsController 
{

    public $autoRender = false;


    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }

    protected function _instanciateVumiRabbitMQ() {
    }


}


class ProgramsControllerTestCase extends ControllerTestCase
{
    public $fixtures = array('app.program','app.group','app.user', 'app.programsUser');


    public function setUp()
    {
        Configure::write("mongo_db", "testdbmongo");
        parent::setUp();

        $this->Programs = new TestProgramsController();
        $this->Programs->constructClasses();
        
        $this->ShortCode = new ShortCode(array('database' => "testdbmongo"));
        $this->dropData();
    }
    
    
    protected function dropData()
    {
        $this->ShortCode->deleteAll(true, false);
    }

    
    public function tearDown()
    {
        unset($this->Programs);

        parent::tearDown();
    }


    protected function mockProgramAccess()
    {
        $programs = $this->generate(
            'Programs', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read')
                    ),
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    )
                )
            );
        
        $programs->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));

        return $programs;
    }
    
    
    protected function _saveShortcodesInMongoDatabase()
    {
        $shortcode1 = array(
                'country' => 'uganda',
                'shortcode' => 8282,
                'international-prefix' => 256
                );        
        $this->ShortCode->create();
        $this->ShortCode->save($shortcode1);
        
        $shortcode2 = array(
                'country' => 'uganda',
                'shortcode' => 8181,
                'international-prefix' => 256
                );
        $this->ShortCode->create();
        $this->ShortCode->save($shortcode2);
        
        $shortcode3 = array(
                'country' => 'kenya',
                'shortcode' => 21222,
                'international-prefix' => 254
                );
        $this->ShortCode->create();
        $this->ShortCode->save($shortcode3);
    }

/**
 * test methods
 *
 */

    public function testIndex()
    {
    	$Programs = $this->mockProgramAccess();

        $Programs->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls('1','1','1'));

        $this->_saveShortcodesInMongoDatabase();
        
        $this->testAction("/programs/index");
        $this->assertEquals(3, count($this->vars['programs']));
    }


    public function testIndex_hasSpecificProgramAccess_True()
    {
        $this->_saveShortcodesInMongoDatabase();
    	
        $Programs = $this->generate('Programs', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Auth' => array('user'),
                    ),
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    )
                ));
        
        $Programs->Auth
            ->staticExpects($this->once())
            ->method('user')
            ->will($this->returnValue(array(
                'id' => '2',
                'group_id' => '2')));

        $Programs->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->onConsecutiveCalls('false', 'false'));
                 
        
        $this->testAction("/programs/index");
        $this->assertEquals(1, count($this->vars['programs']));
    }
    
    
    public function testIndex_filter()
    {
        $this->_saveShortcodesInMongoDatabase();
        
        $Programs = $this->mockProgramAccess();                
        $this->testAction('/programs/index');
        
        $program1 = $this->vars['programs'][0];
        $db1 = $this->vars['programs'][0]['Program']['database'];
        $this->ProgramSetting = new ProgramSetting(array('database' => $db1));
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSetting->saveProgramSetting('shortcode','256-8282');
        
        $program2 = $this->vars['programs'][1];
        $db2 = $this->vars['programs'][1]['Program']['database'];
        $this->ProgramSetting = new ProgramSetting(array('database' => $db2));
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Daresalaam');
        $this->ProgramSetting->saveProgramSetting('shortcode','256-8181');
        
        $program3 = $this->vars['programs'][2];
        $db3 = $this->vars['programs'][2]['Program']['database'];
        $this->ProgramSetting = new ProgramSetting(array('database' => $db3));
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSetting->saveProgramSetting('shortcode','254-21222');
        
        # filter by program name only
        $this->mockProgramAccess();
        $this->testAction('/programs/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t');
        $this->assertEquals(2, count($this->vars['programs']));

        $this->mockProgramAccess();        
        $this->testAction('/programs/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=equal-to&filter_param%5B1%5D%5B3%5D=m6h');
        $this->assertEquals(1, count($this->vars['programs']));
        # filter by shortcode only
        $this->mockProgramAccess();
        $this->testAction('/programs/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=shortcode&filter_param%5B1%5D%5B2%5D=is&filter_param%5B1%5D%5B3%5D=8282');
        $this->assertEquals(1, count($this->vars['programs']));
        
        # filter by country only
        $this->mockProgramAccess();
        $this->testAction('/programs/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=country&filter_param%5B1%5D%5B2%5D=is&filter_param%5B1%5D%5B3%5D=Uganda');
        $this->assertEquals(2, count($this->vars['programs']));
        
        # filter by program name AND shortcode
        $this->mockProgramAccess();
        $this->testAction('/programs/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t&filter_param%5B2%5D%5B1%5D=shortcode&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=8282');
        $this->assertEquals(1, count($this->vars['programs']));
        
        # filter by program name AND country
        $this->mockProgramAccess();                                   
        $this->testAction('/programs/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=equal-to&filter_param%5B1%5D%5B3%5D=m6h&filter_param%5B2%5D%5B1%5D=country&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=Uganda');
        $this->assertEquals(1, count($this->vars['programs']));
        
        # filter by program name AND country AND shortcode
        $this->mockProgramAccess();                                   
        $this->testAction('/programs/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t&filter_param%5B2%5D%5B1%5D=country&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=Uganda&filter_param%5B3%5D%5B1%5D=shortcode&filter_param%5B3%5D%5B2%5D=is&filter_param%5B3%5D%5B3%5D=8282');
        $this->assertEquals(1, count($this->vars['programs']));
        
        # filter by program name OR shortcode
        $this->mockProgramAccess();
        $this->testAction('/programs/index?filter_operator=any&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t&filter_param%5B2%5D%5B1%5D=shortcode&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=8282');
        $this->assertEquals(2, count($this->vars['programs']));
        
        # filter by program name OR country
        $this->mockProgramAccess();                                   
        $this->testAction('/programs/index?filter_operator=any&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=equal-to&filter_param%5B1%5D%5B3%5D=m6h&filter_param%5B2%5D%5B1%5D=country&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=Kenya');
        $this->assertEquals(2, count($this->vars['programs']));
        
        # filter by program name OR country OR shortcode
        $this->mockProgramAccess();                                   
        $this->testAction('/programs/index?filter_operator=any&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t&filter_param%5B2%5D%5B1%5D=country&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=Uganda&filter_param%5B3%5D%5B1%5D=shortcode&filter_param%5B3%5D%5B2%5D=is&filter_param%5B3%5D%5B3%5D=21222');
        $this->assertEquals(3, count($this->vars['programs']));
        
        $this->ProgramSetting->deleteAll(true, false);
        
    }


    public function testView() 
    {
        $this->mockProgramAccess();

        $expected = array('Program' => array(
                    'id' => 1,
                    'name' => 'test',
                    'url' => 'test',
                    'database' => 'testdbprogram',
                    'created' => '2012-01-24 15:29:24',
                    'modified' => '2012-01-24 15:29:24'
                    ),
                'User'=> array(
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
                        ))
            );
        
        
        $this->testAction("/programs/view/1");
        
        $this->assertEquals($this->vars['program'], $expected);
    }


    public function testAdd() 
    {
        $Programs = $this->generate(
            'Programs', array(
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    '_startBackendWorker'
                    )
                )
            );

        $Programs
            ->expects($this->once())
            ->method('_startBackendWorker')
            ->will($this->returnValue(true));

        $data = array(
            'Program' => array(
                'name' => 'programName',
                'url' => 'programurl',
                'database'=> 'programdatabase'
                )
            );

        $this->testAction('/programs/add', array('data' => $data, 'method' => 'post'));
        
        $this->assertFileExist(
            WWW_ROOT . 'files/programs/programurl/');
        ##clean up
        rmdir(WWW_ROOT . 'files/programs/programurl');
    }

    public function testAdd_import() 
    {
        $Programs = $this->generate(
            'Programs', array(
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    '_startBackendWorker',
                    )
                )
            );

        $Programs
            ->expects($this->once())
            ->method('_startBackendWorker')
            ->will($this->returnValue(true));

        $maker = new ScriptMaker();
        $importFromDialogue = new Dialogue(array('database' => 'testdbprogram'));
        $importFromDialogue->deleteAll(true, false);
        $importFromDialogue->create();
        $dialogue = $maker->getOneDialogue();
        $dialogue['Dialogue']['activated'] = 1;
        $savedDialogue = $importFromDialogue->save($dialogue['Dialogue']);
        
        $importFromRequest = new Request(array('database' => 'testdbprogram'));
        $importFromRequest->deleteAll(true, false);
        $importFromRequest->create();
        $importFromRequest->save($maker->getOneRequest());
       
        $programDialogue = new Dialogue(array('database' => 'programdatabase'));
        $programDialogue->deleteAll(true, false);
        $programRequest = new Request(array('database' => 'programdatabase'));
        $programRequest->deleteAll(true, false);

        $data = array(
            'Program' => array(
                'name' => 'programName',
                'url' => 'programurl',
                'database'=> 'programdatabase',
                'import-dialogues-requests-from' => '1',
                )
            );

        $this->testAction('/programs/add', array('data' => $data, 'method' => 'post'));

        $this->assertEqual(1, $programDialogue->find('count'));
        $this->assertEqual(1, $programRequest->find('count'));

    }

    public function testDelete() 
    {
        $Programs = $this->generate(
            'Programs', array(
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    '_stopBackendWorker'    
                    )
                )
            );
        mkdir(WWW_ROOT . 'files/programs/test/');
   
        $Programs
            ->expects($this->once())
            ->method('_stopBackendWorker')
            ->will($this->returnValue(true));
        
        $this->testAction('/programs/delete/1');

        $this->assertFileNotExist(
            WWW_ROOT . 'files/programs/test/');

    }

   
}
