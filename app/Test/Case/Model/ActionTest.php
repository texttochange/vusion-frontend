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
        $this->Action->validates();
        $this->assertEqual(
            'The apostrophe used is not allowed.',
            $this->Action->validationErrors[0]);
        $this->assertEqual(
            1, count($this->Action->validationErrors[0]));
    }


    public function testValidateAction_fail_feedback_fieldMissing() {
        $action = array(
            'type-action' => 'feedback');
        $this->Action->set($action);
        $this->Action->validates();
        $this->assertEqual(
            'The field content is missing.',
            $this->Action->validationErrors[0]);
        $this->assertEqual(
            1, count($this->Action->validationErrors[0]));
    }

    
    public function testValidateAction_fail_delayedEnrolling() {
        $action = array(
            'type-action' => 'delayed-enrolling',
            'enroll' => '233445',
            'offset-days' => array());
        $this->Action->set($action);
        $this->Action->validates();
        $this->assertEqual(
            'The days and time has to be set.',
            $this->Action->validationErrors[0]);
        $this->assertEqual(
            1, count($this->Action->validationErrors[0]));
    }


    public function testValidateAction_fail_delayedEnrolling_value() {
        $action = array(
            'type-action' => 'delayed-enrolling',
            'enroll' => '233445',
            'offset-days' => array(
                'days' => '0',
                'at-time' => '10:10'));
        $this->Action->set($action);
        $this->Action->validates();
        $this->assertEqual(
            'The offset days is not valid.',
            $this->Action->validationErrors[0]);
        $this->assertEqual(
            1, count($this->Action->validationErrors[0]));
    }


    public function testValidateAction_fail_other_action() {
        $action = array(
            'type-action' => 'some-new-action');
        $this->Action->set($action);
        $this->Action->validates();
        $this->assertEqual(
            'The action some-new-action is not supported.',
            $this->Action->validationErrors[0]
            );
        $this->assertEqual(
            1,
            count($this->Action->validationErrors[0]));
    }


    public function testValidateAction_fail_tagging() {
        $action = array(
            'type-action' => 'tagging',
            'tag' => 'a tag$');
        $this->Action->set($action);
        $this->Action->validates();
        $this->assertEqual(
            'Only letters and numbers. Must be tag, tag, ... e.g cool, nice, ...',
            $this->Action->validationErrors[0]);
        $this->assertEqual(
            1, count($this->Action->validationErrors[0]));
    }


    public function testValidateAction_optin() {
        $action = array(
            'type-action' => 'optin');
        $this->Action->set($action);
        $this->assertTrue($this->Action->validates());
    }


} 