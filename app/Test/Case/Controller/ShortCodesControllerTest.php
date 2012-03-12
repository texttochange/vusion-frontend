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
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
 
	
}

/**
 * ShortCodesController Test Case
 *
 */
class ShortCodesControllerTestCase extends ControllerTestCase 
{
	var $database_name = "testdbmongo";
	
/**
 * setUp method
 *
 * @return void
 */
	public function setUp() 
	{
		Configure::write("mongo_db",$this->database_name);
		parent::setUp();

		$this->ShortCodes = new TestShortCodesController();
		//ClassRegistry::config(array('ds' => 'mongo_test'));
		$this->instanciateShortCodesModel();
		$this->dropData();
	}
	

 	protected function instanciateShortCodesModel() {
	    $options = array('database' => $this->database_name);
	    $this->ShortCodes->ShortCode = new ShortCode($options);
	}	
  
  	protected function dropData() {
		$this->ShortCodes->ShortCode->deleteAll(true, false);
	}

	public function tearDown() 
	{
		$this->dropData();
		
		unset($this->ShortCodes);

		parent::tearDown();
	}
	
	public function mock_program_access()
	{
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
	 
	}
	
	
	public function testIndex()
	{
	    
	    $this->mock_program_access();
	    
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
	
	
	public function testAdd()
	{
	    $this->mock_program_access();
	    
	    $shortcodes = array(
	    	    'ShortCodes' => array(
	    	    	    'country' => 'uganda',
	    	    	    'shortcode' => 8282,
	    	    	    'internationalprefix' => 256
	    	    	)
	    	    );
	    $this->testAction("/shortCodes/add", array(
	    	    'method' => 'post',
	    	    'data' => $shortcodes
	    	    ));
	    $this->assertEquals(1, $this->ShortCodes->ShortCode->find('count'));
	}
	
	
	public function testEdit()
	{
	    $this->mock_program_access();
	    
	    $shortcodes = array(
	    	    'ShortCodes' => array(
	    	    	    'country' => 'uganda',
	    	    	    'shortcode' => 8282,
	    	    	    'internationalprefix' => 256
	    	    	)
	    	    );
	    $this->ShortCodes->ShortCode->create();
	    $data = $this->ShortCodes->ShortCode->save($shortcodes);	    
	    
	    $this->testAction("/shortCodes/edit/".$data['ShortCode']['_id'], array(
	    	    'method' => 'post',
	    	    'data' => array(
			    'ShortCodes' => array(
				    'country' => 'uganda',
				    'shortcode' => 8383,
				    'internationalprefix' => 256
				)
			    )
	    	    ));
	    //print_r($this->result);
	    $this->assertEquals(8383, $this->result['ShortCodes']['shortcode']);
	    
	}
	
	
	public function testDelete()
	{
	    $this->mock_program_access();
	    
	    $shortcodes = array(
	    	    'ShortCodes' => array(
	    	    	    'country' => 'uganda',
	    	    	    'shortcode' => 8282,
	    	    	    'internationalprefix' => 256
	    	    	)
	    	    );
	    $this->ShortCodes->ShortCode->create();
	    $data = $this->ShortCodes->ShortCode->save($shortcodes);
	    
	    $this->testAction("/shortCodes/delete/".$data['ShortCode']['_id']);
	    
	    $this->assertEquals(0, $this->ShortCodes->ShortCode->find('count'));
	}

	
}
