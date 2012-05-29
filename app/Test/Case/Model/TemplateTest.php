<?php
App::uses('Template', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class TemplateTestCase extends CakeTestCase
{

      protected $_config = array(
        'datasource' => 'Mongodb.MongodbSource',
        'host' => 'localhost',
        'login' => '',
        'password' => '',
        'database' => 'test',
        'port' => 27017,
        'prefix' => '',
        'persistent' => true,
        );

    
    public function setUp()
    {
        parent::setUp();

        $connections = ConnectionManager::enumConnectionObjects();
        
        if (!empty($connections['test']['classname']) && $connections['test']['classname'] === 'mongodbSource'){
            $config = new DATABASE_CONFIG();
            $this->_config = $config->test;
        }
        
        ConnectionManager::create('mongo_test', $this->_config);
        $this->Mongo = new MongodbSource($this->_config);

        $option         = array('database'=>'test');
        $this->Template = new Template($option);

        $this->Template->setDataSource('mongo_test');
        $this->Template->deleteAll(true, false);
    }


    public function tearDown()
    {
        $this->Template->deleteAll(true, false);
        unset($this->Template);
        parent::tearDown();
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
