<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('KeywordComponent', 'Controller/Component');
App::uses('ScriptMaker', 'Lib');
App::uses('ProgramSpecificMongoModel', 'Model');


class TestKeywordComponentController extends Controller
{
}


class KeywordComponentTest extends CakeTestCase
{

    public $KeywordComponent = null;
    public $Controller = null;
    public $fixtures = array('app.program', 'app.user', 'app.programsUser');


    public function setUp() 
    {
        parent::setUp();
        $Collection = new ComponentCollection();
        $this->KeywordComponent = new KeywordComponent($Collection);
        $CakeRequest = new CakeRequest();
        $CakeResponse = new CakeResponse();
        $this->Controller = new TestKeywordComponentController($CakeRequest, $CakeResponse);
        $this->KeywordComponent->startup($this->Controller);

        $this->Maker = new ScriptMaker();
        $this->instanciateModels();
        $this->instanciateExternalModels('m6h');
    }


    public function tearDown() {
        $this->dropData();
        parent::tearDown();
        // Clean up after we're done
        unset($this->KeywordComponent);
        unset($this->Controller);
    }

    protected function instanciateModels()
    {
        $dbName = 'testdbprogram';
        $this->Dialogue = ProgramSpecificMongoModel::init(
            'Dialogue', $dbName, true);
        $this->Request = ProgramSpecificMongoModel::init(
            'Request', $dbName, true);
        $this->ProgramSetting = ProgramSpecificMongoModel::init(
            'ProgramSetting', $dbName, true);
    }

    protected function instanciateExternalModels($dbName)
    {
        $this->externalModels['dialogue'] = ProgramSpecificMongoModel::init(
            'Dialogue', $dbName, true);
        $this->externalModels['request'] = ProgramSpecificMongoModel::init(
            'Request', $dbName, true);
        $this->externalModels['programSetting'] = ProgramSpecificMongoModel::init(
            'ProgramSetting', $dbName, true);   
    }

    protected function dropData()
    {
        //As this model is created on the fly, need to instantiate again
        $this->Dialogue->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
        $this->Request->deleteAll(true, false);
        
        foreach ($this->externalModels as $model) {
            $model->deleteAll(true, false);
        }
        
    }


    public function testAreProgramKeywordsUsedByOtherPrograms_dialogueKeywordUsedInOtherProgramDialogue() 
    {
        $dialogue = $this->Maker->getOneDialogue('usedKeyword');
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        $otherDialogue = $this->Maker->getOneDialogue('usedkeyword');
        $savedDialogue = $this->externalModels['dialogue']->saveDialogue($otherDialogue);
        $this->externalModels['dialogue']->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(array('key' => 'shortcode', 'value' => '256-8181'));
     
        $expected = array(
            'usedkeyword' => array(
                'program-db' => 'm6h',
                'program-name' => 'm6h', 
                'by-type' => 'Dialogue',
                'dialogue-id' => $savedDialogue['Dialogue']['dialogue-id'],
                'dialogue-name' => $savedDialogue['Dialogue']['name']));
   
        $valid = $this->KeywordComponent->areProgramKeywordsUsedByOtherPrograms('testdbprogram', '256-8181');
        $this->assertEqual($valid, $expected);    
    }


    public function testAreProgramKeywordsUsedByOtherPrograms_dialogueKeywordUsedInOtherProgramRequest() 
    {
        $dialogue = $this->Maker->getOneDialogue('KEYWORD');
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        $this->externalModels['request']->create();
        $savedRequest = $this->externalModels['request']->save($this->Maker->getOneRequest('keyword request'));
        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(array('key' => 'shortcode', 'value' => '256-8181'));
     
        $expected = array(
            'keyword' => array(
                'program-name' => 'm6h',
                'program-db' => 'm6h',
                'by-type' => 'Request',
                'request-id' => $savedRequest['Request']['_id']."",
                'request-name' => $savedRequest['Request']['keyword']));
   
        $valid = $this->KeywordComponent->areProgramKeywordsUsedByOtherPrograms(
            'testdbprogram', '256-8181', array('keyword'));
        $this->assertEqual($valid, $expected);
    }


