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
            "The parameter value 'a bad tag,' is not valid.",
            $this->Action->validationErrors['subconditions'][0]['subcondition-parameter'][0]);
    }

} 