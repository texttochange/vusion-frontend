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


Class TemplatesControllerTestCase extends ControllerTestCase
{
    var $databaseName = "testdbmongo";
    
    public function setUp()
    {
        parent::setUp();
        
        $this->Templates = new TestTemplatesController();
        $this->Template = ClassRegistry::init('Template');
        $this->dropData();
    }

    
    protected function dropData()
    {
        $this->Template->deleteAll(true,false);
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
                'Session' => array('read'),
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
        $this->Template->create();
        $this->Template->save(array(
            'name' => 'example',
            'type-template' => 'open question',
            'template' => 'QUESTION, SHORTCODE, ANSWER, KEYWORD'
            ));
        
        $this->testAction("/templates/index");
        
        $this->assertEquals(1, count($this->vars['templates']));
    }
    
    
    public function testAdd()
    {
        $templates = $this->mockProgramAccess();
        $templates = array(
            'Template'=>array(
                'name'=>'example',
                'type-template' => 'open question',
                'template' => 'QUESTION, SHORTCODE, ANSWER, KEYWORD'
                )
            );
        $this->testAction("/templates/add", array(
            'method' => 'post',
            'data' => $templates
            ));
        $this->assertEquals(1, $this->Template->find('count'));
    }
    
    
    public function testEdit()
    {
        $templates = $this->mockProgramAccess();
        $newtemplates = array(
            'Template'=>array(
                'name'=>'example',
                'type-template' => 'open question',
                'template' => 'QUESTION, SHORTCODE, ANSWER, KEYWORD'
                )
            );
        $this->Template->create();
        $data = $this->Template->save($newtemplates);
        
        $this->testAction("templates/edit/".$data['Template']['_id'], array(
            'method'=>'post',
            'data'=>array(
                'Template'=>array(
                    'name'=>'example one',            
                    'type-template' => 'open question',
                    'template' => 'QUESTION, SHORTCODE, ANSWER, KEYWORD'
                    )
                )
            ));
        $this->assertEquals('example one', $templates->data['Template']['name']);
    }
    
    
    public function testDelete()
    {
        $templates = $this->mockProgramAccess();
        $templates = array(
            'Template'=>array(
                'name'=>'example',
                'type-template' => 'open question',
                'template' => 'QUESTION, SHORTCODE, ANSWER, KEYWORD'
                )
            );
        $this->Template->create();
        $data = $this->Template->save($templates);
        
        $this->testAction("templates/delete/".$data['Template']['_id']);
        $this->assertEquals(0, $this->Templates->Template->find('count'));
    }
    
    
}
