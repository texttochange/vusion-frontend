<?php
/* ShortCodes Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ShortCodesController', 'Controller');

/**
* TestShortCodesController *
*/
class TestShortCodesController extends ShortCodesController
{
    
    public $autoRender = false;
    
    
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
    var $databaseName = "testdbmongo";
    
    
    public function setUp() 
    {
        Configure::write("mongo_db",$this->databaseName);
        parent::setUp();
        
        $this->ShortCodes = new TestShortCodesController();
        $this->instanciateShortCodesModel();
        $this->dropData();
    }
    
    
    protected function instanciateShortCodesModel() 
    {
        $options         = array('database' => $this->databaseName);
        $this->ShortCode = new ShortCode($options);
    }	
    
    
    protected function dropData()
    {
        $this->ShortCode->deleteAll(true, false);
    }
    
    
    public function tearDown() 
    {
        $this->dropData();
        
        unset($this->ShortCodes);
        
        parent::tearDown();
    }
    
	
    public function mockProgramAccess()
    {
        $shortCodes = $this->generate('ShortCodes', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read'),
                'Auth' => array(),
                ),
            'models' => array(
                'Group' => array()
                )
            ));
        
        $shortCodes->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $shortCodes->Session
        ->expects($this->any())
        ->method('read')
        ->will($this->onConsecutiveCalls('1','1','1'));	   
        
        return $shortCodes;
    }
    
    
    public function testIndex()
    {
        
        $this->mockProgramAccess();
        
        $this->instanciateShortCodesModel();
        $this->ShortCode->create();
        $this->ShortCode->save(array(
            'country' => 'uganda',
            'shortcode' => 8282,
            'international-prefix' => 256
            ));
        
        $this->testAction("/shortCodes/index");
        
        $this->assertEquals(1, count($this->vars['shortcodes']));		
    }
    
    
    public function testAdd()
    {
        $this->mockProgramAccess();
        
        $shortcode = array(
            'ShortCode' => array(
                'country' => 'uganda',
                'shortcode' => 8282,
                'international-prefix' => 256
                )
            );
        $this->testAction("/shortCodes/add", array(
            'method' => 'post',
            'data' => $shortcode
            ));
        $this->assertEquals(1, $this->ShortCode->find('count'));
    }
    
    
    public function testEdit()
    {
        
        $shortCodes = $this->mockProgramAccess();
        
        $shortcodes = array(
            'country' => 'uganda',
            'shortcode' => 8282,
            'international-prefix' => 256
            );
        $this->ShortCode->create();
        $data = $this->ShortCode->save($shortcodes);	    
        
        $this->testAction("/shortCodes/edit/".$data['ShortCode']['_id'], array(
            'method' => 'post',
            'data' => array(
                'ShortCode' => array(
                    'country' => 'uganda',
                    'shortcode' => 8383,
                    'international-prefix' => 256
                    )
                )
            ));
        $this->assertEquals(8383, $shortCodes->data['ShortCode']['shortcode']);
    }
    
    
    public function testDelete()
    {
        $this->mockProgramAccess();
        
        $shortcodes = array(
            'ShortCode' => array(
                'country' => 'uganda',
                'shortcode' => 8282,
                'international-prefix' => 256
                )
            );
        $this->ShortCode->create();
        $data = $this->ShortCode->save($shortcodes);
        
        $this->testAction("/shortCodes/delete/".$data['ShortCode']['_id']);
        
        $this->assertEquals(0, $this->ShortCode->find('count'));
    }
    
    
}
