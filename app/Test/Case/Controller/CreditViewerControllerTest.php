<?php

App::uses('CreditViewerController', 'Controller');
App::uses('History', 'Model');
App::uses('ScriptMaker', 'Lib');


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
    public $fixtures = array('app.program', 'app.user', 'app.programsUser', 'app.group');
      
    
    public function setUp()
    {
        Configure::write("mongo_db", "testdbmongo");
        parent::setUp();
        
        $this->Viewer = new TestCreditViewerController();
        $this->Viewer->constructClasses();
        
        $this->ShortCode = new ShortCode(array('database' => "testdbmongo"));
        $this->CreditLog = new CreditLog(array('database' => "testdbmongo"));
        $this->ProgramSettingTest = new ProgramSetting(array('database' => 'testdbprogram'));
        $this->ProgramSettingM6H = new ProgramSetting(array('database' => 'm6h'));
        $this->ProgramSettingTrial = new ProgramSetting(array('database' => 'trial'));

        $this->dropData();
    }
    
    
    protected function dropData()
    {
        $this->ShortCode->deleteAll(true, false);
        $this->CreditLog->deleteAll(true, false);        
        
        $this->ProgramSettingTest->deleteAll(true, false);  
        $this->ProgramSettingM6H->deleteAll(true, false);
        $this->ProgramSettingTrial->deleteAll(true, false);
    
        /*
        $this->HistoryTest = new History(array('database' => 'testdbprogram'));
        $this->HistoryTest->deleteAll(true, false);  
        $this->HistoryM6H = new History(array('database' => 'm6h'));
        $this->HistoryM6H->deleteAll(true, false);
        $this->HistoryTrial = new History(array('database' => 'trial'));
        $this->HistoryTrial->deleteAll(true, false);
        */
    }
    
    
    public function tearDown()
    {
        $this->dropData();
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
    
/*   
    public function testIndex()
    {
        $this->testAction("/creditViewer/index");
        $this->assertEquals(3, count($this->vars['programs']));
        #$this->assertTrue(array_key_exists('total-credits', $this->vars['programs'][0]['Program']));
        #$this->assertTrue(array_key_exists('total-credits', $this->vars['programs'][1]['Program']));
        #$this->assertTrue(array_key_exists('total-credits', $this->vars['programs'][2]['Program']));        
    }
    
    public function testIndex_filtered()
    {
        $this->_saveShortcodesInMongoDatabase();
        
        $this->ProgramSettingTest = new ProgramSetting(array('database' => 'testdbprogram'));
        $this->ProgramSettingTest->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSettingTest->saveProgramSetting('shortcode','256-8282');

        #One recent creditlog
        $creditLog = ScriptMaker::mkCreditLog(
            'program-credit-log', '2014-04-10', 'testdbprogram', '256-8282');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog);

        #One old creditLog
        $creditLog = ScriptMaker::mkCreditLog(
            'program-credit-log', '2013-04-10', 'testdbprogram', '256-8282');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog);

        #One garbage credit log 
        $creditLog = ScriptMaker::mkCreditLog(
            'garbage-credit-log', '2014-04-10', 'testdbprogram', '256-8282');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog);
       
        // filter by program name onl
        $this->testAction('/creditViewer/index?timeframe-type=date-to-now&date-from=01%2F03%2F2014&date-from=&date-to=&predefined-timeframe=');
        $this->assertEquals(2, count($this->vars['programs']));
        $this->assertEqual('test', $this->vars['programs'][0]['Program']['name']);
        $this->assertEqual('trial', $this->vars['programs'][1]['Program']['name']);
        
        
        // filter by shortcode only (8282)
        $this->testAction('/creditViewer/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=shortcode&filter_param%5B1%5D%5B2%5D=is&filter_param%5B1%5D%5B3%5D=8282');
        $this->assertEquals(1, count($this->vars['programs']));
        $this->assertEqual('8282', $this->vars['programs'][0]['Program']['shortcode']);
        
        // filter by program name AND shortcode (t, 8282)
        $this->testAction('/creditViewer/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t&filter_param%5B2%5D%5B1%5D=shortcode&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=8282');
        $this->assertEquals(1, count($this->vars['programs']));
        $this->assertEqual('8282', $this->vars['programs'][0]['Program']['shortcode']);
        $this->assertEqual('test', $this->vars['programs'][0]['Program']['name']);
        
        // filter by program name AND country (m6h, uganda) //uganda
        $this->testAction('/creditViewer/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=equal-to&filter_param%5B1%5D%5B3%5D=m6h&filter_param%5B2%5D%5B1%5D=country&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=uganda');
        $this->assertEquals(1, count($this->vars['programs']));
        $this->assertEquals('uganda', $this->vars['programs'][0]['Program']['country']);
        $this->assertEquals('m6h', $this->vars['programs'][0]['Program']['name']);
        
        // filter by program name AND country AND shortcode (t, uganda, 8282)
        $this->testAction('/creditViewer/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t&filter_param%5B2%5D%5B1%5D=country&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=uganda&filter_param%5B3%5D%5B1%5D=shortcode&filter_param%5B3%5D%5B2%5D=is&filter_param%5B3%5D%5B3%5D=8282');
        $this->assertEquals(1, count($this->vars['programs']));
        $this->assertEquals('test', $this->vars['programs'][0]['Program']['name']);
        
        // filter by program name OR shortcode (t, 21222)
        $this->testAction('/creditViewer/index?filter_operator=any&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t&filter_param%5B2%5D%5B1%5D=shortcode&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=21222');
        $this->assertEquals(2, count($this->vars['programs']));
        $this->assertEquals('test', $this->vars['programs'][0]['Program']['name']);
        $this->assertEquals('trial', $this->vars['programs'][1]['Program']['name']);
        
        // filter by program name OR country (trial, kenya)
        $this->testAction('/creditViewer/index?filter_operator=any&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=equal-to&filter_param%5B1%5D%5B3%5D=m6h&filter_param%5B2%5D%5B1%5D=country&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=kenya');
        $this->assertEquals(2, count($this->vars['programs']));
        $this->assertEquals('trial', $this->vars['programs'][0]['Program']['name']);
        $this->assertEquals('m6h', $this->vars['programs'][1]['Program']['name']);
        
        // filter by program name OR country OR shortcode (t, uganda, 21222)        
        $this->testAction('/creditViewer/index?filter_operator=any&filter_param%5B1%5D%5B1%5D=name&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=t&filter_param%5B2%5D%5B1%5D=country&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=Uganda&filter_param%5B3%5D%5B1%5D=shortcode&filter_param%5B3%5D%5B2%5D=is&filter_param%5B3%5D%5B3%5D=21222');
        $this->assertEquals(3, count($this->vars['programs']));
        $this->assertEquals('test', $this->vars['programs'][0]['Program']['name']);
        $this->assertEquals('trial', $this->vars['programs'][1]['Program']['name']);
        $this->assertEquals('m6h', $this->vars['programs'][2]['Program']['name']);
        
    }    
*/

    public function testIndex_not_timeframed()
    {
        $this->_saveShortcodesInMongoDatabase();
        
        $this->ProgramSettingTest = new ProgramSetting(array('database' => 'testdbprogram'));
        $this->ProgramSettingTest->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSettingTest->saveProgramSetting('shortcode','256-8282');

        #One recent creditlog
        $creditLog = ScriptMaker::mkCreditLog(
            'program-credit-log', date('Y-m-1'), 'testdbprogram', '256-8282');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog);

        #One old creditLog
        $creditLog = ScriptMaker::mkCreditLog(
            'program-credit-log', '2012-04-10', 'testdbprogram', '256-8282');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog);

        $this->ProgramSettingM6H = new ProgramSetting(array('database' => 'm6h'));
        $this->ProgramSettingM6H->saveProgramSetting('timezone','Africa/Daresalaam');
        $this->ProgramSettingM6H->saveProgramSetting('shortcode','256-8181');
       
        //not time frame so only show current month
        $this->testAction('/creditViewer/index');

        $this->assertTrue(isset($this->vars['countriesCredits']));
        $countriesCredits = $this->vars['countriesCredits'];
        $this->assertEquals(1, count($countriesCredits));
        $this->assertEqual('Uganda', $countriesCredits[0]['country']);
        $this->assertEqual('test', $countriesCredits[0]['codes'][0]['programs'][0]['name']);
        $this->assertEqual(1, $countriesCredits[0]['codes'][0]['programs'][0]['outgoing']);
    }

    public function testIndex_timeframed()
    {
        $this->_saveShortcodesInMongoDatabase();
        
        $this->ProgramSettingTest = new ProgramSetting(array('database' => 'testdbprogram'));
        $this->ProgramSettingTest->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSettingTest->saveProgramSetting('shortcode','256-8282');

        #One recent creditlog
        $creditLog = ScriptMaker::mkCreditLog(
            'program-credit-log', '2014-04-10', 'testdbprogram', '256-8282');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog);

        #One old creditLog
        $creditLog = ScriptMaker::mkCreditLog(
            'program-credit-log', '2013-04-10', 'testdbprogram', '256-8282');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog);

        $this->ProgramSettingM6H = new ProgramSetting(array('database' => 'm6h'));
        $this->ProgramSettingM6H->saveProgramSetting('timezone','Africa/Daresalaam');
        $this->ProgramSettingM6H->saveProgramSetting('shortcode','256-8181');
       
        $this->testAction('/creditViewer/index?date-from=01%2F03%2F2014');

        $this->assertTrue(isset($this->vars['countriesCredits']));
        $countriesCredits = $this->vars['countriesCredits'];
        $this->assertEquals(1, count($countriesCredits));
        $this->assertEqual('Uganda', $countriesCredits[0]['country']);
        $this->assertEqual('test', $countriesCredits[0]['codes'][0]['programs'][0]['name']);
        $this->assertEqual(1, $countriesCredits[0]['codes'][0]['programs'][0]['outgoing']);

        $this->testAction('/creditViewer/index?date-from=01%2F03%2F2013&date-to=01%2F05%2F2013');

        $this->assertTrue(isset($this->vars['countriesCredits']));
        $countriesCredits = $this->vars['countriesCredits'];
        $this->assertEquals(1, count($countriesCredits));
        $this->assertEqual('Uganda', $countriesCredits[0]['country']);
        $this->assertEqual('test', $countriesCredits[0]['codes'][0]['programs'][0]['name']);
        $this->assertEqual(1, $countriesCredits[0]['codes'][0]['programs'][0]['outgoing']);

        $this->testAction('/creditViewer/index?predefined-timeframe=current-month');

        $this->assertTrue(isset($this->vars['countriesCredits']));
        $countriesCredits = $this->vars['countriesCredits'];
        $this->assertEquals(0, count($countriesCredits));
    }
}

