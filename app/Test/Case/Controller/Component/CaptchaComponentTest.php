<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('CaptchaComponent', 'Controller/Component');

class TestCaptchaComponentController extends Controller
{
    var $components = array('Captcha');
    
}


class CaptchaComponentTest extends CakeTestCase
{
    public $CaptchaComponent = null;
    public $Controller = null;
    
    public function setup()
    {
        parent::setUp();
        $Collection = new ComponentCollection();
        $this->CaptchaComponent = new CaptchaComponent($Collection);
        $CakeRequest = new CakeRequest();
        $CakeResponse = new CakeResponse();
        
        $this->Controller = new TestCaptchaComponentController($CakeRequest, $CakeResponse);
        $this->Controller->constructClasses();
        $this->CaptchaComponent->initialize($this->Controller);
        
    }
    
    
    public function tearDown()
    {       
        unset($this->CaptchaComponent);
        parent::tearDown();
    }
    
    /*
    public function testSecurity_Code_InSession()
    {
        $captchaTest = $this->getMock('Session',
            array('read'));
        
        $captchaTest
            ->expects($this->once())
            ->method('read')
            ->with('security_code')
            ->will($this->returnValue('fgd256'));
            
        $this->CaptchaComponent->Controller->Session = $captchaTest;
        $code = $this->CaptchaComponent->getVerCode();
        
        $this->assertEqual('fgd256', $code);
    }
    
   
    public function testGeneratedCode_Length()
    {
        $characters = 6;
        $testCode = $this->CaptchaComponent->generateCode($characters);
        $this->assertEquals(6, strlen($testCode));
        
        $characters = 9;
        $testCode = $this->CaptchaComponent->generateCode($characters);
        $this->assertEquals(9, strlen($testCode));
    }
    */
    
    public function testSecurity_Code_Write_InSession()
    {
        $captchaTest = $this->getMock('Session',
            array('write'));
        
        $captchaTest
            ->expects($this->once())
            ->method('write')
            ;
        $this->CaptchaComponent->Controller->Session = $captchaTest;
        $this->CaptchaComponent->create();
    }    
    
      
}
