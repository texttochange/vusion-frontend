<?php
App::uses('ProgramSettingsController', 'Controller');

class TestProgramSettingsController extends ProgramSettingsController
{
/**
 * Auto render
 *
 * @var boolean
 */
    public $autoRender = false;

/**
 * Redirect action
 *
 * @param mixed $url
 * @param mixed $status
 * @param boolean $exit
 * @return void
 */
    public function redirect($url, $status = null, $exit = true) {
        $this->redirectUrl = $url;
    }


}


class ProgramSettingsControllerTestCase extends ControllerTestCase 
{
    /**
    * Data
    *
    */

    var $programData = array(
        0 => array( 
            'Program' => array(
            'name' => 'Test Name',
            'url' => 'testurl',
            'timezone' => 'utc',
            'database' => 'testdbprogram'
        )
     ));


    public function setUp()
    {
        parent::setUp();
        $this->ProgramSettings = new TestProgramSettingsController();
        $this->dropData();
    }


    protected function dropData()
    {
        $this->instanciateProgramSettingsModel();
        $this->ProgramSettings->ProgramSetting->deleteAll(true, false);
    }


    protected function instanciateProgramSettingsModel() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);
        $this->ProgramSettings->ProgramSetting = new ProgramSetting($options);
    }
    

    public function tearDown()
    {
        $this->dropData();
        unset($this->ProgramSettings);
        parent::tearDown();
    }


    protected function mockProgramAccess()
    {
        $ProgramSettings = $this->generate('ProgramSettings', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
            ),
            'models' => array(
                'Program' => array('find', 'count'),
                'Group' => array()
             )
         ));
        
        $ProgramSettings->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
            
        $ProgramSettings->Program
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($this->programData));

        $ProgramSettings->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls(
                '4', 
                '2',
                $this->programData[0]['Program']['database'],
                $this->programData[0]['Program']['name']
                )); 
    }


/**
 * Test Methods
 */
    public function testEdit() 
    {
        $this->mockProgramAccess();

        $programSettings = array(
            'ProgramSettings' => array(
                'country'=>'uganda',
                'timezone'=> 'EAT'
                )
            );
            
        $this->testAction("/testurl/programSettings/edit", array(
            'method' => 'post',
            'data' => $programSettings
            ));
            
        $this->assertEquals($programSettings, $this->result);
    }


}


