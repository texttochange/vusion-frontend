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
    
    
        var $jsonCall =  '{
            "id": 2709,
            "participants_url": "/api/surveys/2709/reporters/csv",
            "questions": [
            {
                "id": 7209,
                "question_type": "select",
                "question_text": "Authentic beard yuccie vinegar.?",
                "answers": [
                {
                    "id": 21442,
                    "answer_text": "austin"
                },
                {
                    "id": 21443,
                    "answer_text": "chia"
                },
                {
                    "id": 21444,
                    "answer_text": "lumbersexual"
                }
                ]
            },
            {
                "id": 7210,
                "question_type": "select",
                "question_text": "Try-hard mixtape organic tousled yuccie iphone disrupt quinoa bitters.?",
                "answers": [
                {
                    "id": 21445,
                    "answer_text": "mumblecore"
                },
                {
                    "id": 21446,
                    "answer_text": "microdosing"
                },
                {
                    "id": 21447,
                    "answer_text": "pour-over"
                }
                ]
            },
            {
                "id": 7211,
                "question_type": "select",
                "question_text": "Freegan organic disrupt asymmetrical intelligentsia beard.?",
                "answers": [
                {
                    "id": 21448,
                    "answer_text": "cronut"
                },
                {
                    "id": 21449,
                    "answer_text": "hashtag"
                },
                {
                    "id": 21450,
                    "answer_text": "hoodie"
                }
                ]
            }
            ]
        }';
    
    public function setUp()
    {
        parent::setUp();
        $this->InstantSurvery = new TestInstantSurveryController();
        $this->Program = ClassRegistry::init('Program');
        $this->ShortCode = ClassRegistry::init('ShortCode');
        $this->ProgramSettingTest = ProgramSpecificMongoModel::init('ProgramSetting', 'survey2709', true);
                
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
    
    
    public function testAddSurvery_with_questions() 
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
        
        $programDialogue = ProgramSpecificMongoModel::init(
            'Dialogue', 'survery2709', true);
        $programDialogue->deleteAll(true, false);        
                
        $data = json_decode($this->jsonCall, true);
        //$data = $this->jsonCall;
        
        $this->testAction('/InstantSurvery/addSurvery.json', array('data' => $data, 'method' => 'post'));
        $this->assertEqual(1, $programDialogue->find('count'));
    }   
    
    
}
