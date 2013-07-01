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
    var $databaseName = "testdbmongo";

    public $fixtures = array('app.program','app.group','app.user', 'app.programsUser');


    public function setUp()
    {
        parent::setUp();

        $this->Programs = new TestProgramsController();
        $this->Programs->constructClasses();
        
        $this->instanciateModels();
        $this->dropData();
    }
    
    
    protected function instanciateModels()
    {
        $this->ShortCode = new ShortCode(array('database' => $this->databaseName));
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
                    'paginate'
                    )
                )
            );
        
        $programs->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));

        return $programs;
    }
    
    
    protected function mockProgramAccess_with_paginate()
    {
        $programsArray = array(
            0 => array(
                'Program' => array(
                    'id' => 1,
                    'name' => 'test',
                    'url' => 'test',
                    'database' => 'testdbprogram',
                    'created' => '2012-01-24 15:29:24',
                    'modified' => '2012-01-24 15:29:24')),
            1 => array(
                'Program' => array(
                    'id' => 2,
                    'name' => 'm6h',
                    'url' => 'm6h',
                    'database' => 'm6h',            
                    'created' => '2012-01-24 15:29:24',
                    'modified' => '2012-01-24 15:29:24'))
            );
        
        $Programs = $this->mockProgramAccess();
        $Programs
                ->expects($this->once())
                ->method('paginate')
                ->will($this->returnValue($programsArray));
                
        return $Programs;
    }

/**
 * test methods
 *
 */
/*
    public function testIndex()
    {
    	$Programs = $this->mockProgramAccess();

        $Programs->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls('1','1','1'));
        
        $Programs
                ->expects($this->once())
                ->method('paginate')
                ->will($this->returnValue(array(
                    0 => array(
                        'Program' => array(
                            'database' => 'test1')),
                    1 => array(
                        'Program' => array(
                            'database' => 'test2'))
                    )));

        $this->testAction("/programs/index");
        $this->assertEquals(2, count($this->vars['programs']));
    }


    public function testIndex_hasSpecificProgramAccess_True()
    {
    	
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
  */  
    
    public function testIndex_filter()
    {
        $programsArray = array(
            0 => array(
                'Program' => array(
                    'id' => 1,
                    'name' => 'test',
                    'url' => 'test',
                    'database' => 'testdbprogram',
                    'created' => '2012-01-24 15:29:24',
                    'modified' => '2012-01-24 15:29:24')),
            1 => array(
                'Program' => array(
                    'id' => 2,
                    'name' => 'm6h',
                    'url' => 'm6h',
                    'database' => 'm6h',            
                    'created' => '2012-01-24 15:29:24',
                    'modified' => '2012-01-24 15:29:24'))
            );
        //$Programs = $this->mockProgramAccess_with_paginate();
        $Programs = $this->mockProgramAccess();
        $Programs
                ->expects($this->once())
                ->method('paginate')
                ->will($this->returnValue($programsArray));
                
        $this->testAction('programs/index');
        
        $program1 = $this->vars['programs'][0];
        $db1 = $this->vars['programs'][0]['Program']['database'];
        $this->ProgramSetting = new ProgramSetting(array('database' => $db1));
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSetting->saveProgramSetting('shortcode','8282');
        
         # filter by program name only
         echo "starting filter tests\n";
        //$this->mockProgramAccess_with_paginate();
        //$this->mockProgramAccess();
        $this->testAction('programs/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t');
        //print_r($this->vars['programs']);
        $this->assertEquals(1, count($this->vars['programs']));
        
        $this->testAction('programs/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=equal-to&filter_param%5B1%5D%5B3%5D=m6h');
        $this->assertEquals(1, count($this->vars['programs']));
        
        /*$this->mockProgramAccess_with_paginate();
        $this->testAction('programs/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=equal-to&filter_param%5B1%5D%5B3%5D=m6h');
        $this->assertEquals(1, count($this->vars['programs']));
        /*$shortcode1 = array(
                'country' => 'uganda',
                'shortcode' => 8282,
                'international-prefix' => 256
                );
        
        $this->ShortCode->create();
        $this->ShortCode->save($shortcode1);
        /*$shortcode2 = array(
                'country' => 'uganda',
                'shortcode' => 8181,
                'international-prefix' => 256
                );
        $shortcode3 = array(
                'country' => 'kenya',
                'shortcode' => 21222,
                'international-prefix' => 254
                );
        $this->ShortCode->create();
        $this->ShortCode->save($shortcode2);
        $this->ShortCode->create();
        $this->ShortCode->save($shortcode3);*/
        
        /*$this->testAction('programs/index', array('return' => 'vars'));
        
        $program1 = $this->vars['programs'][0];
        $db1 = $this->vars['programs'][0]['Program']['database'];
        $this->ProgramSetting = new ProgramSetting(array('database' => $db1));
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSetting->saveProgramSetting('shortcode','8282');
        
        //$shortcode = $this->ProgramSetting->find('programSetting', array('key'=>'shortcode'));
        //$code      = $this->ShortCode->find('prefixShortCode', array('prefixShortCode'=> $shortcode[0]['ProgramSetting']['value']));
        //print_r($shortcode);
        # filter by program name only
        $this->testAction('programs/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t',
            array('return' => 'vars'));
        $this->assertEquals(1, count($this->vars['programs']));
        
        $this->testAction('programs/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=equal-to&filter_param%5B1%5D%5B3%5D=m6h',
            array('return' => 'vars'));
        $this->assertEquals(1, count($this->vars['programs']));
        //print_r($this->vars['programs']);
        # filter by shortcode only
        $this->testAction('programs/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=shortcode&filter_param%5B1%5D%5B2%5D=equal-to&filter_param%5B1%5D%5B3%5D=8282',
            array('return' => 'vars'));
        //print_r($this->vars['programs']);
        $this->assertEquals(1, count($this->vars['programs']));
        
        $this->ProgramSetting->deleteAll(true, false);*/
    }

/*
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

  */ 
}
