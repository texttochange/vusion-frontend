<?php
App::uses('ShortCodesController', 'Controller');
App::uses('Program', 'Model');
App::uses('ScriptMaker', 'Lib');



class TestShortCodesController extends ShortCodesController
{

    public $autoRender = false;
    
    
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }    
    
}


class ShortCodesControllerTestCase extends ControllerTestCase
{
    public $fixtures = array('app.program', 'app.user', 'app.programsUser');
    
    
    var $databaseName = "testdbmongo";
    
    public function setUp() 
    {
        parent::setUp();
        
        $this->ShortCodes = new TestShortCodesController();
        $this->ShortCode = ClassRegistry::init('ShortCode');
        $this->Program = ClassRegistry::init('Program');
        $this->ProgramSettingTest = ProgramSpecificMongoModel::init('ProgramSetting', 'testdbprogram', true);
        $this->ProgramSettingM6H = ProgramSpecificMongoModel::init('ProgramSetting', 'm6h', true);
        $this->maker = new ScriptMaker();
        $this->dropData();
    }
    
    
    protected function dropData()
    {
        $this->ShortCode->deleteAll(true, false);
        $this->ProgramSettingTest->deleteAll(true, false);  
        $this->ProgramSettingM6H->deleteAll(true, false);
    }
    
    
    public function tearDown() 
    {
        $this->dropData(); 
        unset($this->Program);
        unset($this->ProgramSetting);
        unset($this->ShortCodes);
        parent::tearDown();
    }
    
	
    public function mockProgramAccess()
    {
        $shortCodes = $this->generate('ShortCodes', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read'),
                'Auth' => array('loggedIn'),
                ),
            'models' => array(
                'Group' => array()
                )
            ));
        
        $shortCodes->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $shortCodes->Auth
        ->expects($this->any())
        ->method('loggedIn')
        ->will($this->returnValue('true'));

        $shortCodes->Session
        ->expects($this->any())
        ->method('read')
        ->will($this->onConsecutiveCalls('1','1','1'));	   
        
        return $shortCodes;
    }
    
    
    public function testIndex()
    {
        $this->mockProgramAccess();
        
        $this->ShortCode->create();
        $this->ShortCode->save(array(
            'country' => 'uganda',
            'shortcode' => 8282,
            'international-prefix' => 256
            ));
        
        $this->testAction("/shortCodes/index");
        
        $this->assertEquals(1, count($this->vars['shortcodes']));		
    }
    
    
    public function testAdd()
    {
        $this->mockProgramAccess();
        
        $shortcode = array(
            'ShortCode' => array(
                'country' => 'uganda',
                'shortcode' => 8282,
                'international-prefix' => 256
                )
            );
        $this->testAction("/shortCodes/add", array(
            'method' => 'post',
            'data' => $shortcode
            ));
        $this->assertEquals(1, $this->ShortCode->find('count'));
    }
    
    
    public function testEdit()
    {
        
        $shortCodes = $this->mockProgramAccess();
        
        $shortcodes = array(
            'country' => 'uganda',
            'shortcode' => 8282,
            'international-prefix' => 256
            );
        $this->ShortCode->create();
        $data = $this->ShortCode->save($shortcodes);	    
        
        $this->testAction("/shortCodes/edit/".$data['ShortCode']['_id'], array(
            'method' => 'post',
            'data' => array(
                'ShortCode' => array(
                    'country' => 'uganda',
                    'shortcode' => 8383,
                    'international-prefix' => 256
                    )
                )
            ));
        $this->assertEquals(8383, $shortCodes->data['ShortCode']['shortcode']);
    }
    
    
    public function testDelete()
    {
        $this->mockProgramAccess();
        
        $shortcodes = array(
            'ShortCode' => array(
                'country' => 'uganda',
                'shortcode' => 8282,
                'international-prefix' => 256
                )
            );
        $this->ShortCode->create();
        $data = $this->ShortCode->save($shortcodes);
        
        $this->testAction("/shortCodes/delete/".$data['ShortCode']['_id']);
        
        $this->assertEquals(0, $this->ShortCode->find('count'));
    }
    
    
    public function testArchive()
    {
        $this->mockProgramAccess();
        $this->ProgramSettingTest->saveProgramSetting('shortcode', '343');
        $this->ProgramSettingM6H->saveProgramSetting('shortcode', '8282');
        $shortcodes = array(
            'ShortCode' => array(
                'country' => 'uganda',
                'shortcode' =>343,
                'international-prefix' => 256
                )
            );
        $this->ShortCode->create();
        $data = $this->ShortCode->save($shortcodes);
        
        $shortcodeRun = array(
            'ShortCode' => array(
                'country' => 'uganda',
                'shortcode' =>8282,
                'international-prefix' => 256
                )
            );
        $this->ShortCode->create();
        $this->ShortCode->save($shortcodeRun);
        
        $this->testAction("/shortCodes/archive/".$data['ShortCode']['_id']);
        
        $this->assertEquals(2, $this->ShortCode->find('count'));
        $this->assertEquals(1, $this->ShortCode->find('count', array('conditions'=> array('status'=>'archived'))));
    }
    
    
    public function testUnarchive()
    {
        $this->mockProgramAccess();
        $this->ProgramSettingTest->saveProgramSetting('shortcode', '343');
        $this->ProgramSettingM6H->saveProgramSetting('shortcode', '8282');
        $shortcodes = array(
            'ShortCode' => array(
                'country' => 'uganda',
                'shortcode' =>343,
                'international-prefix' => 256,
                'status' => 'archived'
                )
            );
        $this->ShortCode->create();
        $data = $this->ShortCode->save($shortcodes);
        
        $shortcodeRun = array(
            'ShortCode' => array(
                'country' => 'uganda',
                'shortcode' =>8282,
                'international-prefix' => 256,
                'status' => 'running'
                )
            );
        $this->ShortCode->create();
        $this->ShortCode->save($shortcodeRun);
        
        $this->testAction("/shortCodes/unarchive/".$data['ShortCode']['_id']);
        
        $this->assertEquals(2, $this->ShortCode->find('count'));
        $this->assertEquals(0, $this->ShortCode->find('count', array('conditions'=> array('status'=>'archived'))));
        $this->assertEquals(2, $this->ShortCode->find('count', array('conditions'=> array('status'=>'running'))));
    }
    
    
}
