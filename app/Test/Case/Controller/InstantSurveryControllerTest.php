<?php
App::uses('InstantSurveryController', 'Controller');
App::uses('ScriptMaker', 'Lib');
App::uses('TestHelper', 'Lib');
App::uses('Dialogue', 'Model');
App::uses('Program', 'Model');
App::uses('ProgramSetting', 'Model');


class TestInstantSurveryController extends InstantSurveryController
{
    
    public $autoRender = false;
    
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
    
    protected function _instanciateVumiRabbitMQ() 
    {}
    
}


class InstantSurveryControllerTestCase extends ControllerTestCase
{
    
    public $fixtures = array('app.program','app.group','app.user', 'app.programsUser');
    
    
    var $jsonCall = array(
        'survey' => array(
            'uid'=> 'survey_uid0001',
            'participants_url' => 'http://askpeople.com/surveys/participants/survey_uid0001/list.csv',
            'questions' => array(
                0 => array(
                    'uid' => 'question_uid001',
                    'question_text' => 'example question 1?',
                    'answers' => array(
                        0 => array(
                            'uid' => 'answer_uid_001',
                            'answer_text' => 'yes',
                            ),
                        1 => array(
                            'uid' => 'answer_uid_002',
                            'answer_text' => 'no',
                            )
                        )                        
                    ),
                1 => array(
                    'uid' => 'question_uid002',
                    'question_text' => 'example question 2?',
                    'answers' => array(
                        0 => array(
                            'uid' => 'answer_uid_003',
                            'answer_text' => 'yes',
                            ),
                        1 => array(
                            'uid' => 'answer_uid_004',
                            'answer_text' => 'no',
                            )
                        )                        
                    )
                )
            )
        );
    
    
    public function setUp()
    {
        parent::setUp();
        $this->InstantSurvery = new TestInstantSurveryController();
        $this->Program = ClassRegistry::init('Program');
        $this->ShortCode = ClassRegistry::init('ShortCode');
        $this->ProgramSettingTest = ProgramSpecificMongoModel::init('ProgramSetting', 'surveyuid0001', true);
                
        $this->maker = new ScriptMaker();
    }
    
    
    protected function dropData()
    { 
        $this->Program->deleteAll(true, false);
        $this->ShortCode->deleteAll(true, false);;
        $this->ProgramSettingTest->deleteAll(true, false);
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        unset($this->InstantSurvery);
        parent::tearDown();
    }
    
    
    protected function mockProgramAccess()
    {
        $InstantSurvery = $this->generate(
            'InstantSurvery', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Auth' => array('user', 'loggedIn'),
                    'Session' => array('read'),
                    'Stats',
                    ),
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    )
                )
            );
        
        $InstantSurvery->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $InstantSurvery->Auth
        ->expects($this->any())
        ->method('loggedIn')
        ->will($this->returnValue('true'));
        
        $InstantSurvery->Auth
        ->staticExpects($this->any())
        ->method('user')
        ->will($this->returnValue(array(
            'id' => '8',
            'group_id' => '1')));
        
        return $InstantSurvery;
    }
    
    
   /* public function testAddSurvery() 
    {
        $InstantSurvery = $this->generate(
            'InstantSurvery', array(
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    '_startBackendWorker',
                    ),
                'components' => array(
                    'Auth' => array('user'),
                    )
                )
            );
        
        $InstantSurvery->Auth
        ->staticExpects($this->any())
        ->method('user')
        ->will($this->returnValue(array(
            'id' => '8',
            'group_id' => '1')));
        
        $InstantSurvery
        ->expects($this->once())
        ->method('_startBackendWorker')
        ->will($this->returnValue(true));
        
        
        $data = $this->jsonCall;
        
        $this->testAction('/InstantSurvery/addSurvery.json', array('data' => $data, 'method' => 'post'));
    }*/
    
    
    public function testAddSurvery_questions() 
    {
        $this->_saveShortcodesInMongoDatabase();
        
        $this->ProgramSettingTest = new ProgramSetting(array('database' => 'surveyuid0001'));
        $this->ProgramSettingTest->saveProgramSetting('timezone','EAT');
        $this->ProgramSettingTest->saveProgramSetting('international-prefix','256');
        
        
        $InstantSurvery = $this->generate(
            'InstantSurvery', array(
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    '_startBackendWorker',
                    ),
                'components' => array(
                    'Auth' => array('user'),
                    )
                )
            );
        
        $InstantSurvery->Auth
        ->staticExpects($this->any())
        ->method('user')
        ->will($this->returnValue(array(
            'id' => '8',
            'group_id' => '1')));
        
        $InstantSurvery
        ->expects($this->once())
        ->method('_startBackendWorker')
        ->will($this->returnValue(true));
        
        
        $data = $this->jsonCall;
        
        $this->testAction('/InstantSurvery/addSurvery.json', array('data' => $data, 'method' => 'post'));
        
        //$this->assertEqual(1, $programDialogue->find('count'));
        
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
    }
    
    
    
}
