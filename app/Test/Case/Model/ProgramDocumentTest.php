<?php
App::uses('ProgramDocument', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class ProgramDocumentTestCase extends CakeTestCase {
	
	protected $_config = array(
		'datasource' => 'Mongodb.MongodbSource',
		'host' => 'localhost',
		'login' => '',
		'password' => '',
		'database' => 'test_mongo',
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
		
		$this->ProgramDocument = ClassRegistry::init('ProgramDocument');
		$this->ProgramDocument->setDataSource('mongo_test');
		
		$this->mongodb =& ConnectionManager::getDataSource($this->ProgramDocument->useDbConfig);
		$this->mongodb->connect();
		
		$this->dropData();
		
	}
	
	public function tearDown() {
		unset($this->ProgramDocument);
		
		parent::tearDown();
	}

	
/**
 * Drop database
 *
 * @return void
 * @access public
 */
	public function dropData() {
		try {
			$db = $this->mongodb
				->connection
				->selectDB($this->_config['database']);

			foreach($db->listCollections() as $collection) {
				$collection->drop();
			}
		} catch (MongoException $e) {
			trigger_error($e->getMessage());
		}
	}

	
	public function testSave() {
		$data = array(
			'machin' => array('un truc'),
			'RequestResponse' => array('un autre truc'),
			);	
		$this->ProgramDocument->recursive = -1;
		$this->ProgramDocument->create();
		$saveResult = $this->ProgramDocument->save($data);
		$this->assertTrue(!empty($saveResult) && is_array($saveResult));
	
		$result = $this->ProgramDocument->find('all');
		$this->assertEqual(1, count($result));
		
	}
	
}



?>
