<?php
App::uses('ProgramSettingsController', 'Controller');
App::uses('ProgramSpecificMongoModel', 'Model');


class TestProgramSettingsController extends ProgramSettingsController
{
    
    public $autoRender = false;    

    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }    
    
}


class ProgramSettingsControllerTestCase extends ControllerTestCase
{
    
    var $programData = array(
        0 => array( 
            'Program' => array(
                'name' => 'Test Name',
                'url' => 'testurl',
                'timezone' => 'utc',
                'database' => 'testdbprogram',
                'status' => 'running'
                )
            ));
    
    
    public function setUp()
    {
        parent::setUp();
        $this->ProgramSettings = new TestProgramSettingsController();
        
        $dbName = $this->programData[0]['Program']['database'];
        $this->ProgramSetting = ProgramSpecificMongoModel::init(
            'ProgramSetting', $dbName, true);
        $this->dropData();
    }
    
    
    protected function dropData()
    {
        //$this->instanciateProgramSettingsModel();
        $this->ProgramSetting->deleteAll(true, false);
    }
    
/*    
    protected function instanciateProgramSettingsModel() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);
        
        $this->ProgramSettings->ProgramSetting = new ProgramSetting($options);
    }
  */  
    
    public function tearDown()
    {
        $this->dropData();
        unset($this->ProgramSettings);
        parent::tearDown();
    }
    
    
    protected function mockProgramAccess()
    {
        $programSettings = $this->generate('ProgramSettings', array(
            'components' => array(
                'Auth' => array('loggedIn', 'startup'),
                'Acl' => array('check'),
                'Session' => array('read', 'setFlash'),
                'Keyword' => array('areProgramKeywordsUsedByOtherPrograms', 'validationToMessage') 
                ),
            'models' => array(
                'Program' => array('find', 'count'),
                'Group' => array()
                ),
            'methods' => array(
                '_instanciateVumiRabbitMQ',
                '_notifyUpdateProgramSettings',
                )
            ));
        
        $programSettings->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $programSettings->Auth
        ->expects($this->any())
        ->method('loggedIn')
        ->will($this->returnValue('true'));

        $programSettings->Program
        ->expects($this->once())
        ->method('find')
        ->will($this->returnValue($this->programData));
        
        $programSettings->Session
        ->expects($this->any())
        ->method('read')
        ->will(
            $this->returnValue(
                $this->programData[0]['Program']['database']
                )
            ); 
        return $programSettings;
    }
    
    public function testEdit_ok() 
    {
        $programSettingsController = $this->mockProgramAccess();
        $programSettingsController
        ->expects($this->once())
        ->method('_notifyUpdateProgramSettings')
        ->with('testurl')
        ->will($this->returnValue(true));
        
        $programSettingsController->Keyword
        ->expects($this->once())
        ->method('areProgramKeywordsUsedByOtherPrograms')
        ->will($this->returnValue(array()));
        
        $programSettings = array(
            'ProgramSetting' => array(
                'shortcode'=>'8282',
                'international-prefix'=>'256',
                'timezone'=> 'EAT',
                'credit-type' => 'none'
                )
            );
        
        $this->testAction("/testurl/programSettings/edit", array(
            'method' => 'post',
            'data' => $programSettings
            ));
        
        $this->assertEquals($programSettings, $programSettingsController->data);
    }
    
    
    public function testEdit_fail() 
    {
        $programSettingsController = $this->mockProgramAccess();
        
        $programSettingsController->Keyword
        ->expects($this->once())
        ->method('areProgramKeywordsUsedByOtherPrograms')
        ->will($this->returnValue(
            array('KEYWORD' => array(
                'program-db' => 'm6h',
                'program-name' => 'my Program', 
                'by-type' => 'request'))));
        
        $programSettingsController->Session
        ->expects($this->once())
        ->method('setFlash')
        ->with("Save settings failed.");

        $programSettings = array(
            'ProgramSetting' => array(
                'shortcode'=>'8282',
                'international-prefix'=>'256',
                'timezone'=> 'EAT',
                'credit-type' => 'none'
                )
            );
        
        $this->testAction("/testurl/programSettings/edit", array(
            'method' => 'post',
            'data' => $programSettings
            ));
        
        $this->assertEquals($programSettings, $programSettingsController->data);
    }
 

}


