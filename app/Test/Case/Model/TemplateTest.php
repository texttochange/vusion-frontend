<?php
App::uses('Template', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class TemplateTestCase extends CakeTestCase
{
    
    
    public function setUp()
    {
        parent::setUp();
        /*
        $option         = array('database'=>'test');
        $this->Template = new Template($option);
        
        $this->Template->setDataSource('mongo_test');
        $this->Template->deleteAll(true, false);*/
        $dbName = 'testdbprogram';
        $this->Template = ClassRegistry::init(
            'Template', $dbName);
    }
    
    
    public function tearDown()
    {
        $this->Template->deleteAll(true, false);
        unset($this->Template);
        parent::tearDown();
    }
    
    public function testSave()
    {
        $openTemplate = array(
            'name'=>'my open template',
            'type-template'=>'open-question',
            'template' => 'QUESTION, SHORTCODE, ANSWER, KEYWORD'
            );
        
        $otherOpenTemplate = array(
            'name'=>'my Open template',
            'type-template'=>'open-question',
            'template' => 'QUESTION, SHORTCODE, ANSWER, KEYWORD but different'
            );
        
        $this->Template->create();
        $saveTemplate = $this->Template->save($openTemplate);
        $this->assertEqual('my open template', $saveTemplate['Template']['name']);
        
        $this->Template->create();
        $this->assertFalse($this->Template->save($otherOpenTemplate));        
        
    }
    
    
    public function testSave_trimed()
    {
        $template = array(
            'name'=>'my open template',
            'type-template'=>'open-question',
            'template' => ' \tQUESTION, SHORTCODE, ANSWER, KEYWORD\t \t \n'
            );
        
        $this->Template->create();
        $saveTemplate = $this->Template->save($template);
        $this->assertEqual('QUESTION, SHORTCODE, ANSWER, KEYWORD', $saveTemplate['Template']['template']);
        
    }   
    
    
    public function testGetTemplateOptions()
    {
        
        $openTemplate = array(
            'name'=>'my open template',
            'type-template'=>'open-question',
            'template' => 'QUESTION, SHORTCODE, ANSWER, KEYWORD'
            );
        
        $closedTemplate = array(
            'name'=>'my closed template',
            'type-template'=>'closed-question',
            'template' => 'QUESTION, SHORTCODE, ANSWERS, KEYWORD'
            );
        
        $this->Template->create();
        $this->Template->save($openTemplate);
        
        $this->Template->create();
        $this->Template->save($closedTemplate);
        
        $options = $this->Template->getTemplateOptions('open-question');
        
        $this->assertEqual(1, count($options));
        foreach ($options as $option) {
            $this->assertEqual('my open template', $option);
        }
    }
    
    
}
