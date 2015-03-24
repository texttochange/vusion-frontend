var dynamicForm = {};

var dialogue = {
    "Dialogue":{
        'type': 'container',
        'contains': [
            "name",  
            "set-prioritized", 
            "auto-enrollment-box", 
            "interactions",
            "dialogue-id", 
            "activated"],
        'skip': true,
    },
    'name': {
        'type': 'text',
    },
    "dialogue-id":{ 
        'type': "hidden",
    },
    "auto-enrollment-box": {
        'type': 'container',
        'class': 'ttc-foldable',
        'contains': ['auto-enrollment'],
        'skip': false,
        'item': "auto-enrollment-box",
    },
    "auto-enrollment":{ 
        'type': "spanradiobuttons",
        'options':  [
            {'value': 'none'},
            {'value': 'all'},
            {'value': 'match',
            'subfields': [ 
                "condition-operator", 
                "subconditions"]}],
    }, 
    "set-prioritized": {
        'type': "checkboxes",
        'value': 'prioritized'
    },
    "interactions": {
        'type': 'list',
        'add-button': true,
        'adds' : "interaction",
    },
    "interaction": {
        'type': 'container',
        'contains': [
            "type-schedule", 
            "type-interaction",
            "interaction-id", 
            "activated"],
        'skip': true,
    },
    "interaction-id":{
        'type': "hidden",
        'default-value': (function() {return 'local:' + ($("[name*='.interaction-id']").length + 1)})
    },
    "activated": {
        'type': "hidden",
    },
    "type-schedule": {
        "type": "spanradiobuttons",
        "options": [
            {"value": "fixed-time",
            "subfields":["date-time"]},
            {"value": "offset-time",
            "subfields":["minutes"]},
            {'value': "offset-days",
            'subfields': [
                "days",
                "at-time"]},
            {'value': "offset-condition",
            'subfields': [
                "offset-condition-interaction-id",
                "offset-condition-delay"]}]},
    "type-interaction": {
        "type": "spanradiobuttons",
        "options": [
            {"value": "announcement",
            "subfields": [
                "content",
                "announcement-actions"]},
            {"value": "question-answer",
            'subfields': [
                "content",
                "keyword", 
                "set-use-template", 
                "type-question",
                "set-matching-answer-actions",
                "set-max-unmatching-answers", 
                "type-unmatching-feedback",
                "set-reminder"]},
            {"value": "question-answer-keyword",
            "subfields": [
                "content", 
                "label-for-participant-profiling", 
                "answer-keywords", 
                "set-reminder"]}]
    },
    "type-unmatching-feedback" : {
        'type': "spanradiobuttons",
        'options': [
            {"value": "no-unmatching-feedback"}, 
            {'value': "program-unmatching-feedback"},
            {'value': "interaction-unmatching-feedback",
            'subfields': ['unmatching-feedback-content']}],
    },
    "unmatching-feedback-content": {
        'type': "textarea"
    },
    "set-use-template":{
        'type': "checkboxes",
        'value': 'use-template'
    },
    "set-matching-answer-actions": {
        "type": "spancheckboxes",
        "value": "matching-answer-actions",
        "subfields": [
            "matching-answer-actions"
        ]
    },
    "matching-answer-actions": {
        "type": "list",
        "add-button": true,
        "adds": "action"
    },
    "set-max-unmatching-answers": {
        'type': "spancheckboxes",
        "value": "max-unmatching-answers",
        "subfields": [
            "max-unmatching-answer-number",
            "max-unmatching-answer-actions"]
    },
    "max-unmatching-answer-number":{
        'type': "text",
    },
    "max-unmatching-answer-actions": {
        'type': 'list',
        'add-button': true,
        'adds': "action"
    },
    "type-question":{
        'type': "spanradiobuttons",
        "options": [
            {"value": "closed-question",
             "subfields": [
                 "label-for-participant-profiling", 
                 "set-answer-accept-no-space", 
                 "answers"]},
            {"value": 'open-question',
             "subfields": [
                 "answer-label", 
                 "feedbacks"]}]
    },
    "set-answer-accept-no-space": { 
        "type": "checkboxes",
        "value": "answer-accept-no-space"
    },
    "label-for-participant-profiling": {"type": "text"},
    "answer-label": { "type": "text"},
    "answers": { 
        "type": 'list',
        "add-button": true,
        "adds": "answer"
    },
    "answer": {
        'type': 'container',
        'contains': ["choice", "feedbacks", "answer-actions"],
        'skip': true
    },
    "feedbacks": {
        "type": "list",
        "add-button": true,
        "adds": "feedback",
    },
    "feedback": {
        "type": 'container',
        "contains": ["content"],
        'skip': true
    },
    "answer-actions": {
        "type": "list",
        "add-button": true,
        "adds": "action"
    },
    /*
    "announcement-actions-container": {
        'type': 'container',
        'contains': ['announcement-actions'],
        'skip': true,
    },*/
    "announcement-actions": {
        "type": "list",
        "add-button": true,
        "adds": "action"
    },
    "choice": {
        "type": "text",
    },
    "answer-keywords": {
        'type': 'list',
        'add-button': true,
        'adds': "answer-keyword",
    },
    "answer-keyword": {
        'type': 'container',
        'contains': ["keyword","feedbacks", "answer-actions"],
        'skip': true,
    },
    "offset-condition-interaction-id": {
        'type': 'select',
        'data': 'server-dynamic',
        'fieldset': true,
        'style': 'margin-left:0px',
        'onmouseover': 'updateOffsetConditions(this)',
    },
    "offset-condition-delay": {
        "type": 'text',
    },
    "keyword": {"type": "text"},
};
$.extend(dynamicForm, dialogue);

