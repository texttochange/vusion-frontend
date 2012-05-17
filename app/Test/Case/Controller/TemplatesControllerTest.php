<?php

App::uses('TemplatesController', 'Controller');

class TestTemplatesController extends TemplatesController
{
    public $autoRender = false;
    
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
    
    
}

/**
* TemplatesController Test Case
*
*/
Class TemplatesControllerTestCase extends ControllerTestCase
{
    var $databaseName = "testdbmongo";
    
    public function setUp()
    {
        Configure::write("mongo_db",$this->databaseName);
        parent::setUp();
        
        $this->Templates = new TestTemplatesController();
        $this->instanciateTemplateModel();
        $this->dropData();
    }
    
    
    protected function instanciateTemplateModel()
    {
        $options = array('database'=>$this->databaseName);
        $this->Templates->Template = new Template($options);
    }
    
    
    protected function dropData()
    {
        $this->Templates->Template->deleteAll(true,false);
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        
        unset($this->Templates);
        
        parent::tearDown();
    }
    
    
    public function mockProgramAccess()
    {
        $templates = $this->generate('Templates', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
            ),
            'models' => array(
                'Group' => array()
                )
            ));
        
        $templates->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
            
        $templates->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls('1','1','1'));
            
        return $templates;
    }
    
    
/**
* Test Methods
*
*/
    public function testIndex()
    {
        $templates = $this->mockProgramAccess();
        $this->instanciateTemplateModel();
        $this->Templates->Template->create();
        $this->Templates->Template->save(array(
            'name' => 'example',
            'template' => 'type KEYWORD'
            ));
        
        $this->testAction("/templates/index");

        $this->assertEquals(1, count($this->vars['templates']));
    }
    
    
    public function testAdd()
    {
        $templates = $this->mockProgramAccess();
        $templates = array(
            'Templates'=>array(
                'name'=>'example',
                'template'=>'type KEYWORD'
                )
            );
        $this->testAction("/templates/add", array(
            'method' => 'post',
            'data' => $templates
        ));
        $this->assertEquals(1, $this->Templates->Template->find('count'));
    }
    
    
    public function testEdit()
    {
        $templates = $this->mockProgramAccess();
        $newtemplates = array(
            'Templates'=>array(
                'name'=>'example',
                'template'=>'type KEYWORD'
                )
            );
        $this->Templates->Template->create();
        $data = $this->Templates->Template->save($newtemplates);
        
        $this->testAction("templates/edit/".$data['Template']['_id'], array(
            'method'=>'post',
            'data'=>array(
                'Templates'=>array(
                    'name'=>'example one',
                    'template'=>'type KEYWORD here'
                    )
                )
            ));
        $this->assertEquals('example one', $templates->data['Templates']['name']);
    }
    
    
    public function testDelete()
    {
        $templates = $this->mockProgramAccess();
        $templates = array(
            'Templates'=>array(
                'name'=>'example',
                'template'=>'type KEYWORD'
                )
            );
        $this->Templates->Template->create();
        $data = $this->Templates->Template->save($templates);
        
        $this->testAction("templates/delete/".$data['Template']['_id']);
        $this->assertEquals(0, $this->Templates->Template->find('count'));
    }
    
    
}
