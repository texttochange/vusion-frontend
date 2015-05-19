<?php
App::uses('Action', 'Model');
App::uses('ProgramSpecificMongoModel', 'Model');

class ActionTestCase extends CakeTestCase
{
    public function setUp()
    {
        parent::setUp();
        $dbName = 'testdbprogram';
        $this->ContentVariableTable = ProgramSpecificMongoModel::init(
            'ContentVariableTable', $dbName);
        $this->ContentVariable = ProgramSpecificMongoModel::init(
            'ContentVariable', $dbName);
        $this->Action = new Action($dbName);
    }
    
    
    public function tearDown()
    {
        unset($this->Action);
        $this->ContentVariableTable->deleteAll(true, false);
        $this->ContentVariable->deleteAll(true, false);
    }
    
    
    public function testValidateAction_fail_feedback_contentNotAllow()
    {
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
    
    
    public function testValidateAction_fail_feedback_fieldMissing()
    {
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
    
    
    public function testValidateAction_ok_feedback_dynamic_content()
    {
        $action = array(
            'type-action' => 'feedback',
            'content' => 'Hello [contentVariable.mombasa.chicken.price]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertTrue($this->Action->validates());
    }
    
    
    public function testValidateAction_fail_feedback_dynamic_content()
    {
        $action = array(
            'type-action' => 'feedback',
            'content' => 'Hello [shoe.box]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            "To be used as customized content, 'shoe' can only be either: participant, contentVariable, time or context.",
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
    
    
    public function testValidateAction_fail_delayedEnrolling()
    {
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
    
    
    public function testValidateAction_fail_delayedEnrolling_value()
    {
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
    
    
    public function testValidateAction_fail_other_action()
    {
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
    
    
    public function testValidateAction_fail_tagging()
    {
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
    
    
    public function testValidateAction_optin()
    {
        $action = array(
            'type-action' => 'optin');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertTrue($this->Action->validates());
    }
    
    
    public function testValidateAction_condition_fail_conditionOperator_value()
    {
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
    
    
    public function testValidateAction_condition_fail_conditionOperator_required()
    {
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
    
    
    public function testValidateAction_condition_fail_subcondition_value()
    {
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
    
    
    public function testValidateAction_fail_proportionalTagging()
    {
        $action = array(
            'type-action' => 'proportional-tagging',
            'set-only-optin-count' => 'something',
            'proportional-tags' => array(
                array(
                    'tag' => 'a tag$',
                    'weight' => '6.5')));
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            "The field set-only-optin-count doesn't have a valide value.",
            $this->Action->validationErrors['set-only-optin-count'][0]);
        $this->assertEqual(
            "Use only space, letters and numbers for tag, e.g 'group 1'.",
            $this->Action->validationErrors['proportional-tags'][0]['tag'][0]);
        $this->assertEqual(
            'The weight value can only be a integer.',
            $this->Action->validationErrors['proportional-tags'][0]['weight'][0]);
    }
    
    
    public function testValidateAction_fail_proportionalLabelling()
    {
        $action = array(
            'type-action' => 'proportional-labelling',
            'label-name' => '',
            'set-only-optin-count' => 'something',
            'proportional-labels' => array(
                array(
                    'label-value' => 'control`',
                    'weight' => '6.5')));
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            "Use only space, letters and numbers for the label name.",
            $this->Action->validationErrors['label-name'][0]);
        $this->assertEqual(
            "The field set-only-optin-count doesn't have a valide value.",
            $this->Action->validationErrors['set-only-optin-count'][0]);
        $this->assertEqual(
            "Use only DOT, space, letters and numbers for the label value.",
            $this->Action->validationErrors['proportional-labels'][0]['label-value'][0]);
        $this->assertEqual(
            'The weight value can only be a integer.',
            $this->Action->validationErrors['proportional-labels'][0]['weight'][0]);
    }
    
    
    public function testValidateAction_url_forwarding_ok()
    {
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
        
        $action = array(
            'type-action' => 'url-forwarding',
            'forward-url' => "http://partner.com/index.php?script='insert into [PROGRAM] value [MESSAGE]'");
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $result = $this->Action->validates();
        $this->assertTrue($result);
    }
    
    
    public function testValidateAction_url_forwarding_fail_format()
    {
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
    }
    
    
    public function testValidateAction_url_forwarding_fail_replace()
    {        
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
    
    
    public function testValidateAction_sms_forwarding_ok_dynamic_content()
    {
        $action = array(
            'type-action' => 'sms-forwarding',
            'forward-to'=>'my tag, mylabel:[participant.mylabel]',
            'forward-content' => 'Hello [participant.name]([participant.phone]) from 
            [participant.address] says [context.message] at [time.H]:[time.M]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertTrue($this->Action->validates());
    }
    
    
    public function testValidateAction_sms_forwarding_ok_missing_field()
    {
        $action = array(
            'type-action' => 'sms-forwarding',
            'forward-content' => 'Hello');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertTrue($this->Action->validates());
    }
    
    
    public function testValidateAction_sms_forwarding_fail_multi_selector()
    {
        $action = array(
            'type-action' => 'sms-forwarding',
            'forward-to'=>'mylabel:[contentVariable:something]',
            'forward-content' => 'Hello');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            "The selector mylabel:[contentVariable:something] should be a tags or a labels. For labels, their value could be matching the sender by using content variable notation.",
            $this->Action->validationErrors['forward-to'][0]);
    }
    
    
    public function testValidateAction_sms_forwarding_fail_wrong_customized_content()
    {
        $action = array(
            'type-action' => 'sms-forwarding',
            'forward-to'=>'my tag',
            'forward-content' => 'Hello [participant.name]([participant.phone]) from 
            [participant.address] says [context.message] at [times.H]');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            "To be used as customized content, 'times' can only be either: participant, contentVariable, time or context.",
            $this->Action->validationErrors['forward-content'][0]);
        $this->assertEqual(
            1, 
            count($this->Action->validationErrors['forward-content']));
    }
    
    
    public function testValidateAction_sms_forwarding_fail_wrong_fieldmissing()
    {
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
    
    
    public function testValidateAction_sms_forwarding_message_condition_ok()
    {
        $action = array(
            'type-action' => 'sms-forwarding',
            'forward-to'=>'my tag',
            'set-forward-message-condition' => 'forward-message-condition',
            'forward-message-condition-type' => 'phone-number',
            'forward-message-no-participant-error' => 'The phone number don\'t match any patient',
            'forward-content' => 'Hello');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertTrue($this->Action->validates());
    }
    
    
    public function testValidateAction_sms_forwarding_message_condition_fail()
    {
        $action = array(
            'type-action' => 'sms-forwarding',
            'forward-to'=>'my tag',
            'set-forward-message-condition' => 'forward-message-condition',
            'forward-message-condition-type' => 'tag',
            'forward-message-no-participant-error' => 'The phone number don\'t match any patient',
            'forward-content' => 'Hello');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            'The field value is not valid.',
            $this->Action->validationErrors['forward-message-condition-type'][0]);
    }
    
    
    public function testValidateAction_sms_forwarding_no_participant_feedback_fail()
    {
        $action = array(
            'type-action' => 'sms-forwarding',
            'forward-to'=>'my tag',
            'set-forward-message-condition' => 'forward-message-condition',
            'forward-message-condition-type' => 'phone-number',
            'forward-message-no-participant-feedback' => 'The phone number [contet.condition] don\'t match any patient',
            'forward-content' => 'Hello');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            'To be used as customized content, \'contet\' can only be either: participant, contentVariable, time or context.',
            $this->Action->validationErrors['forward-message-no-participant-feedback'][0]);
    }
    
    
    public function testValidateAction_sms_invite_ok_dynamic_content()
    {
        $action = array(
            'type-action' => 'sms-invite',
            'invite-content'=>'pliz join to have fun',
            'invitee-tag' => 'invited',
            'feedback-inviter' => 'participant already in program');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertTrue($this->Action->validates());
    }
    
    
    public function testValidateAction_sms_invite_no_invite_content_fail()
    {
        $action = array(
            'type-action' => 'sms-invite',
            'invitee-tag' => 'invited',
            'feedback-inviter' => 'participant already in program');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            'The type-action field with value sms-invite require the field invite-content.',
            $this->Action->validationErrors['type-action'][0]);
    }
    
    
    public function testValidateAction_sms_invite_no_invite_content_wrong_customized_content_fail()
    {
        $action = array(
            'type-action' => 'sms-invite',
            'invite-content'=>'[participant%.name] says pliz join to have fun',
            'invitee-tag' => 'invited',
            'feedback-inviter' => 'participant already in program');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            'To be used as customized content, \'participant%\' can only be composed of letter(s), digit(s) and/or space(s).',
            $this->Action->validationErrors['invite-content'][0]);
    }
    
    
    public function testValidateAction_sms_invite_no_feedback_already_optin_fail()
    {
        $action = array(
            'type-action' => 'sms-invite',
            'invite-content'=>'[participant.name] says pliz join to have fun',
            'invitee-tag' => 'invited');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            'The type-action field with value sms-invite require the field feedback-inviter.',
            $this->Action->validationErrors['type-action'][0]);
    }


    public function testValidateAction_fail_reset_keep_tags()
    {
        $action = array(
            'type-action' => 'reset',
            'keep-tags' => 'a tag$');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            "Only space letters and numbers separate by coma. Must be tag1, tag2, ... e.g cool, nice, ...",
            $this->Action->validationErrors['keep-tags'][0]);
        $this->assertEqual(
            1, count($this->Action->validationErrors['keep-tags']));
    }


    public function testValidateAction_fail_reset_keep_labels()
    {
        $action = array(
            'type-action' => 'reset',
            'keep-labels' => 'label"$');
        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $this->assertEqual(
            "Only space letters and numbers separate by coma. Must be label1, label2, ... e.g age, name, ...",
            $this->Action->validationErrors['keep-labels'][0]);
        $this->assertEqual(
            1, count($this->Action->validationErrors['keep-labels']));
    }


    public function testValidateAction_saveContentVariableTable_ok()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh', null)
                    ),
                )
            );
        $this->ContentVariableTable->create();
        $cvt = $this->ContentVariableTable->save($contentVariableTable);

        $action = array(
            'type-action' => 'save-content-variable-table',
            'scvt-attached-table' => $cvt['ContentVariableTable']['_id'],
            'scvt-row-keys' => array(array(
                'scvt-row-value' => '[participant.city')),
            'scvt-col-key-header' => 'Chicken price',
            'scvt-col-extras' => array(array(
                'scvt-col-extra-header' => 'phone',
                'scvt-col-extra-value' => '[participant.phone]')));

        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->Action->validates();
        $result = $this->Action->validates();
        $this->assertTrue($result);
    }


    public function testValidateAction_saveContentVariableTable_fail_noTable()
    {
        $action = array(
            'type-action' => 'save-content-variable-table',
            'scvt-attached-table' => '1',
            'scvt-row-keys' => array(array(
                'scvt-row-header' => 'Town',
                'scvt-row-value' => '[participant.city')),
            'scvt-col-key-header' => 'Chicken price',
            'scvt-col-extras' => array(array(
                'scvt-col-extra-header' => 'phone',
                'scvt-col-extra-value' => '[participant.phone]')));

        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            "Must reference an existing table id.",
            $this->Action->validationErrors['scvt-attached-table'][0]);
        $this->assertEqual(
            1, count($this->Action->validationErrors['scvt-attached-table']));
    }


    public function testValidateAction_saveContentVariableTable_fail_noHeader()
    {
        $contentVariableTable = array(
            'name' => 'my table',
            'columns' => array(
                array(
                    'header' => 'Town',
                    'values' => array('mombasa', 'nairobi')
                    ),
                array(
                    'header' => 'Chicken price',
                    'values' => array('300 Ksh', null)
                    ),
                )
            );
        $this->ContentVariableTable->create();
        $cvt = $this->ContentVariableTable->save($contentVariableTable);

        $action = array(
            'type-action' => 'save-content-variable-table',
            'scvt-attached-table' =>  $cvt['ContentVariableTable']['_id']."",
            'scvt-row-keys' => array(array(
                'scvt-row-header' => 'Town',
                'scvt-row-value' => '[participant.city]')),
            'scvt-col-key-header' => 'fish.price',
            'scvt-col-extras' => array(
                array(
                    'scvt-col-extra-header' => 'phone.',
                    'scvt-col-extra-value' => '[participant.phone]'),
                array(
                    'scvt-col-extra-header' => 'Town',
                    'scvt-col-extra-value' => '[participant.city]')));

        $this->Action->set($action);
        $this->Action->beforeValidate();
        $this->assertFalse($this->Action->validates());
        $this->assertEqual(
            "Use only space, letters and numbers for a key, e.g 'uganda 1'.",
            $this->Action->validationErrors['scvt-col-key-header'][0]);
        $this->assertEqual(
            "Use only space, letters and numbers for a key, e.g 'uganda 1'.",
            $this->Action->validationErrors['scvt-col-extras'][0]['scvt-col-extra-header'][0]);
        $this->assertEqual(
            "The header cannot be a key in the table.",
            $this->Action->validationErrors['scvt-col-extras'][1]['scvt-col-extra-header'][0]);
        $this->assertEqual(
            2, count($this->Action->validationErrors));
    }

}