var request = {
    "Request": {
        'type': 'container',
        'contains': [
            "keyword", 
            "set-no-request-matching-try-keyword-only", 
            "responses", 
            "actions"],
    },
    "set-no-request-matching-try-keyword-only": { 
        'type': "checkboxes",
        'value': "no-request-matching-try-keyword-only"
    },
    "responses": {
        'type': 'list',
        'add-button': true,
        'adds': "response",
    },
    "actions": { 
        'type': 'list',
        'add-button': true,
        'adds': "action"
    },
    "response":{ 
        "type": "container",
        "contains": ["content"],
        "skip": true
    },
}
$.extend(dynamicForm, request);

var basic = {
    "content": {'type': "textarea",},
    "date": {'type': 'date'},
    "date-time": { 'type': "text"},
    "days": {"type": "text"},
    "minutes": {'type': "text"},
    "at-time": {'type': "text"},
    "time": {'type': "text"},
}
$.extend(dynamicForm, basic);

var reminder = {
    "set-reminder": {
        'type': 'spancheckboxes',
        'value': 'reminder',
        'subfields': [
            "reminder-number",
            "type-schedule-reminder",
            "reminder-actions"]
    },
    "reminder-number": {
        'type': 'text',
    },
    "type-schedule-reminder": {
        'type': "spanradiobuttons",
        'options': [
            {'value': "reminder-offset-time",
            'subfields': ['reminder-minutes']},
            {'value': "reminder-offset-days",
            'subfields': ["reminder-days", "reminder-at-time"]}],
    },
    "reminder-actions": {
        'type': 'list',
        'add-button': true,
        'adds': "action",
    },
    "reminder-days": {"type": "text"},
    "reminder-minutes": {'type': "text"},
    "reminder-at-time": {"type": "text"},
}
$.extend(dynamicForm, reminder);

