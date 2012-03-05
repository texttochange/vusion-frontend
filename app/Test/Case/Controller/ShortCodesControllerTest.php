<?php
/* ShortCodes Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ShortCodesController', 'Controller');

/**
 * TestShortCodesController *
 */
class TestShortCodesController extends ShortCodesController {
/**
 * Auto render
 *
 * @var boolean
 */
	public $autoRender = false;

/**
 * Redirect action
 *
 * @param mixed $url
 * @param mixed $status
 * @param boolean $exit
 * @return void
 */
	public function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

/**
 * ShortCodesController Test Case
 *
 */
class ShortCodesControllerTestCase extends ControllerTestCase 
{
	
/**
 * setUp method
 *
 * @return void
 */
	public function setUp() 
	{
		parent::setUp();

		$this->ShortCodes = new TestShortCodesController();
		ClassRegistry::config(array('ds' => 'mongo_test'));
		//$this->instanciateShortCodesModel();
		$this->dropData();
	}


 	protected function instanciateShortCodesModel() {
	    $options = array('database' => 'test');
	    $this->ShortCodes->ShortCode = new ShortCode($options);
	}	
  
  	protected function dropData() {
		$this->instanciateShortCodesModel();
  		$this->ShortCodes->ShortCode->deleteAll(true, false);
	}

	public function tearDown() 
	{
		$this->dropData();
		
		unset($this->ShortCodes);

		parent::tearDown();
	}
	
	
	public function testIndex() {
	    $ShortCodes = $this->generate('ShortCodes', array(
			'components' => array(
				'Acl' => array('check'),
				'Session' => array('read')
			),
			'models' => array(
				'Group' => array()
				)
		));
	    
	    $ShortCodes->Acl
			->expects($this->any())
			->method('check')
			->will($this->returnValue('true'));
		
	    $ShortCodes->Session
			->expects($this->any())
			->method('read')
			->will($this->onConsecutiveCalls('1','1','1'));
	    
	    $this->instanciateShortCodesModel();
	    $this->ShortCodes->ShortCode->create();
	    $this->ShortCodes->ShortCode->save(array(
	    	    	'country' => 'uganda',
	    	    	'shortcode' => 8282,
	    	    	'internationalprefix' => 256
	    	    	));
		
	    $this->testAction("/shortCodes/index");
		
	    $this->assertEquals(1, count($this->vars['shortcodes']));		
	}

	
}
