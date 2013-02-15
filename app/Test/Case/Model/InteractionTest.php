<?php
App::uses('Interaction', 'Model');
App::uses('ScriptMaker', 'Lib');


class InteractionTestCase extends CakeTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->Interaction = new Interaction();
        $this->Maker       = new ScriptMaker();
    }

    public function tearDown()
    {
        unset($this->Interaction);
        unset($this->Maker);
        parent::tearDown();
    }


    public function testBeforeValidate()
    {
         $dialogue = $this->Maker->getOneDialogue();
         
         $interaction = $this->Interaction->beforeValidate($dialogue['Dialogue']['interactions'][0]);

         $this->assertTrue(isset($interaction['model-version']));
         $this->assertTrue(isset($interaction['object-type']));
    }


    public function testBeforeValidate_openQuestion()
    {
        $interaction = $this->Maker->getInteractionOpenQuestion();
        
        $interaction = $this->Interaction->beforeValidate($interaction);

        $this->assertEqual($interaction['activated'], 0);
        $this->assertEqual($interaction['model-version'], "2");
        $this->assertTrue(isset($interaction['reminder-actions'][0]['model-version']));
    }


    public function testBeforeValidate_closedQuestion() 
    {
        $interaction = $this->Maker->getInteractionClosedQuestion();
        
        $interaction = $this->Interaction->beforeValidate($interaction);

        $this->assertEqual($interaction['activated'], 0);
        $this->assertTrue(isset($interaction['answers'][0]['answer-actions'][0]['model-version']));
    }


    public function testBeforeValidate_multiKeywordQuestion()
    {
        $interaction = $this->Maker->getInteractionMultiKeywordQuestion();
        
        $interaction = $this->Interaction->beforeValidate($interaction);

        $this->assertEqual($interaction['activated'], 0);
        $this->assertTrue(isset($interaction['answer-keywords'][1]['answer-actions'][0]['model-version']));
        $this->assertTrue(isset($interaction['reminder-actions'][0]['model-version']));
    }


    public function testValidate_keyword_ok()
    {
        $interaction = $this->Maker->getInteractionOpenQuestion();
        $interaction['keyword'] = "test, Name, 123, to4";        

        $interaction = $this->Interaction->beforeValidate($interaction);
        $this->assertTrue(isset($interaction));    
    }


    public function testValidate_openQuestion_keyword_fail()
    {
        $interaction = $this->Maker->getInteractionOpenQuestion();
        $interaction['keyword'] = "test, keyword 1, other";        

        try{
            $interaction = $this->Interaction->beforeValidate($interaction);
            $this->failed("This interaction should be rejected");
        } catch (FieldValueIncorrect $e) {
             $this->assertEqual($e->getMessage(), "The keyword/alias 'test, keyword 1, other' is not valid.");
        }        
    }


    public function testValidate_multiKeywordQuestion_keyword_fail()
    {
        $interaction = $this->Maker->getInteractionMultiKeywordQuestion();
        $interaction['answer-keywords'][0]['keyword'] = "test, keyword 1, other";        

        try{
            $interaction = $this->Interaction->beforeValidate($interaction);
            $this->failed("This interaction should be rejected");
        } catch (FieldValueIncorrect $e) {
             $this->assertEqual($e->getMessage(), "The keyword/alias 'test, keyword 1, other' is not valid.");
        }        
    }


}