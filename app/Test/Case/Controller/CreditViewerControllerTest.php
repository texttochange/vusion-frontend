<?php
App::uses('ProgramSpecificMongoModel', 'Model');
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
        //Configure::write("mongo_db", "testdbmongo");
        parent::setUp();
        
        $this->Viewer = new TestCreditViewerController();
        $this->Viewer->constructClasses();
        

        $this->ShortCode = ClassRegistry::init('ShortCode');
        $this->CreditLog = ClassRegistry::init('CreditLog');
        $this->ProgramSettingTest = ProgramSpecificMongoModel::init(
            'ProgramSetting', 'testdbprogram', true);
        $this->ProgramSettingM6H = ProgramSpecificMongoModel::init(
            'ProgramSetting', 'm6h', true);
        $this->ProgramSettingTrial = ProgramSpecificMongoModel::init(
            'ProgramSetting', 'trial', true);
        $this->ProgramSettingM9h = ProgramSpecificMongoModel::init(
            'ProgramSetting', 'm9h', true);
        /*
        $this->ShortCode           = new ShortCode(array('database' => "testdbmongo"));
        $this->CreditLog           = new CreditLog(array('database' => "testdbmongo"));
        $this->ProgramSettingTest  = new ProgramSetting(array('database' => 'testdbprogram'));
        $this->ProgramSettingM6H   = new ProgramSetting(array('database' => 'm6h'));
        $this->ProgramSettingTrial = new ProgramSetting(array('database' => 'trial'));
        $this->ProgramSettingM9h   = new ProgramSetting(array('database' => 'm9h'));*/
        
        $this->dropData();
    }
    
    
    protected function dropData()
    {
        $this->ShortCode->deleteAll(true, false);
        $this->CreditLog->deleteAll(true, false);        
        
        $this->ProgramSettingTest->deleteAll(true, false);  
        $this->ProgramSettingM6H->deleteAll(true, false);
        $this->ProgramSettingTrial->deleteAll(true, false);
        $this->ProgramSettingM9h->deleteAll(true, false);
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
    
    
    public function testIndex_not_timeframed()
    {
        $this->_saveShortcodesInMongoDatabase();
        
        //$this->ProgramSettingTest = new ProgramSetting(array('database' => 'testdbprogram'));
        $this->ProgramSettingTest->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSettingTest->saveProgramSetting('shortcode','256-8282');
        
        #One recent creditlog
        $creditLog = ScriptMaker::mkCreditLog(
            'program-credit-log', date('Y-m-d'), 'testdbprogram', '256-8282');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog);
        
        #One old creditLog
        $creditLog = ScriptMaker::mkCreditLog(
            'program-credit-log', '2012-04-10', 'testdbprogram', '256-8282');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog);
        
        //$this->ProgramSettingM6H = new ProgramSetting(array('database' => 'm6h'));
        $this->ProgramSettingM6H->saveProgramSetting('timezone','Africa/Daresalaam');
        $this->ProgramSettingM6H->saveProgramSetting('shortcode','256-8181');
        
        //not time frame so only show current month
        $this->testAction('/creditViewer/index');
        
        $this->assertTrue(isset($this->vars['countriesCredits']));
        $countriesCredits = $this->vars['countriesCredits'];
        $this->assertEquals(1, count($countriesCredits));
        $this->assertEqual('Uganda', $countriesCredits[0]['country']);
        $this->assertEqual('test', $countriesCredits[0]['codes'][0]['programs'][0]['program-name']);
        $this->assertEqual(1, $countriesCredits[0]['codes'][0]['programs'][0]['outgoing']);
    }
    
    
    public function testIndex_timeframed()
    {
        $this->_saveShortcodesInMongoDatabase();
        
        //$this->ProgramSettingTest = new ProgramSetting(array('database' => 'testdbprogram'));
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
        
        //$this->ProgramSettingM6H = new ProgramSetting(array('database' => 'm6h'));
        $this->ProgramSettingM6H->saveProgramSetting('timezone','Africa/Daresalaam');
        $this->ProgramSettingM6H->saveProgramSetting('shortcode','256-8181');
        
        $this->testAction('/creditViewer/index?date-from=01%2F03%2F2014');
        
        $this->assertTrue(isset($this->vars['countriesCredits']));
        $countriesCredits = $this->vars['countriesCredits'];
        $this->assertEquals(1, count($countriesCredits));
        $this->assertEqual('Uganda', $countriesCredits[0]['country']);
        $this->assertEqual('test', $countriesCredits[0]['codes'][0]['programs'][0]['program-name']);
        $this->assertEqual(1, $countriesCredits[0]['codes'][0]['programs'][0]['outgoing']);
        
        $this->testAction('/creditViewer/index?date-from=01%2F03%2F2013&date-to=01%2F05%2F2013');
        
        $this->assertTrue(isset($this->vars['countriesCredits']));
        $countriesCredits = $this->vars['countriesCredits'];
        $this->assertEquals(1, count($countriesCredits));
        $this->assertEqual('Uganda', $countriesCredits[0]['country']);
        $this->assertEqual('test', $countriesCredits[0]['codes'][0]['programs'][0]['program-name']);
        $this->assertEqual(1, $countriesCredits[0]['codes'][0]['programs'][0]['outgoing']);
        
        $this->testAction('/creditViewer/index?predefined-timeframe=current-month');
        
        $this->assertTrue(isset($this->vars['countriesCredits']));
        $countriesCredits = $this->vars['countriesCredits'];
        $this->assertEquals(0, count($countriesCredits));
    }
    
    
    public function testExport()
    {
        $this->_saveShortcodesInMongoDatabase();
        
        #One recent creditlog
        //$this->ProgramSettingTest = new ProgramSetting(array('database' => 'testdbprogram'));
        $this->ProgramSettingTest->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSettingTest->saveProgramSetting('shortcode','256-8282');
        
        $creditLog = ScriptMaker::mkCreditLog(
            'program-credit-log', date('Y-m-d'), 'testdbprogram', '256-8282');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog);
        
        $creditLog = ScriptMaker::mkCreditLog(
            'program-credit-log', date('Y-m-d'), 'm6h', '256-8282');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog);
        
        $garbageCreditLog = ScriptMaker::mkCreditLog(
            'garbage-credit-log', date('Y-m-d'), '' , '256-8282', 10, 25);
        $this->CreditLog->create();        
        $this->CreditLog->save($garbageCreditLog);
        
        //$this->ProgramSettingTrial = new ProgramSetting(array('database' => 'trial'));
        $this->ProgramSettingTrial->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSettingTrial->saveProgramSetting('shortcode','256-8181');
        $creditLog = ScriptMaker::mkCreditLog(
            'program-credit-log', date('Y-m-d'), 'trial', '256-8181');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog); 
        
        //$this->ProgramSettingM9h = new ProgramSetting(array('database' => 'm9h'));
        $this->ProgramSettingM9h->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSettingM9h->saveProgramSetting('shortcode','254-21222');
        $creditLog = ScriptMaker::mkCreditLog(
            'program-credit-log', date('Y-m-d'), 'm9h', '254-21222');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog);
        
        #One old creditLog
       // $this->ProgramSettingTest = new ProgramSetting(array('database' => 'testdbprogram'));
        $this->ProgramSettingTest->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSettingTest->saveProgramSetting('shortcode','256-8282');
        $creditLog = ScriptMaker::mkCreditLog(
            'program-credit-log', '2012-04-10', 'testdbprogram', '256-8282');
        $this->CreditLog->create();        
        $this->CreditLog->save($creditLog);
        
        $this->testAction("/creditViewer/export?date-from=01%2F03%2F2014&date-to=01%2F05%2F2053");
        
        $this->assertTrue(isset($this->vars['fileName']));
        $this->assertFileEquals(
            TESTS . 'files/exported_creditViewer.csv',
            WWW_ROOT . 'files/programs/creditViewer/' . $this->vars['fileName']);
        
        //Asserting that programName "creditViewer" is adding to export file
        $this->assertEquals(
            substr($this->vars['fileName'], 0, -14),
            'creditViewer_');
    }
    
    
}

