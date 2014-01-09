<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('KeywordComponent', 'Controller/Component');
App::uses('ScriptMaker', 'Lib');

class TestKeywordComponentController extends Controller
{
}


class KewyordComponentTest extends CakeTestCase
{

    public $KeywordComponent = null;
    public $Controller = null;
    public $fixtures = array('app.program', 'app.user', 'app.programsUser');


    public function setUp() 
    {
        parent::setUp();
        $Collection = new ComponentCollection();
        $this->KeywordComponent = new KeywordComponent($Collection);
        $this->KeywordComponent->Program = ClassRegistry::init('Program');
        //Don't get why the useDbConfig is not properly configure by ClassResigtry
        $this->KeywordComponent->Program->useDbConfig = 'test';
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
        $options = array('database' => 'testdbprogram');

        $this->Dialogue       = new Dialogue($options);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->Request        = new Request($options);
    }

    protected function instanciateExternalModels($databaseName)
    {
        $this->externalModels['dialogue']       = new Dialogue(array('database' => $databaseName));
        $this->externalModels['programSetting'] = new ProgramSetting(array('database' => $databaseName));
        $this->externalModels['request'] = new Request(array('database' => $databaseName));
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

    public function testAreProgramKeywordsUsedByOtherPrograms_failed_dialogueKeywordUsedInOtherProgramDialogue() 
    {
        $expected = array('usedKeyword' => array('programName' => 'm6h', 'type' => 'dialogue'));

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        $otherDialogue = $this->Maker->getOneDialogue('usedkeyword');
        $savedDialogue = $this->externalModels['dialogue']->saveDialogue($otherDialogue);
        $this->externalModels['dialogue']->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'256-8181'
                )
            );
        
        $valid = $this->KeywordComponent->areProgramKeywordsUsedByOtherPrograms('testdbprogram', '256-8181');
        $this->assertEqual($valid, $expected);    
    }

    public function testAreProgramKeywordsUsedByOtherPrograms_failed_dialogueKeywordUsedInOtherProgramRequest() 
    {
        $expected = array('KEYWORD' => array('programName' => 'm6h', 'type' => 'request'));

        $dialogue = $this->Maker->getOneDialogue('KEYWORD');
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        $this->externalModels['request']->create();
        $this->externalModels['request']->save($this->Maker->getOneRequest('keyword request'));
        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'256-8181'
                )
            );
        
        $valid = $this->KeywordComponent->areProgramKeywordsUsedByOtherPrograms('testdbprogram', '256-8181');
        $this->assertEqual($valid, $expected);    
    }

    public function testAreProgramKeywordsUsedByOtherPrograms_failed_requestKeywordUsedInOtherProgramRequest() 
    {
        $expected = array('Keyword2' => array('programName' => 'm6h', 'type' => 'request'));

        $this->Request->create();
        $this->Request->save($this->Maker->getOneRequest('keyword1, Keyword2 other'));
        
        $this->externalModels['request']->create();
        $this->externalModels['request']->save($this->Maker->getOneRequest('keyword2 stuff'));

        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'256-8181'
                )
            );
        
        $valid = $this->KeywordComponent->areProgramKeywordsUsedByOtherPrograms('testdbprogram', '256-8181');
        $this->assertEqual($valid, $expected);    
    }

    public function testAreProgramKeywordsUsedByOtherPrograms_failed_requestKeywordUsedInOtherProgramDialogue() 
    {
        $expected = array('KEYWORD' => array('programName' => 'm6h', 'type' => 'dialogue'));

        $this->Request->create();
        $this->Request->save($this->Maker->getOneRequest());
        
        $dialogue = $this->Maker->getOneDialogue('Keyword');        
        $savedDialogue = $this->externalModels['dialogue']->saveDialogue($dialogue);
        $this->externalModels['dialogue']->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);

        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'256-8181'
                )
            );
        
        $valid = $this->KeywordComponent->areProgramKeywordsUsedByOtherPrograms('testdbprogram', '256-8181');
        $this->assertEqual($valid, $expected);    
    }

    public function testAreKeywordsUsedByOtherPrograms_failed_keywordUsedInOtherProgramRequest() 
    {
        $expected = array('KEYWORD' => array('programName' => 'm6h', 'type' => 'request'));
        
        $this->externalModels['request']->create();
        $this->externalModels['request']->save($this->Maker->getOneRequest());

        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'256-8181'
                )
            );
        
        $valid = $this->KeywordComponent->areKeywordsUsedByOtherPrograms('testdbprogram', '256-8181', array('KEYWORD'));
        $this->assertEqual($valid, $expected);    
    }

    public function testAreKeywordsUsedByOtherPrograms_failed_keywordUsedInOtherProgramDialogue() 
    {
        $expected = array('KEYWORD' => array('programName' => 'm6h', 'type' => 'dialogue'));

        $dialogue = $this->Maker->getOneDialogue('Keyword');        
        $savedDialogue = $this->externalModels['dialogue']->saveDialogue($dialogue);
        $this->externalModels['dialogue']->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);

        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'256-8181'
                )
            );
        
        $valid = $this->KeywordComponent->areKeywordsUsedByOtherPrograms('testdbprogram', '256-8181', array('KEYWORD'));
        $this->assertEqual($valid, $expected);    
    }

    public function testValidationToMessage_request()
    {
        $validation = array('KEYWORD' => array('programName' => 'm6h', 'type' => 'request'));
        $expected = "'KEYWORD' already used by a request of program 'm6h'.";
        
        $this->assertEqual(
            $expected,
            $this->KeywordComponent->validationToMessage($validation));
    }

    public function testValidationToMessage_dialogue()
    {
        $validation = array('KEYWORD' => array('programName' => 'm6h', 'type' => 'dialogue'));
        $expected = "'KEYWORD' already used by a dialogue of program 'm6h'.";
        
        $this->assertEqual(
            $expected,
            $this->KeywordComponent->validationToMessage($validation));
    }

}