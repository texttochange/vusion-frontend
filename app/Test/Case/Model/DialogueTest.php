<?php
App::uses('Dialogue', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class DialogueTestCase extends CakeTestCase {
	
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
		
		$this->Dialogue = ClassRegistry::init('Dialogue');
		$this->Dialogue->setDataSource('mongo_test');
		
		$this->mongodb =& ConnectionManager::getDataSource($this->Dialogue->useDbConfig);
		$this->mongodb->connect();
		
		$this->dropData();
		
	}
	
	public function tearDown() {
		unset($this->Dialogue);
		
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
		$this->Dialogue->recursive = -1;
		$this->Dialogue->create();
		$saveResult = $this->Dialogue->save($data);
		$this->assertTrue(!empty($saveResult) && is_array($saveResult));
	
		$result = $this->Dialogue->find('all');
		$this->assertEqual(1, count($result));
		
	}
	
}



?>
