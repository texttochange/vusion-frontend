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
         $interaction = $dialogue['Dialogue']['interactions'][0];

         $this->Interaction->set($interaction);
         $this->Interaction->beforeValidate();
         $interaction = $this->Interaction->getCurrent();
         
         $this->assertTrue(isset($interaction['model-version']));
         $this->assertTrue(isset($interaction['object-type']));
    }


    public function testBeforeValidate_openQuestion()
    {
        $interaction = $this->Maker->getInteractionOpenQuestion();
  
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $interaction = $this->Interaction->getCurrent();

        $this->assertEqual($interaction['activated'], 0);
        $this->assertEqual($interaction['model-version'], '3');
        $this->assertTrue(isset($interaction['reminder-actions'][0]['model-version']));
    }


    public function testBeforeValidate_closedQuestion() 
    {
        $interaction = $this->Maker->getInteractionClosedQuestion();
        
        $this->Interaction->set($interaction);
        $this->assertTrue($this->Interaction->beforeValidate());
        $interaction = $this->Interaction->getCurrent();
        $this->assertEqual($interaction['activated'], 0);
        $this->assertTrue(isset($interaction['answers'][0]['answer-actions'][0]['model-version']));
    }


    public function testBeforeValidate_multiKeywordQuestion()
    {
        $interaction = $this->Maker->getInteractionMultiKeywordQuestion();
        
        $this->Interaction->set($interaction);
        $this->assertTrue($this->Interaction->beforeValidate());
        $interaction = $this->Interaction->getCurrent();
        
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

        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertFalse($this->Interaction->validates());
        $this->assertEqual(
            $this->Interaction->validationErrors['keyword'][0],        
            "The keyword/alias is(are) not valid.");
    }


    public function testValidate_multiKeywordQuestion_keyword_fail()
    {
        $interaction = $this->Maker->getInteractionMultiKeywordQuestion();
        $interaction['answer-keywords'][0]['keyword'] = "test, keyword 1, other";        

        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertFalse($this->Interaction->validates());
        $this->assertEqual(
            $this->Interaction->validationErrors['keyword'][0],        
            "The keyword/alias is(are) not valid.");
                
    }


    public function testValidate_fail()
    {
         $interaction = array(
                    'type-interaction' => 'annoucement', 
                    'content' => 'hello',
                    'keyword' => 'feel',
                    'interaction-id' => '1');
         $this->Interaction->set($interaction);
         $this->assertFalse($this->Interaction->validates());

         $this->assertEqual(
             $this->Interaction->validationErrors['type-interaction'][0], 
             'Type Interaction value is not valid.'
             );
         $this->assertEqual(
             $this->Interaction->validationErrors['activated'][0], 
             'Activated field is missing.'
             );
    }


    public function testValidate_fail_requiredConditionalFieldValue()
    {
         $interaction = array(
                    'type-interaction' => 'announcement', 
                    'type-schedule' => 'fixed-time',
                    'content' => 'hello',
                    'keyword' => 'feel',
                    'interaction-id' => '1',
                    'activated' => 0,
                    'prioritized' => 'prioritized');
         $this->Interaction->set($interaction);
         $this->Interaction->beforeValidate();
         $this->assertFalse($this->Interaction->validates());

         $this->assertEqual(
             $this->Interaction->validationErrors['type-schedule'][0], 
             'The type-schedule field with value fixed-time require the field date-time.'
             );
    }

}