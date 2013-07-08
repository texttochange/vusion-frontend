<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('EmulatePaginatorComponent', 'Controller/Component');

class TestEmulatePaginatorComponentController extends Controller {
    public $components = array('EmulatePaginator');
}


class EmulatePaginatorComponentTest extends CakeTestCase {

    public $EmulatePaginatorComponent = null;
    public $Controller = null;
    public $fixtures = array('app.program', 'app.user', 'app.programsUser');


    public function setUp() 
    {
        parent::setUp();
        $this->EmulatePaginator = new EmulatePaginatorComponent($this->getMock('ComponentCollection'), array());
        $this->request = new CakeRequest('programs/index');
		$this->request->params['pass'] = $this->request->params['named'] = array();
        $this->Controller = new Controller($this->request);

    }

    public function tearDown() {
        parent::tearDown();
        // Clean up after we're done
        unset($this->EmulatePaginatorComponent);
        unset($this->Controller);
    }
    
    
    public function testPaginate()
    {
        //print_r($this->request);
        $Controller = new TestEmulatePaginatorComponentController($this->request);
		$Controller->uses = array('PaginatorControllerPrograms');
        $Controller->request->params['pass'] = array('1');
		$Controller->request->query = array();
		$Controller->constructClasses();
        
        $programs = array(1,2,3,4,5,6,7,8,9,10,11,12);
        
        $results = $Controller->EmulatePaginator->paginate($programs);
        $this->assertEqual($results, array(1,2,3,4,5,6,7,8,9,10,11,12));
        
        $Controller->request->params['named'] = array('page' => '-1');
        $results = $Controller->EmulatePaginator->paginate($programs);
		$this->assertEquals($Controller->params['paging']['PaginatorControllerPrograms']['page'], 1);
		$this->assertEquals($results, array(1,2,3,4,5,6,7,8,9,10,11,12));
    }
    
}
