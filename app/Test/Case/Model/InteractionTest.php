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


}