    public function testAreProgramKeywordsUsedByOtherPrograms_requestKeywordUsedInOtherProgramRequest() 
    {

        $this->Request->create();
        $this->Request->save($this->Maker->getOneRequest('keyword1, Keyword2 other'));   
        $this->externalModels['request']->create();
        $savedRequest = $this->externalModels['request']->save($this->Maker->getOneRequest('keyword2 stuff'));
        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(array('key'=>'shortcode', 'value'=>'256-8181'));
        
        $expected = array(
            'keyword2' => array(
                'program-name' => 'm6h',
                'program-db' => 'm6h',
                'by-type' => 'Request',                 
                'request-id' => $savedRequest['Request']['_id']."",
                'request-name' => $savedRequest['Request']['keyword']));

        $valid = $this->KeywordComponent->areProgramKeywordsUsedByOtherPrograms('testdbprogram', '256-8181');
        $this->assertEqual($valid, $expected);    
    }


    public function testAreProgramKeywordsUsedByOtherPrograms_requestKeywordUsedInOtherProgramDialogue() 
    {
        $this->Request->create();
        $this->Request->save($this->Maker->getOneRequest());
        $dialogue = $this->Maker->getOneDialogue('Keyword');        
        $savedDialogue = $this->externalModels['dialogue']->saveDialogue($dialogue);
        $this->externalModels['dialogue']->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(array('key'=>'shortcode', 'value'=>'256-8181'));
     
        $expected = array(
            'keyword' => array(
                'program-db' => 'm6h',
                'program-name' => 'm6h', 
                'by-type' => 'Dialogue',
                'dialogue-id' => $savedDialogue['Dialogue']['dialogue-id'],
                'dialogue-name' => $savedDialogue['Dialogue']['name']));
   
        $valid = $this->KeywordComponent->areProgramKeywordsUsedByOtherPrograms('testdbprogram', '256-8181');
        $this->assertEqual($valid, $expected);    
    }


    public function testAreUsedKeywords() 
    {
        $this->Request->create();
        $savedRequest = $this->Request->save($this->Maker->getOneRequest('keyword1 stuff, Keyword1 other'));
        $dialogue = $this->Maker->getOneDialogue('KEYWORD2');
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
           
        $this->externalModels['request']->create();
        $savedOtherRequest = $this->externalModels['request']->save($this->Maker->getOneRequest('keyword3 stuff'));
        $otherDialogue = $this->Maker->getOneDialogue('keyword4, keyword5');
        $savedOtherDialogue = $this->externalModels['dialogue']->saveDialogue($otherDialogue);
        $this->externalModels['dialogue']->makeDraftActive($savedOtherDialogue['Dialogue']['dialogue-id']);
        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(array('key'=>'shortcode', 'value'=>'256-8181'));
        
        $expected = array(
            'keyword1' => array(
                'program-name' => '',
                'program-db' => 'testdbprogram',
                'by-type' => 'Request',                 
                'request-id' => $savedRequest['Request']['_id']."",
                'request-name' => $savedRequest['Request']['keyword']),
             'keyword2' => array(
                'program-name' => '',
                'program-db' => 'testdbprogram',
                'by-type' => 'Dialogue',                 
                'dialogue-id' => $savedDialogue['Dialogue']['dialogue-id'],
                'dialogue-name' => $savedDialogue['Dialogue']['name']),
             'keyword3' => array(
                 'program-name' => 'm6h',
                 'program-db' => 'm6h',
                 'by-type' => 'Request',                 
                 'request-id' => $savedOtherRequest['Request']['_id']."",
                 'request-name' => $savedOtherRequest['Request']['keyword']),
             'keyword4' => array(
                 'program-name' => 'm6h',
                 'program-db' => 'm6h',
                 'by-type' => 'Dialogue',                 
                 'dialogue-id' => $savedOtherDialogue['Dialogue']['dialogue-id'],
                 'dialogue-name' => $savedOtherDialogue['Dialogue']['name']),
             'keyword5' => array(
                 'program-name' => 'm6h',
                 'program-db' => 'm6h',
                 'by-type' => 'Dialogue',                 
                 'dialogue-id' => $savedOtherDialogue['Dialogue']['dialogue-id'],
                 'dialogue-name' => $savedOtherDialogue['Dialogue']['name']));

        $valid = $this->KeywordComponent->areUsedKeywords(
            'testdbprogram', '256-8181', array('keyword1', 'keyword2', 'keyword3', 'keyword4', 'keyword5', 'keyword6'));
        $this->assertEqual($valid, $expected);    
    }


