<?php
App::uses('Script', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class ScriptTestCase extends CakeTestCase {
	
	protected $_config = array(
		'datasource' => 'Mongodb.MongodbSource',
		'host' => 'localhost',
		'login' => '',
		'password' => '',
		'database' => 'test',
		'port' => 27017,
		'prefix' => '',
		'persistent' => true,
	);

	public function setUp(){
		parent::setUp();
		
		$connections = ConnectionManager::enumConnectionObjects();
		
		if (!empty($connections['test']['classname']) && $connections['test']['classname'] === 'mongodbSource'){
			$config = new DATABASE_CONFIG();
			$this->_config = $config->test;
		}
		
		ConnectionManager::create('mongo_test', $this->_config);
		$this->Mongo = new MongodbSource($this->_config);
		
		$options = array('database' => 'test');
		$this->Script = new Script($options);
	
		$this->Script->setDataSource('mongo_test');
		
		$this->mongodb =& ConnectionManager::getDataSource($this->Script->useDbConfig);
		$this->mongodb->connect();
		
		$this->dropData();
		
	}
	
	public function tearDown() {
		unset($this->Script);
		
		parent::tearDown();
	}

	
/**
 * Drop database
 *
 * @return void
 * @access public
 */
	public function dropData() {
		
		$this->Script->deleteAll(true,false);

	}

	
	public function testSave() {
		$data = array(
			'script' => 1
			);	
		$this->Script->recursive = -1;
		$this->Script->create();
		$saveResult = $this->Script->save($data);
		$this->assertTrue(!empty($saveResult) && is_array($saveResult));
	
		$result = $this->Script->find('all');
		$this->assertEqual(count($result), 1);
		
	}
	
	/*
	public function testUpdateDraft() {
		$data['Script'] = array(
			'something' => 1
			);
		$updateData['Script'] = array(
			'something' => 2
			);
		
		$this->Script->recursive = -1;
		$this->Script->create();
		$saveResult = $this->Script->save($data);
		
		$this->Script->create();
		$saveResult = $this->Script->save($updateData);
		
		$result = $this->Script->find('draft');
		$this->assertEqual(count($result), 1);
		$record = $this->Script->find('draft');
		$this->assertEquals($record[0]['Script']['something'],2);		
	}
	*/
	
	public function testFindCountDraft() {
		
		$result = $this->Script->find('countDraft');
		//print_r($result);
		$this->assertEquals(count($result), 0);
	
		
		$data['Script'] = array(
			'something' => 1
			);
		
		$this->Script->recursive = -1;
		$this->Script->create();
		$this->Script->save($data);
		
		$result = $this->Script->find('countDraft');
		//print_r($result);
		$this->assertEquals(count($result), 1);
	
		$this->Script->create();
		$this->Script->save($data);

		$result = $this->Script->find('countDraft');		
		$this->assertEquals(count($result), 2);
	
	}
	
	public function testMakeTurnDraftActive(){
		$data['Script'] = array(
			'script' => array(
				'do' => 'something'
				)
			);
		$this->Script->recursive = -1;
		$this->Script->create();
		$id = $this->Script->save($data);
		
		$draft = $this->Script->find('draft');
		$this->assertEquals($draft[0]['Script']['activated'], 0);
		
		$this->Script->makeDraftActive();
		
		$this->assertEquals(count($this->Script->find('draft')), 0);
		
		$active = $this->Script->find('active');
		$this->assertEquals($active[0]['Script']['activated'], 1);
		$this->assertEquals($active[0]['Script']['script'], $data['Script']['script']);
		
	}
	
	public function testMakeTurnDraftActive_noDraft(){
		$result = $this->Script->makeDraftActive();
		$this->assertEquals($result, false);
	}
	
}



?>
