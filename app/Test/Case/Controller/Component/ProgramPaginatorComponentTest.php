<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('ProgramSetting', 'Model');
App::uses('ShortCode', 'Model');
App::uses('ProgramPaginatorComponent', 'Controller/Component');
App::uses('ProgramSpecificMongoModel', 'Model');


class TestProgramPaginatorComponentController extends Controller
{
    public $components = array('ProgramPaginator');
}


class ProgramPaginatorComponentTest extends CakeTestCase {

    public $ProgramPaginatorComponent = null;
    public $Controller = null;
    public $fixtures = array('app.program', 'app.user', 'app.programsUser');


    public function setUp() 
    {
        //Configure::write("mongo_db", "test_vusion");
        parent::setUp();
        $this->ProgramPaginator = new ProgramPaginatorComponent($this->getMock('ComponentCollection'), array());
        $this->request = new CakeRequest('programs/index');
		$this->request->params['pass'] = $this->request->params['named'] = array();
        $this->Controller = new Controller($this->request);
    }

    
    public function tearDown() {
        parent::tearDown();
        unset($this->ProgramPaginatorComponent);
        unset($this->Controller);
    }
    
    public function testPaginate()
    {
        

    }

    
    public function testPaginatePrograms()
    {
        $Controller = new TestProgramPaginatorComponentController($this->request);
		$Controller->uses = array('PaginatorControllerPrograms');
        $Controller->request->params['pass'] = array('1');
		$Controller->request->query = array();
		$Controller->constructClasses();
        
        $programs = array(1,2,3,4,5,6,7,8,9,10,11,12);
        
        # test returned results
        $results = $Controller->ProgramPaginator->paginatePrograms($programs);
        $this->assertEqual($results, array(1,2,3,4,5,6,7,8,9,10,11,12));
        
        # test paging
        $Controller->request->params['named'] = array('page' => '1');
        $results = $Controller->ProgramPaginator->paginatePrograms($programs);
		$this->assertEquals($Controller->params['paging']['PaginatorControllerPrograms']['page'], 1);
		$this->assertEquals($results, array(1,2,3,4,5,6,7,8,9,10,11,12));
		
		# limit set to 10 records per page
		$Controller->request->params['named'] = array('page' => '2');
		$Controller->ProgramPaginator->settings = array('limit' => '10', 'page' => '2','maxLimit' => 10, 'paramType' => 'named');
        $results = $Controller->ProgramPaginator->paginatePrograms($programs);
		$this->assertEquals($Controller->params['paging']['PaginatorControllerPrograms']['page'], 2);
        $this->assertEquals($Controller->params['paging']['PaginatorControllerPrograms']['pageCount'], 2);
		$this->assertEquals($results, array(11,12));
		
		# test limit records
		$Controller->request->params['named'] = array();
		$Controller->ProgramPaginator->settings = array('limit' => '1', 'page' => '1','maxLimit' => 10, 'paramType' => 'named');
        $results = $Controller->ProgramPaginator->paginatePrograms($programs);
        $this->assertSame($Controller->params['paging']['PaginatorControllerPrograms']['limit'], 1);
		$this->assertSame($Controller->params['paging']['PaginatorControllerPrograms']['page'], 1);
		$this->assertSame($Controller->params['paging']['PaginatorControllerPrograms']['pageCount'], 12);
		$this->assertSame($Controller->params['paging']['PaginatorControllerPrograms']['prevPage'], false);
		$this->assertSame($Controller->params['paging']['PaginatorControllerPrograms']['nextPage'], true);

        # test empty results
        $results = $Controller->ProgramPaginator->paginatePrograms(array());
        $this->assertSame($Controller->params['paging']['PaginatorControllerPrograms']['limit'], 1);
        $this->assertSame($Controller->params['paging']['PaginatorControllerPrograms']['page'], 1);
        $this->assertSame($Controller->params['paging']['PaginatorControllerPrograms']['pageCount'], 0);
        $this->assertSame($Controller->params['paging']['PaginatorControllerPrograms']['prevPage'], false);
        $this->assertSame($Controller->params['paging']['PaginatorControllerPrograms']['nextPage'], false);        
    }


    public function testFilterPrograms() {
        $programs[0] = array(
            'Program' => array(
                'id' => 3,
                'name' => 'M4h',
                'url' => 'm4h',
                'database' => 'm4h',
                'created' => '2012-01-24 15:29:24',
                'modified' => '2012-01-24 15:29:24'),
            );
        $tempProgramSetting = ProgramSpecificMongoModel::init('ProgramSetting', 'm4h', true);
        $tempProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        $tempProgramSetting->saveProgramSetting('shortcode','256-8282');
        $tempShortCode = ClassRegistry::init('ShortCode');
        $shortcode = array(
            'country' => 'uganda',
            'shortcode' => '8282',
            'international-prefix' => '256'
            );        
        $tempShortCode->create();
        $tempShortCode->save($shortcode);
      
        
        $programs[1] = array(
            'Program' => array(
                'id' => 4,
                'name' => 'tester',
                'url' => 'tester',
                'database' => 'tester',
                'created' => '2012-01-24 15:29:24',
                'modified' => '2012-01-24 15:29:24')
            );
        $tempProgramSetting = ProgramSpecificMongoModel::init('ProgramSetting', 'tester', true);
        $tempProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        $tempProgramSetting->saveProgramSetting('shortcode','256-8181');        
        $shortcode = array(
            'country' => 'uganda',
            'shortcode' => '8181',
            'international-prefix' => '256'
            );        
        $tempShortCode->create();
        $tempShortCode->save($shortcode);
        
        
        //Test simple condition   
        $conditions = array();
        $filtered = $this->ProgramPaginator->filterPrograms($programs, $conditions);
        $this->assertEqual(2, count($filtered));

        $conditions = array('shortcode' => '8282');        
        $filtered = $this->ProgramPaginator->filterPrograms($programs, $conditions);
        $this->assertEqual(1, count($filtered));
        $this->assertEqual('m4h', $filtered[0]['Program']['database']);

        $conditions = array('shortcode' => '8080');        
        $filtered = $this->ProgramPaginator->filterPrograms($programs, $conditions);
        $this->assertEqual(0, count($filtered));
        
        $conditions = array('shortcode' => '8181');        
        $filtered = $this->ProgramPaginator->filterPrograms($programs, $conditions);
        $this->assertEqual(1, count($filtered));
        $this->assertEqual('tester', $filtered[0]['Program']['database']);

        #test if there is no programs that match before the filtering
        $conditions = array('shortcode' => '8181');        
        $filtered = $this->ProgramPaginator->filterPrograms(array(), $conditions);
        $this->assertEqual(0, count($filtered));
    }
    
    
    
}
