<?php

App::uses('CreditViewerController', 'Controller');
//App::uses('History', 'Model');
//App::uses('ProgramSetting', 'Model');


class TestCreditViewerController extends CreditViewerController
{
    
    public $autoRender = false;
    
    
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
    
    
}


class CreditViewerControllerTestCase extends ControllerTestCase
{
    public $fixtures = array('app.program', 'app.user', 'app.programsUser');
      
    
    public function setUp()
    {
        parent::setUp();
        
        $this->Viewer = new TestCreditViewerController();
        
        $this->dropData();
    }
    
    
    /*protected function instanciateModels() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);    
        $this->ProgramSetting    = new ProgramSetting($options);
        $this->History           = new History($options);
    }*/
    
    // we only mock the data to be used so we dont need a dropData() function;    
    
    public function tearDown()
    {
        unset($this->Viewer);
        
        parent::tearDown();
    }
    
    
    public function mock_program_access()
    {
        $viewers = $this->generate(
            'CreditViewer', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read'),
                    'CreditManager'
                    ),
                )
            );
        
        $viewers->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
            
        /*$viewers->Program
        ->expects($this->any())
        ->method('find')
        ->will($this->returnValue($this->programData));*/
                
        return $viewers;
    }
    
    
    public function testIndex()
    {
        //$this->mock_program_access();        
        $this->testAction("/creditViewer/index");
        $this->assertEquals(3, count($this->vars['programs']));        
    }

    /*
    public function testFilter()
    {
        $this->mock_program_access();
        
        $this->testAction("/creditViewer/index");        
    }*/    
    
}

