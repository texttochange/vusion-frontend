<?php
App::uses('Request', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class RequestTestCase extends CakeTestCase
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

        $option        = array('database'=>'test');
        $this->Request = new Request($option);

        $this->Request->setDataSource('mongo_test');
        $this->Request->deleteAll(true, false);
    }


    public function tearDown()
    {
        $this->Request->deleteAll(true, false);
        unset($this->Request);
        parent::tearDown();
    }

    
    public function testFindKeyword()
    {
        $request['Request'] = array(
            'keyword' => 'key request, keyword, otherkeyword request'
            );
        $this->Request->create();
        $this->Request->save($request);
        $matchingKeywordRequest = $this->Request->find('keyword', array('keywords'=>'keyword'));
        $this->assertEqual(1, count($matchingKeywordRequest));

        $matchingKeywordRequest = $this->Request->find('keyword', array('keywords'=>'keywo'));
        $this->assertEqual(0, count($matchingKeywordRequest));

        $matchingKeywordRequest = $this->Request->find('keyword', array('keywords'=>'keywor, keyword'));
        $this->assertEqual(1, count($matchingKeywordRequest));
        
        $matchingKeywordRequest = $this->Request->find('keyword', array('keywords'=>'kEy'));
        $this->assertEqual(1, count($matchingKeywordRequest));
        
        $matchingKeywordRequest = $this->Request->find('keyword', array('keywords'=>'request'));
        $this->assertEqual(0, count($matchingKeywordRequest));

        $request['Request'] = array(
            'keyword' => 'key'
            );
        $this->Request->create();
        $this->Request->save($request);
        $matchingKeywordRequest = $this->Request->find('keyword', array('keywords'=>'key'));
        $this->assertEqual(1, count($matchingKeywordRequest));
        
    }

}
