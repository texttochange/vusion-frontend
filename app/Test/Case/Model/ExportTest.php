<?php
App::uses('Export', 'Model');


class ExportTestCase extends CakeTestCase
{


	public function setUp()
	{
		parent::setUp();
     	$this->Export = ClassRegistry::init('Export');
	}


	public function tearDown()
	{
		$this->Export->DeleteAll(true, false);
		unset($this->Export);
		parent::tearDown();
	}


	public function testSave_failed()
	{
		$export = array(
			'timestamp' => '',
			'database' => '',
			'collection' => '',
			'filters' => '',
			'order' => '',
			'conditions' => '',
			'status' => '',
			'size' => '',
			'file-full-name' => ''
			);

		$this->Export->save($export);
		$this->assertTrue(isset($this->Export->validationErrors['timestamp']));
		$this->assertTrue(isset($this->Export->validationErrors['database']));
		$this->assertTrue(isset($this->Export->validationErrors['collection']));
		$this->assertTrue(isset($this->Export->validationErrors['status']));
		$this->assertTrue(isset($this->Export->validationErrors['size']));
		$this->assertTrue(isset($this->Export->validationErrors['file-full-name']));
	}


	public function testSave_ok()
	{
		$export = array(
			'timestamp' => '2014-12-12T10:10:00',
			'database' => 'something',
			'collection' => 'history',
			'filters' => '',
			'order' => '',
			'conditions' => '',
			'status' => 'queued',
			'size' => 0,
			'file-full-name' => '/var/vusion/app/webroot/files/programs/myprogram/Export_2014-12-12_10_10.csv'
			);

		$savedExport = $this->Export->save($export);
		$this->assertTrue(isset($savedExport['Export']));
	}


	public function testSave_ok_conditions()
	{
		$export = array(
			'timestamp' => '2014-12-12T10:10:00',
			'database' => 'something',
			'collection' => 'history',
			'filters' => '',
			'order' => '',
			'conditions' => array('$and' => array(
				array('phone' => '+06'),
				array('tags' => array('$in' => array('geek'))))),
			'status' => 'queued',
			'size' => 0,
			'file-full-name' => '/var/vusion/app/webroot/files/programs/myprogram/Export_2014-12-12_10_10.csv'
			);

		$savedExport = $this->Export->save($export);
		$this->assertTrue(isset($savedExport['Export']));
	}


	public function testDelete_ok()
	{
		$file = new File('testFile.csv', true);
		$export = array(
			'timestamp' => '2014-12-12T10:10:00',
			'database' => 'something',
			'collection' => 'history',
			'filters' => '',
			'order' => '',
			'conditions' => '',
			'status' => 'success',
			'size' => 0,
			'file-full-name' => $file->path
			);
		$savedExport = $this->Export->save($export);

		$this->Export->delete();

		$this->assertFalse($file->exists());
	}



}