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

/* overwrite the outputImage function to avoid poluting unittest output*/
class CaptchaConponentWithoutOutput extends CaptchaComponent 
{
    protected function _outputImage($image)
    {
    }
}


class CaptchaComponentTest extends CakeTestCase
{
    public $CaptchaComponent = null;
    public $Controller = null;
    
    public function setup()
    {
        parent::setUp();
        $Collection = new ComponentCollection();
        $this->CaptchaComponent = new CaptchaConponentWithoutOutput($Collection);
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
    
    
    public function testGetCodeVariable_ok_securityCodeReadInSession()
    {
        $captchaTest = $this->getMock('Session',
            array('read'));
        
        $captchaTest
            ->expects($this->once())
            ->method('read')
            ->with('captchaCode')
            ->will($this->returnValue('fgd256'));
            
        $this->CaptchaComponent->Controller->Session = $captchaTest;
        $captchaCode = $this->CaptchaComponent->getCaptchaCode();
        
        $this->assertEqual('fgd256', $captchaCode);
    }
    
   
    public function testGeneratedCode_ok_codeLength()
    {
        $characters = 6;
        $testCode = $this->CaptchaComponent->generateCaptchaCode($characters);
        $this->assertEquals(6, strlen($testCode));
        
        $characters = 9;
        $testCode = $this->CaptchaComponent->generateCaptchaCode($characters);
        $this->assertEquals(9, strlen($testCode));
    }
    
    
    public function testCreate_ok_securityCodeWriteInSession()
    {
        $captchaTest = $this->getMock('Session',
            array('write'));
        
        $captchaTest
            ->expects($this->once())
            ->method('write')
            ->with('captchaCode');

        $this->CaptchaComponent->Controller->Session = $captchaTest;
        $captchaConfig = array(
            'settings' => array(
                'font'            => 'BIRTH_OF_A_HERO.ttf', 
                'width'           => 120,
                'height'          => 40,
                'characters'      => 6,
                'theme'           => 'default',
                'font_adjustment' => 0.70
                ),
            'themes'  => array(
                'default' => array(
                    'bgcolor'    => array(200,200, 200),
                    'txtcolor'   => array(10, 30, 80),
                    'noisecolor' => array(60, 90, 120)
                    )
                )
            );
        Configure::write('vusion.captcha', $captchaConfig);

        $this->CaptchaComponent->create();
    }    
    
      
}