var action = {
    "action": { 
        'type': 'container',
        'contains': ["set-condition", "type-action"],
        'skip': true,
    },
    "set-condition": {
        'type': 'spancheckboxes',
        'value': 'condition',
        'subfields': [
            "condition-operator", 
            "subconditions"],
    },
    "condition-operator": { 
        'type': 'spanradiobuttons',
        'options': [
            {'value': "all-subconditions"},
            {'value': "any-subconditions"}],
        'style': 'padding-top:0px'
    },
    'subconditions': {
        'type': 'list',
        'add-button': true,
        'adds': 'subcondition'
    },
    "subcondition": {
        'type': 'container', 
        'contains': ["subcondition-field", "subcondition-operator", "subcondition-parameter"],
        'skip': true,
    },
    "subcondition-field": {
        'type': 'select',
        'data': 'server-dynamic',
        'onchange': 'supplySubconditionOperatorOptions(this)',
        'onmouseover': 'supplySubconditionOperatorOptions(this)',
        'fieldset': false,
    },
    "subcondition-operator": {
        'type': 'select',
        'data': 'server-dynamic',
        'onmouseover': 'supplySubconditionOperatorOptions(this)',
        'fieldset': false,
    },
    "subcondition-parameter": {
        'type': 'text',
        'style': 'width:200px'
    },
    "type-action": {
        'type': 'spanradiobuttons',
        'options': [
            {'value': "optin"},
            {'value': "optout"}, 
            {'value': "enrolling",
            'subfields': ['enroll']},
            {'value': "delayed-enrolling",
            'subfields': ['enroll', 'offset-days']}, 
            {'value': "tagging",
            'subfields': ['tag']},
            {'value': "reset",
            'subfields': ['keep-tags',
                          'keep-labels']},
            {'value': "feedback",
            'subfields': ['content']},
            {'value': 'proportional-tagging',
            'subfields': ['proportional-tags']},
            {'value': 'proportional-labelling',
            'subfields': ['label-name', 'proportional-labels']},
            {'value': 'url-forwarding',
            'subfields': ['forward-url']},
            {'value': 'sms-forwarding',
            'subfields': ['forward-to', 
                           'set-forward-message-condition',
                           'forward-content']},
            {'value': 'sms-invite',
            'subfields': ['invite-content',
                          'invitee-tag',
                          'feedback-inviter']}               
        ]
    },
    "tag": {'type': 'text'},
    "enroll": {
        'type': 'select',
        'data': 'server-dynamic',
        'fieldset': true
    },
    'offset-days': {
        'type': 'container',
        'contains': ['days', 'at-time'],
        'skip': false,
        'add-prefix': true
    },
    "proportional-tags": {
        'type': 'list',
        'add-button': true,
        'adds': 'proportional-tag'
    },
    "proportional-tag": {
        'type': 'container', 
        'contains': ["tag", "weight"],
        'skip': true,
    },
    "proportional-labels": {
        'type': 'list',
        'add-button': true,
        'adds': 'proportional-label',
    },
    "proportional-label": {
        'type': 'container', 
        'contains': ["label-value", "weight"],
        'skip': true,
    },
    "label-name": {'type': 'text'},
    "label-value": {'type': 'text'},
    "set-forward-message-condition": {
        'type': 'spancheckboxes',
        'value': 'forward-message-condition',
        'subfields': [
            "forward-message-condition-type",
            "forward-message-no-participant-feedback"],
        'style': 'padding-top:0px;padding-bottom:5px',
    },
    "forward-message-condition-type": {
        'type': 'spanradiobuttons',
        'options': [
            {'value': "phone-number"}],
        'style': 'padding-top:0px',
    },
    "forward-message-no-participant-feedback": {
        'type': 'textarea',
    },
    "weight": {'type': 'text'},
    'forward-url': {'type': 'text'},
    'forward-to': {'type': 'text'},
    'forward-content': {'type': 'textarea'},
    'invite-content': {'type': 'textarea'},
    'invitee-tag': {'type': 'text'},
    'feedback-inviter': {'type': 'textarea'},
    'keep-tags': {'type': 'text'},
    'keep-labels': {'type': 'text'}
    
}
$.extend(dynamicForm, action);