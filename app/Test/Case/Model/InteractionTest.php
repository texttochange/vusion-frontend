<?php
App::uses('Interaction', 'Model');
App::uses('ScriptMaker', 'Lib');


class InteractionTestCase extends CakeTestCase
{


    public function setUp()
    {
        parent::setUp();
        $this->Interaction = new Interaction('testdbprogram');
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
        $this->assertEqual($interaction['model-version'], '5');
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
    

    public function testValidate_offsetConditionDelay_fail()
    {
        $interaction = $this->Maker->getInteractionOpenQuestion();
        $interaction['type-schedule'] = 'offset-condition';
        $interaction['offset-condition-interaction-id'] = 'local:20';
        $interaction['offset-condition-delay'] = 'a string'; 

        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertFalse($this->Interaction->validates());
        $this->assertEqual(
            $this->Interaction->validationErrors['offset-condition-delay'][0], 
            'The delay only accept full minutes.');

    }


    public function testValidate_keyword_ok()
    {
        $interaction = $this->Maker->getInteractionOpenQuestion();
        $interaction['keyword'] = "test, Name, 123, to4"; 

        $interaction = $this->Interaction->beforeValidate($interaction);
        $this->assertTrue(isset($interaction));    
    }
    
    
    public function testValidate_content_dynamicContent_ok()
    {
        $interaction = $this->Maker->getInteractionOpenQuestion();
        $interaction['content'] = "Hello [participant.first name] the temperature in [contentVariable.mombasa] is [contentVariable.mombasa.temperature.night]";        
        
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertTrue($this->Interaction->validates());
    }
    
    
    public function testValidate_content_dynamicContent_fail()
    {
        $interaction = $this->Maker->getInteractionOpenQuestion();
        $interaction['content'] = "Hello [participant.first %name]";        
        
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertFalse($this->Interaction->validates());
        
        $this->assertEqual(
            $this->Interaction->validationErrors['content'][0], 
            "To be used as customized content, 'first %name' can only be composed of letter(s), digit(s) and/or space(s)."
            );
        
        $interaction = $this->Maker->getInteractionOpenQuestion();
        $interaction['content'] = "Hello [participant.gender.name]";        
        
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertFalse($this->Interaction->validates());
        
        $this->assertEqual(
            $this->Interaction->validationErrors['content'][0], 
            "To be used in message, participant only accepts one key."
            );
        
        $interaction = $this->Maker->getInteractionOpenQuestion();
        $interaction['content'] = "Hello [contentVariable.mombasa.chichen.female.price]";        
        
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertFalse($this->Interaction->validates());
        
        $this->assertEqual(
            $this->Interaction->validationErrors['content'][0], 
            "To be used in message, contentVariable only accepts maximum three keys."
            );
        
        $interaction = $this->Maker->getInteractionOpenQuestion();
        $interaction['content'] = "Hello [participants.name]";        
        
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertFalse($this->Interaction->validates());
        
        $this->assertEqual(
            $this->Interaction->validationErrors['content'][0], 
            "To be used as customized content, 'participants' can only be either: participant, contentVariable or time."
            );
        
        ## test feedback action
        $interaction = $this->Maker->getInteractionOpenQuestion();
        $interaction['reminder-actions'] = array(
            array('type-action' => 'feedback',
                'content' => "Hello [person.name]"));        
        
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertFalse($this->Interaction->validates());
        
        $this->assertEqual(
            $this->Interaction->validationErrors['reminder-actions'][0]['content'][0], 
            "To be used as customized content, 'person' can only be either: participant, contentVariable, time or context.");
    }
    
    
    public function testValidate_openQuestion_keyword_failRegularExp()
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
    
    
    public function testValidate_multiKeywordQuestion_keyword_failRegularExp()
    {
        $interaction = $this->Maker->getInteractionMultiKeywordQuestion();
        $interaction['answer-keywords'][0]['keyword'] = "test, keyword 1, other";        
        
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertFalse($this->Interaction->validates());
        $this->assertEqual(
            $this->Interaction->validationErrors['answer-keywords'][0]['keyword'][0],        
            "The keyword/alias is(are) not valid.");        
    }


    public function testValidate_openQuestion_keyword_fail_alreadyUsed()
    {
        $interaction = $this->Maker->getInteractionClosedQuestion();
        $interaction['set-answer-accept-no-space'] = "answer-accept-no-space";
        $interaction['keyword'] = "k1, k2";        
        $alreadyUsedKeywords = array(
            'k2bad' => array(
                'program-db' => 'otherdb',
                'program-name' => 'otherprogram',
                'by-type' => 'Dialogue',
                'dialogue-id' => '01',
                'dialogue-name' => 'my dialogue'));
        
        $this->Interaction->setUsedKeywords($alreadyUsedKeywords);
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertFalse($this->Interaction->validates());
        $this->assertEqual(
            $this->Interaction->validationErrors['keyword'][0],        
            "'k2bad' already used by a Dialogue of program 'otherprogram'.");
    }


    public function testValidate_questionanwer_keyword_nospace_fail_alreadyUsed()
    {
        $interaction = $this->Maker->getInteractionMultiKeywordQuestion();
        $interaction['answer-keywords'][0]['keyword'] = "test, other";        
        $alreadyUsedKeywords = array(
            'other' => array(
                'program-db' => 'otherdb',
                'program-name' => 'otherprogram',
                'by-type' => 'Dialogue',
                'dialogue-id' => '01',
                'dialogue-name' => 'my dialogue'));

        $this->Interaction->setUsedKeywords($alreadyUsedKeywords);
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertFalse($this->Interaction->validates());
        $this->assertEqual(
            $this->Interaction->validationErrors['answer-keywords'][0]['keyword'][0],        
            "'other' already used by a Dialogue of program 'otherprogram'.");
    }
    
    
    
    public function testValidate_multiKeywordQuestion_keyword_fail_alreadyUsed()
    {
        $interaction = $this->Maker->getInteractionMultiKeywordQuestion();
        $interaction['answer-keywords'][0]['keyword'] = "test, other";        
        $alreadyUsedKeywords = array(
            'other' => array(
                'program-db' => 'otherdb',
                'program-name' => 'otherprogram',
                'by-type' => 'Dialogue',
                'dialogue-id' => '01',
                'dialogue-name' => 'my dialogue'));

        $this->Interaction->setUsedKeywords($alreadyUsedKeywords);
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertFalse($this->Interaction->validates());
        $this->assertEqual(
            $this->Interaction->validationErrors['answer-keywords'][0]['keyword'][0],        
            "'other' already used by a Dialogue of program 'otherprogram'.");
    }

    
    public function testValidate_fail()
    {
        $interaction = array(
            'type-interaction' => 'annoucement', 
            'content' => 'hello',
            'keyword' => 'feel',
            'interaction-id' => '1');
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        $this->assertFalse($this->Interaction->validates());
        
        $this->assertEqual(
            $this->Interaction->validationErrors['type-schedule'][0], 
            'Type Schedule field cannot be empty.'
            );
        $this->assertEqual(
            $this->Interaction->validationErrors['type-interaction'][0], 
            'Type Interaction value is not valid.'
            );
    }


    public function testValidate_date_fail_format()
    {
        $interaction = $this->Maker->getInteractionClosedQuestion();
        $interaction['date-time'] = '2013-10-20 20:20:00';
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        
        $this->assertFalse($this->Interaction->validates());
        $this->assertEqual(
            $this->Interaction->validationErrors['date-time'][0], 
            'The date time is not in an ISO format.');
    }


    public function testValidate_date_fail_empty()
    {
        $interaction = $this->Maker->getInteractionClosedQuestion();
        $interaction['date-time'] = null;
        $this->Interaction->set($interaction);
        $this->Interaction->beforeValidate();
        
        $this->assertFalse($this->Interaction->validates());
        $this->assertEqual(
            $this->Interaction->validationErrors['date-time'][0], 
            'The date time has to be set.');
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


    public function testGetInteractionKeywords()
    {
        $interaction = $this->Maker->getInteractionOpenQuestion();
        $this->assertEqual(
            array('name'),
            Interaction::getInteractionKeywords($interaction));

        $interaction = $this->Maker->getInteractionClosedQuestion();
        $this->assertEqual(
            array('feel'),
            Interaction::getInteractionKeywords($interaction));

        $interaction['set-answer-accept-no-space'] = 'answer-accept-no-space';
        $this->assertEqual(
            array('feel', 'feelfine', 'feelbad'),
            Interaction::getInteractionKeywords($interaction));

        
        $interaction = $this->Maker->getInteractionMultiKeywordQuestion();
        $this->assertEqual(
            array('female', 'male'),
            Interaction::getInteractionKeywords($interaction));
    }

  
}