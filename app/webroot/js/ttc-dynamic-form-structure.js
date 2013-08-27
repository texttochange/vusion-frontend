var dynamicForm = {};

var dialogue = {
    "Dialogue":{
        'type': 'container',
        'contains': ["name",  "set-prioritized", "auto-enrollment", "interactions","dialogue-id", "activated"],
        'skip': true,
    },
    'name': {
        'type': 'text',
    },
    "dialogue-id":{ 
        'type': "hidden",
    },
    "auto-enrollment":{ 
        'type': "select",
        'data': "static",
        'options':  ["none", "all"],
        'fieldset': true
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
    },
    "activated": {
        'type': "hidden",
    },
    "type-schedule": {
        "type": "radiobuttons",
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
            'subfields': ["offset-condition-interaction-id"]}]},
    "type-interaction": {
        "type": "radiobuttons",
        "options": [
            {"value": "announcement",
            "subfields": ["content"]},
            {"value": "question-answer",
            'subfields': [
                "content",
                "keyword", 
                "set-use-template", 
                "type-question", 
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
        'type': "radiobuttons",
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
    "set-max-unmatching-answers":{
        'type': "checkboxes",
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
        'type': "radiobuttons",
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
        'type': "select",
        'data': 'server-dynamic',
        'fieldset': true,
        'onmouseover': 'updateOffsetConditions(this)',
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
        'type': 'checkboxes',
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
        'type': "radiobuttons",
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
        'type': 'checkboxes',
        'value': 'condition',
        'subfields': [
            "condition-operator", 
            "subconditions"],
    },
    "condition-operator": { 
        'type': 'radiobuttons',
        'options': [
            {'value': "all-subconditions"},
            {'value': "any-subconditions"}],
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
        'type': 'radiobuttons',
        'options': [
            {'value': "optin"},
            {'value': "optout"}, 
            {'value': "enrolling",
            'subfields': ['enroll']},
            {'value': "delayed-enrolling",
            'subfields': ['enroll', 'offset-days']}, 
            {'value': "tagging",
            'subfields': ['tag']},
            {'value': "reset"},
            {'value': "feedback",
            'subfields': ['content']},
            {'value': 'proportional-tagging',
            'subfields': ['proportional-tags']},
            {'value': 'message-forwarding',
            'subfields': ['url']}
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
    "weight": {'type': 'text'},
    'url': {'type': 'text'}
}
$.extend(dynamicForm, action);