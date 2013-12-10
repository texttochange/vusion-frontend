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


    public function testAreKeywordsUsedByOtherPrograms_failed_dialogueKeywordUsedInOtherProgramDialogue() 
    {
        $dialogue = $this->Maker->getOneDialogue('usedKeyword');
        
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        $savedDialogue = $this->externalModels['dialogue']->saveDialogue($dialogue);
        $this->externalModels['dialogue']->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);

        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'256-8181'
                )
            );
        
        $valid = $this->KeywordComponent->AreKeywordsUsedByOtherPrograms('testdbprogram', '256-8181');
        $this->assertEqual($valid['status'], 'fail');    
    }


    public function testAreKeywordsUsedByOtherPrograms_failed_dialogueKeywordUsedInOtherProgramRequest() 
    {
        $dialogue = $this->Maker->getOneDialogue('Keyword');
        
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
        
        $this->externalModels['request']->create();
        $this->externalModels['request']->save($this->Maker->getOneRequest());

        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'256-8181'
                )
            );
        
        $valid = $this->KeywordComponent->AreKeywordsUsedByOtherPrograms('testdbprogram', '256-8181');
        $this->assertEqual($valid['status'], 'fail');    
    }


    public function testAreKeywordsUsedByOtherPrograms_failed_requestKeywordUsedInOtherProgramRequest() 
    {
        $this->Request->create();
        $this->Request->save($this->Maker->getOneRequest());
        
        $this->externalModels['request']->create();
        $this->externalModels['request']->save($this->Maker->getOneRequest());

        $this->externalModels['programSetting']->create();
        $this->externalModels['programSetting']->save(
            array(
                'key'=>'shortcode',
                'value'=>'256-8181'
                )
            );
        
        $valid = $this->KeywordComponent->AreKeywordsUsedByOtherPrograms('testdbprogram', '256-8181');
        $this->assertEqual($valid['status'], 'fail');    
    }


    public function testAreKeywordsUsedByOtherPrograms_failed_requestKeywordUsedInOtherProgramDialogue() 
    {
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
        
        $valid = $this->KeywordComponent->AreKeywordsUsedByOtherPrograms('testdbprogram', '256-8181');
        $this->assertEqual($valid['status'], 'fail');    
    }


}