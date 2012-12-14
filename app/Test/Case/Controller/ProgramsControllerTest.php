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


}


class ProgramsControllerTestCase extends ControllerTestCase
{

    public $fixtures = array('app.program','app.group','app.user', 'app.programsUser');


    public function setUp()
    {
        parent::setUp();

        $this->Programs = new TestProgramsController();
        $this->Programs->constructClasses();
    }

    
    public function tearDown()
    {
        unset($this->Programs);

        parent::tearDown();
    }


    protected function mockProgramAccess()
    {
        $programs = $this->generate('Programs', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
            ),
            'methods' => array(
                'paginate'
            )
        ));
        
        $programs->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));

        return $programs;
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
    	
        $programs = $this->generate('Programs', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read', 'write')
            ),
        ));
        
        $programs->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->onConsecutiveCalls('false', 'false'));
        

        $programs->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls('2','2','2','2','2','2','2','2','2'));
            
        $this->testAction("/programs/index");
        $this->assertEquals(1, count($this->vars['programs']));
    }


    public function testView() 
    {
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
        $Programs = $this->generate('Programs', array(
            'methods' => array(
                '_startBackendWorker'
            )
            ));

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
    }

    public function testAdd_import() 
    {
        $Programs = $this->generate('Programs', array(
            'methods' => array(
                '_startBackendWorker'
            )
            ));

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
        
        $importFromRequest = new Request(array('database' => 'm6h'));
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
                'import-dialogues-from' => '1',
                'import-requests-from' => '2',
                )
            );

        $this->testAction('/programs/add', array('data' => $data, 'method' => 'post'));

        $this->assertEqual(1, $programDialogue->find('count'));
        $this->assertEqual(1, $programRequest->find('count'));

    }


    public function testEdit() 
    {

    }


    public function testDelete() 
    {

        $Programs = $this->generate('Programs', array(
            'methods' => array(
                '_stopBackendWorker'
            )
            ));
        
        $Programs
            ->expects($this->once())
            ->method('_stopBackendWorker')
            ->will($this->returnValue(true));
        
        $this->testAction('/programs/delete/1');

    }

    
}
