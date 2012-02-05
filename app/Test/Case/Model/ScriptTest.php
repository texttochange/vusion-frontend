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
		
		$options = array('database' => 'test');
	
		$this->Script = new Script($options);
		
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
	
}



?>