    public function testAreUsedKeywords_withRequestKeyphrases()
    {
        $this->Request->create();
        $savedRequest = $this->Request->save($this->Maker->getOneRequest('keyword1 stuff, Keyword1 other'));

        $expected = array(
            'keyword1 stuff' => array(
                'program-name' => '',
                'program-db' => 'testdbprogram',
                'by-type' => 'Request',                 
                'request-id' => $savedRequest['Request']['_id']."",
                'request-name' => $savedRequest['Request']['keyword']));

        $valid = $this->KeywordComponent->areUsedKeywords(
            'testdbprogram', '256-8181', array('keyword1 shawdow', 'keyword1 stuff'), 'Request');
        $this->assertEqual($valid, $expected);
    }


    public function testAreUsedKeywords_withUnauthorizedKeywords() 
    {
        $settings = array(
            'shortcode' => '256-8181',
            'sms-forwarding-allowed' => 'none',
            'authorized-keywords' => array('keyword1', 'keyword2'),
            'contact' => '1'
            );
        $this->ProgramSetting->saveProgramSettings($settings);
        
        $expected = array(
            'keyword3' => array(
                'program-name' => '',
                'program-db' => 'testdbprogram',
                'by-type' => 'ProgramSetting'));

        $valid = $this->KeywordComponent->areUsedKeywords(
            'testdbprogram', '256-8181', array('keyword3 shawdow', 'keyword1 stuff'), 'Request');
        $this->assertEqual($valid, $expected);    
    }


    public function testAreKeywordsUsedByOtherPrograms_keywordUsedInOtherProgramRequest() 
    {   
        $this->externalModels['request']->create();
        $savedRequest = $this->externalModels['request']->save($this->Maker->getOneRequest());
        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(array('key'=>'shortcode', 'value'=>'256-8181'));
     
        $expected = array(
            'keyword' => array(
                'program-db' => 'm6h',
                'program-name' => 'm6h', 
                'by-type' => 'Request',
                'request-id' => $savedRequest['Request']['_id']."",
                'request-name' => $savedRequest['Request']['keyword']));
   
        $valid = $this->KeywordComponent->areKeywordsUsedByOtherPrograms('testdbprogram', '256-8181', array('KEYWORD'));
        $this->assertEqual($valid, $expected);    
    }


    public function testAreKeywordsUsedByOtherPrograms_keywordUsedInArchivedProgram() 
    {   
        $this->instanciateExternalModels('trial');

        $this->externalModels['request']->create();
        $savedRequest = $this->externalModels['request']->save($this->Maker->getOneRequest());
        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(array('key'=>'shortcode', 'value'=>'256-8181'));
     
        $expected = array();
   
        $valid = $this->KeywordComponent->areKeywordsUsedByOtherPrograms(
            'testdbprogram',
            '256-8181',
            array('KEYWORD'));
        $this->assertEqual($valid, $expected);    
    }


    public function testAreKeywordsUsedByOtherPrograms_keywordUsedInOtherProgramDialogue() 
    {
        $dialogue = $this->Maker->getOneDialogue('Keyword');        
        $savedDialogue = $this->externalModels['dialogue']->saveDialogue($dialogue);
        $this->externalModels['dialogue']->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(array('key'=>'shortcode', 'value'=>'256-8181'));

        $expected = array(
            'keyword' => array(
                'program-db' => 'm6h',
                'program-name' => 'm6h',
                'by-type' => 'Dialogue',
                'dialogue-id' => $savedDialogue['Dialogue']['dialogue-id'],
                'dialogue-name' => $savedDialogue['Dialogue']['name']));
        
        $valid = $this->KeywordComponent->areKeywordsUsedByOtherPrograms('testdbprogram', '256-8181', array('KEYWORD'));
        $this->assertEqual($valid, $expected);    
    }


    public function testFoundKeywordsToMessage_request()
    {
        $validation = array(
            'keyword' => array(
                'program-db' => 'm6h',
                'program-name' => 'm6h', 
                'by-type' => 'Request'));
        $expected = "'keyword' already used by a Request of program 'm6h'.";
        
        $this->assertEqual(
            $expected,
            $this->KeywordComponent->foundKeywordsToMessage('otherdb', $validation));
    }


    public function testFoundKeywordsToMessage_dialogue()
    {
        $validation = array(
            'keyword' => array(
                'program-db' => 'm6h',
                'program-name' => 'm6h',
                'by-type' => 'Dialogue'));
        $expected = "'keyword' already used by a Dialogue of program 'm6h'.";
        
        $this->assertEqual(
            $expected,
            $this->KeywordComponent->foundKeywordsToMessage('otherdb', $validation));
    }


}