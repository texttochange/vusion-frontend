<?php
/* Programs Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ProgramsController', 'Controller');


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
        $Programs = $this->generate('Programs', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
            ),
            'methods' => array(
                'paginate'
            )
        ));
        
        $Programs->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));

        return $Programs;
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
    	
        $Programs = $this->generate('Programs', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
            ),
        ));
        
        $Programs->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
        

        $Programs->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls('2','2','2'));
            
        $this->testAction("/programs/index");
        $this->assertEquals(1, count($this->vars['programs']));
    }


    public function testView() 
    {
        $expected = array('Program' => array(
                    'id' => 1,
                    'name' => 'test',
                    'url' => 'test',
                    'database' => 'test',
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
                'url' => 'programUrl',
                'database'=> 'programDatabase'
                )
            );

        $this->testAction('/programs/add', array('data' => $data, 'method' => 'post'));
    }


    public function testEdit() 
    {

    }


    public function testDelete() 
    {

    }


}
