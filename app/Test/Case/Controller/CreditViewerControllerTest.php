<?php

App::uses('CreditViewerController', 'Controller');
App::uses('History', 'Model');


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
        Configure::write("mongo_db", "testdbmongo");
        parent::setUp();
        
        $this->Viewer = new TestCreditViewerController();
        $this->Viewer->constructClasses();
        
        $this->ShortCode = new ShortCode(array('database' => "testdbmongo"));
        $this->dropData();
    }
    
    
    protected function dropData()
    {
        $this->ShortCode->deleteAll(true, false);
        
        $this->ProgramSettingTest = new ProgramSetting(array('database' => 'testdbprogram'));
        $this->ProgramSettingTest->deleteAll(true, false);  
        $this->ProgramSettingM6H = new ProgramSetting(array('database' => 'm6h'));
        $this->ProgramSettingM6H->deleteAll(true, false);
        $this->ProgramSettingTrial = new ProgramSetting(array('database' => 'trial'));
        $this->ProgramSettingTrial->deleteAll(true, false);
        
        $this->HistoryTest = new History(array('database' => 'testdbprogram'));
        $this->HistoryTest->deleteAll(true, false);  
        $this->HistoryM6H = new History(array('database' => 'm6h'));
        $this->HistoryM6H->deleteAll(true, false);
        $this->HistoryTrial = new History(array('database' => 'trial'));
        $this->HistoryTrial->deleteAll(true, false);
    }
    
    
    public function tearDown()
    {
        unset($this->Viewer);
        
        parent::tearDown();
    }
    
    
    protected function _saveShortcodesInMongoDatabase()
    {
        $shortcode1 = array(
            'country' => 'uganda',
            'shortcode' => '8282',
            'international-prefix' => '256'
            );        
        $this->ShortCode->create();
        $this->ShortCode->save($shortcode1);
        
        $shortcode2 = array(
            'country' => 'uganda',
            'shortcode' => '8181',
            'international-prefix' => '256'
            );
        $this->ShortCode->create();
        $this->ShortCode->save($shortcode2);
        
        $shortcode3 = array(
            'country' => 'kenya',
            'shortcode' => '21222',
            'international-prefix' => '254'
            );
        $this->ShortCode->create();
        $this->ShortCode->save($shortcode3);
    }
    
    
    public function testIndex()
    {
        $this->testAction("/creditViewer/index");
        $this->assertEquals(3, count($this->vars['programs']));
        $this->assertTrue(array_key_exists('total-credits', $this->vars['programs'][0]['Program']));
        $this->assertTrue(array_key_exists('total-credits', $this->vars['programs'][1]['Program']));
        $this->assertTrue(array_key_exists('total-credits', $this->vars['programs'][2]['Program']));        
    }

    
    public function testIndex_filters()
    {
        $this->_saveShortcodesInMongoDatabase();
        
        $this->ProgramSettingTest = new ProgramSetting(array('database' => 'testdbprogram'));
        $this->ProgramSettingTest->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSettingTest->saveProgramSetting('shortcode','256-8282');
        
        $this->ProgramSettingM6H = new ProgramSetting(array('database' => 'm6h'));
        $this->ProgramSettingM6H->saveProgramSetting('timezone','Africa/Daresalaam');
        $this->ProgramSettingM6H->saveProgramSetting('shortcode','256-8181');
        
        $this->ProgramSettingTrial = new ProgramSetting(array('database' => 'trial'));
        $this->ProgramSettingTrial->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSettingTrial->saveProgramSetting('shortcode','254-21222');
        
        $this->HistoryTest  = new History(array('database' => 'testdbprogram'));
        $this->HistoryTest->create('unattach-history');
        $this->HistoryTest->save(array(
            'participant-phone' => '256712747841',
            'message-content' => 'Hello everyone!',
            'timestamp' => '2012-02-08T12:20:43.882854',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered',
            'message-credits' => '1'
            ));
        
        $this->HistoryM6H   = new History(array('database' => 'm6h'));
        $this->HistoryM6H->create('unattach-history');
        $this->HistoryM6H->save(array(
            'participant-phone' => '256712747842',
            'message-content' => 'Hello everyone! Merry xmas!!!',
            'timestamp' => '2012-02-08T12:30:43.882854',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered',
            'message-credits' => '1'
            ));
        
        $this->HistoryTrial = new History(array('database' => 'trial'));
        $this->HistoryTrial->create('unattach-history');
        $this->HistoryTrial->save(array(
            'participant-phone' => '256712747843',
            'message-content' => 'Hello everyone! happy new year!!!',
            'timestamp' => '2012-02-08T12:40:43.882854',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered',
            'message-credits' => '1'
            ));
        
        // filter by program name only
        $this->testAction('/creditViewer/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t');
        $this->assertEquals(2, count($this->vars['programs']));
        $this->assertEquals(1, count($this->vars['programs'][0]['Program']['total-credits']));
        $this->assertEquals(1, count($this->vars['programs'][1]['Program']['total-credits']));
        
        // filter by shortcode only (8282)
        $this->testAction('/creditViewer/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=shortcode&filter_param%5B1%5D%5B2%5D=is&filter_param%5B1%5D%5B3%5D=8282');
        $this->assertEquals(1, count($this->vars['programs']));
        $this->assertEquals(1, count($this->vars['programs'][0]['Program']['total-credits']));
        
        // filter by country only (Uganda)
        $this->testAction('/creditViewer/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=country&filter_param%5B1%5D%5B2%5D=is&filter_param%5B1%5D%5B3%5D=uganda');
        $this->assertEquals(2, count($this->vars['programs']));
        $this->assertEquals(1, count($this->vars['programs'][0]['Program']['total-credits']));
        $this->assertEquals(1, count($this->vars['programs'][1]['Program']['total-credits']));
        
        // filter by program name AND shortcode (t, 8282)
        $this->testAction('/creditViewer/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t&filter_param%5B2%5D%5B1%5D=shortcode&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=8282');
        $this->assertEquals(1, count($this->vars['programs']));
        $this->assertEquals(1, count($this->vars['programs'][0]['Program']['total-credits']));
        
        // filter by program name AND country (m6h, kenya) //uganda
        $this->testAction('/creditViewer/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=equal-to&filter_param%5B1%5D%5B3%5D=m6h&filter_param%5B2%5D%5B1%5D=country&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=uganda');
        $this->assertEquals(1, count($this->vars['programs']));
        $this->assertEquals(1, count($this->vars['programs'][0]['Program']['total-credits']));
        
        // filter by program name AND country AND shortcode (t, uganda, 8282)
        $this->testAction('/creditViewer/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t&filter_param%5B2%5D%5B1%5D=country&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=uganda&filter_param%5B3%5D%5B1%5D=shortcode&filter_param%5B3%5D%5B2%5D=is&filter_param%5B3%5D%5B3%5D=8282');
        $this->assertEquals(1, count($this->vars['programs']));
        $this->assertEquals(1, count($this->vars['programs'][0]['Program']['total-credits']));
        
        // filter by program name OR shortcode (t, 21222)
        $this->testAction('/creditViewer/index?filter_operator=any&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t&filter_param%5B2%5D%5B1%5D=shortcode&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=21222');
        $this->assertEquals(2, count($this->vars['programs']));
        $this->assertEquals(1, count($this->vars['programs'][0]['Program']['total-credits']));
        $this->assertEquals(1, count($this->vars['programs'][1]['Program']['total-credits']));
        
        // filter by program name OR country (trial, kenya)
        $this->testAction('/creditViewer/index?filter_operator=any&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=equal-to&filter_param%5B1%5D%5B3%5D=m6h&filter_param%5B2%5D%5B1%5D=country&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=kenya');
        $this->assertEquals(2, count($this->vars['programs']));
        $this->assertEquals(1, count($this->vars['programs'][0]['Program']['total-credits']));
        $this->assertEquals(1, count($this->vars['programs'][1]['Program']['total-credits']));
        
        // filter by program name OR country OR shortcode (t, uganda, 21222)        
        $this->testAction('/creditViewer/index?filter_operator=any&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t&filter_param%5B2%5D%5B1%5D=country&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=Uganda&filter_param%5B3%5D%5B1%5D=shortcode&filter_param%5B3%5D%5B2%5D=is&filter_param%5B3%5D%5B3%5D=21222');
        $this->assertEquals(3, count($this->vars['programs']));
        $this->assertEquals(1, count($this->vars['programs'][0]['Program']['total-credits']));
        $this->assertEquals(1, count($this->vars['programs'][1]['Program']['total-credits']));
        $this->assertEquals(1, count($this->vars['programs'][2]['Program']['total-credits']));
    }    
    
}

