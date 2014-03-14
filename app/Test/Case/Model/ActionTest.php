<?php
App::uses('Action', 'Model');


class ActionTestCase extends CakeTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->Action = new Action();
    }

    public function tearDown()
    {
        unset($this->Action);
    }


    public function testValidateAction_fail_feedback_contentNotAllow() {
        $action = array(
            'type-action' => 'feedback',
            'content' => 'What’up');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            'The apostrophe used is not allowed.',
            $this->Action->validationErrors['content'][0]);
        $this->assertEqual(
            1, 
            count($this->Action->validationErrors['content']));
    }


    public function testValidateAction_fail_feedback_fieldMissing() {
        $action = array(
            'type-action' => 'feedback');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            'The type-action field with value feedback require the field content.',
            $this->Action->validationErrors['type-action'][0]);
        $this->assertEqual(
            1, 
            count($this->Action->validationErrors['type-action']));
    }
    
    
    public function testValidateAction_ok_feedback_dynamic_content() {
        $action = array(
            'type-action' => 'feedback',
            'content' => 'Hello [contentVariable.mombasa.chicken.price]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertTrue($this->Action->validates());
    }

    public function testValidateAction_fail_feedback_dynamic_content() {
        $action = array(
            'type-action' => 'feedback',
            'content' => 'Hello [shoe.box]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            "To be used as customized content, 'shoe' can only be either 'participant', 'contentVariable', 'context' or 'time'.",
            $this->Action->validationErrors['content'][0]);
        $this->assertEqual(
            1, 
            count($this->Action->validationErrors['content']));
        
        $action['content'] = 'hello [participant.$%name]';
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            "To be used as customized content, '$%name' can only be composed of letter(s), digit(s) and/or space(s).",
            $this->Action->validationErrors['content'][0]);
        $this->assertEqual(
            1, 
            count($this->Action->validationErrors['content']));
        
        $action['content'] = 'hello [contentVariable.name.age.person.gender]';
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            "To be used in message, contentVariable only accepts maximum three keys.",
            $this->Action->validationErrors['content'][0]);
        $this->assertEqual(
            1, 
            count($this->Action->validationErrors['content']));
    }

   
    public function testValidateAction_fail_delayedEnrolling() {
        $action = array(
            'type-action' => 'delayed-enrolling',
            'enroll' => '233445',
            'offset-days' => array());
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            'The days field is required.',
            $this->Action->validationErrors['offset-days']['days'][0]);
        $this->assertEqual(
            'The at-time field is required.',
            $this->Action->validationErrors['offset-days']['at-time'][0]);
        $this->assertEqual(
            2,
            count($this->Action->validationErrors['offset-days']));
    }


    public function testValidateAction_fail_delayedEnrolling_value() {
        $action = array(
            'type-action' => 'delayed-enrolling',
            'enroll' => '233445',
            'offset-days' => array(
                'days' => '0',
                'at-time' => '10:10'));
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            'Offset days has to be greater or equal to 1.',
            $this->Action->validationErrors['offset-days']['days'][0]);
        $this->assertEqual(
            1, 
            count($this->Action->validationErrors['offset-days']));
    }


    public function testValidateAction_fail_other_action() {
        $action = array(
            'type-action' => 'some-new-action');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            'The type-action value is not valid.',
            $this->Action->validationErrors['type-action'][0]
            );
        $this->assertEqual(
            1,
            count($this->Action->validationErrors));
    }


    public function testValidateAction_fail_tagging() {
        $action = array(
            'type-action' => 'tagging',
            'tag' => 'a tag$');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            "Use only space, letters and numbers for tag, e.g 'group 1'.",
            $this->Action->validationErrors['tag'][0]);
        $this->assertEqual(
            1, count($this->Action->validationErrors['tag']));
    }


    public function testValidateAction_optin() {
        $action = array(
            'type-action' => 'optin');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertTrue($this->Action->validates());
    }


    public function testValidateAction_condition_fail_conditionOperator_value() {
        $action = array(
            'type-action' => 'optin',
            'set-condition' => 'condition',
            'condition-operator' => 'somethingwrong',
            'subconditions' => array());
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            'The condition-operator value is not valid.',
            $this->Action->validationErrors['condition-operator'][0]);
    }


    public function testValidateAction_condition_fail_conditionOperator_required() {
        $action = array(
            'type-action' => 'optin',
            'set-condition' => 'condition');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            'The condition-operator value is not valid.',
            $this->Action->validationErrors['condition-operator'][0]);
        $this->assertEqual(
            'At least one subconditions has to be set.',
            $this->Action->validationErrors['subconditions'][0]);
    }


    public function testValidateAction_condition_fail_subcondition_value() {
        $action = array(
            'type-action' => 'optin',
            'set-condition' => 'condition',
            'condition-operator' => 'all-subconditions',
            'subconditions' => array(
                array('subcondition-field' => 'tagged',
                    'subcondition-operator' => 'with',
                    'subcondition-parameter' => 'a bad tag,')));
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            "Use only space, letters and numbers for tag, e.g 'group 1'.",
            $this->Action->validationErrors['subconditions'][0]['subcondition-parameter'][0]);
    }


    public function testValidateAction_fail_proportionalTagging() {
        $action = array(
            'type-action' => 'proportional-tagging',
            'proportional-tags' => array(
                array(
                    'tag' => 'a tag$',
                    'weight' => '6.5')));
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            "Use only space, letters and numbers for tag, e.g 'group 1'.",
            $this->Action->validationErrors['proportional-tags'][0]['tag'][0]);
        $this->assertEqual(
            'The weight value can only be a integer.',
            $this->Action->validationErrors['proportional-tags'][0]['weight'][0]);
    }


    public function testValidateAction_ok_forwarding() {
        $action = array(
            'type-action' => 'url-forwarding',
            'forward-url' => 'http://partner.com/receive_mo.php');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertTrue($this->Action->validates());
        
        $action = array(
            'type-action' => 'url-forwarding',
            'forward-url' => 'http://partner.com/receive_mo.php?message=[MESSAGE]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertTrue($this->Action->validates());
        
        $action = array(
            'type-action' => 'url-forwarding',
            'forward-url' => 'http://partner.com/receive_mo.php?message=[MESSAGE]&origin=[FROM]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertTrue($this->Action->validates());

         $action = array(
            'type-action' => 'url-forwarding',
            'forward-url' => 'http://partner.com/index.php?login=login&message=[MESSAGE]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertTrue($this->Action->validates());

        $action = array(
            'type-action' => 'url-forwarding',
            'forward-url' => 'http://partner.com/index.php?login=login&password=password&message=[MESSAGE]&other=other');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertTrue($this->Action->validates());
    }

    
    public function testValidateAction_fail_forwarding_format() {
        $action = array(
            'type-action' => 'url-forwarding',
            'forward-url' => 'partner.com/receive_mo.php');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            'The forward url is not valid.',
            $this->Action->validationErrors['forward-url'][0]);
        
        $action = array(
            'type-action' => 'url-forwarding',
            'forward-url' => 'http://partner.com/receive_mo.php?message=[MESSAGE]?origin=[TO]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            'The forward url is not valid.',
            $this->Action->validationErrors['forward-url'][0]);
        
        $action = array(
            'type-action' => 'url-forwarding',
            'forward-url' => 'http://partner.com/receive_mo.php?message=[MESSAGE]&origin=[TO[]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            'The forward url is not valid.',
            $this->Action->validationErrors['forward-url'][0]);
    }


    public function testValidateAction_fail_forwarding_replace() {        
        $action = array(
            'type-action' => 'url-forwarding',
            'forward-url' => 'http://partner.com/receive_mo.php?message=[Message]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            'The replacement [Message] is not allowed.',
            $this->Action->validationErrors['forward-url'][0]);
        
        $action = array(
            'type-action' => 'url-forwarding',
            'forward-url' => 'http://partner.com/receive_mo.php?message=[content]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            'The replacement [content] is not allowed.',
            $this->Action->validationErrors['forward-url'][0]);
    }
    
    
    public function testValidateAction_ok_sms_forwarding_dynamic_content() {
        $action = array(
            'type-action' => 'sms-forwarding',
            'forward-to'=>'my tag',
            'forward-content' => 'Hello [participant.name]([participant.phone]) from 
                                  [participant.address] says [context.message] at [time.H]:[time.M]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertTrue($this->Action->validates());
    }

    
    public function testValidateAction_fail_sms_forwarding_wrong_customized_content() {
        $action = array(
            'type-action' => 'sms-forwarding',
            'forward-to'=>'my tag',
            'forward-content' => 'Hello [participant.name]([participant.phone]) from 
                                  [participant.address] says [context.message] at [times.H]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            'To be used as customized content, \'times\' can only be either \'participant\', \'contentVariable\', \'context\' or \'time\'.',
            $this->Action->validationErrors['forward-content'][0]);
        $this->assertEqual(
            1, 
            count($this->Action->validationErrors['forward-content']));
    }
    
    
    public function testValidateAction_fail_sms_forwarding_wrong_fieldmissing() {
        $action = array(
            'type-action' => 'sms-forwarding',
            'forward-to'=>'my tag',
            'forward-content' => 'Hello [participant%.name]([participant.phone]) from 
                                  [participant.address] says [context.message] at [time.H]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            'To be used as customized content, \'participant%\' can only be composed of letter(s), digit(s) and/or space(s).',
            $this->Action->validationErrors['forward-content'][0]);
        $this->assertEqual(
            1, 
            count($this->Action->validationErrors['forward-content']));
    }
        
} 