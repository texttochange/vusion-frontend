<?php
App::uses('Controller', 'Controller');
App::uses('ExportComponent', 'Controller/Component');


class TestExportComponentController extends Controller
{
    var $components = array('Export');
    var $redisExportPrefix = 'unittest';

    public function constructClasses()
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1');
    }
}


class ExportComponentTest extends CakeTestCase
{


    public function setup()
    {
        parent::setUp();
        $Collection = new ComponentCollection();
        $this->ExportComponent = new ExportComponent($Collection);
        $CakeRequest = new CakeRequest();
        $CakeResponse = new CakeResponse();
        
        $this->Controller = new TestExportComponentController($CakeRequest, $CakeResponse);
        $this->Controller->constructClasses();
        $this->ExportComponent->initialize($this->Controller);
        $this->ExportComponent->startup($this->Controller);

        $this->redis = $this->Controller->redis;
    }


    public function tearDown()
    {
        $keys = $this->redis->keys('unittest:*');
        foreach ($keys as $key){
            $this->redis->delete($key);
        }
        unset($this->ExportComponent);
        parent::tearDown();
    }


    public function testCounter()
    {
        $this->assertFalse(
            $this->ExportComponent->hasExports("myprogramUrl", "participants"));
        $this->ExportComponent->startAnExport("myprogramUrl", "participants");
        $this->assertTrue(
            $this->ExportComponent->hasExports("myprogramUrl", "participants"));
        $this->assertFalse(
            $this->ExportComponent->hasExports("myprogramUrl", "history"));
    }

}
