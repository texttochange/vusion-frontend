var dynamicForm = {};

var dialogue = {
    "Dialogue":{
        'type': 'container'
        'contains': ["name",  "set-prioritized", "auto-enrollment", "interactions","dialogue-id", "activated"],
    },
    "dialogue-id":{ 
        'type': "hidden",
    }
    "auto-enrollment":{ 
        'type': "select",
        'options':  ["none": 'None', "all": "All participants"],
    }    
    "set-prioritized": {
        'type': "checkboxes",
        'value': 'prioritized'
    },
    "interactions": {
        'type': 'list',
        'add-button': true
        'adds' : "interaction",
    },
    "interaction": {
        'type': 'containers',
        'contains': [
            "type-schedule", 
            "type-interaction",
            "interaction-id", 
            "activated"],
    }
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
            {"value": "offset-days",
            "subfields":["minutes"]},
            {'value': "offset-time",
            'subfields': [
                "days",
                "at-time"]},
            {'value': "offset-condition",
            'subfields': ["offset-condition-interaction-id"]},
    "type-interaction": {
        "type": "radiobuttons",
        "options": [
            {"value": "announcement",
            "subfields": "content"},
            {"value": "question-answer",
            'subfields': [
                "content",
                "keyword", 
                "set-use-template", 
                "type-question", 
                "set-max-unmatching-answers", 
                "radio-type-unmatching-feedback",
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
            {"value": "no-unmatching-feedback"} 
            {'value': "program-unmatching-feedback"}
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
             "subfield": [
                 "label-for-participant-profiling", 
                 "checkbox-set-answer-accept-no-space", 
                 "answers"]},
            {"value": 'open-question',
             "subfield": [
                 "answer-label", 
                 "feedbacks"]}
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
    },
    "feedbacks": {
        "type": "list",
        "add-button": true,
        "adds": "feedback",
    },
    "feedback": {
        "type": 'container',
        "contains": ["content"]
    },
    "answer-actions": {
        "type": "list",
        "add-button": true,
        "adds": "actions"
    },
    "choice": {
        "type": "text",
    }
    "answer-keywords": {
        'type': 'list',
        'add-button': true
        'adds': "answer-keyword",
    },
    "answer-keyword": {
        'type': 'container',
        'contains': ["keyword","feedbacks", "answer-actions"],
    },
    "offset-condition-interaction-id": {
        'type': "select",
        'options': 'dynamic'
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
    }
    "response":{ 
        "type": "container",
        "contains": ["content"],
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
        'add-button': true
        'add': "action",
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
    },
    "set-condition": {
        'type': 'checkboxes',
        'value': 'condition',
        'subfields': [
            "condition-operator", 
            "subconditions"],
    },
    "condition-operator": { 
        'type': 'radio',
        'options': [
            {'value': "all-subconditions"},
            {'value': "any-subconditions"}],
    },
    'subconditions': {
        'type': 'list'
        'add-button': true,
        'adds': 'subcondition'
    },
    "subcondition": {
        'type': 'container', 
        'contains': ["subcondition-field", "subcondition-operator", "subcondition-parameter"],
        'align': 'horizontal',
    },
    "subcondition-field": {
        'type': 'select',
        'options': 'dynamic',
        'modify': ['subcondition-operator', 'subcondition-parameter'],
    },
    "subcondition-operator": {
        'type': 'select',
        'options': 'dynamic',
        'modify': ['subcondition-parameter']
    },
    "subcondition-parameter": {
        'type': 'select',
        'options': 'dynamic',
    },
    "type-action": {
        'type': 'radio',
        'options': [
            {'value': "optin"},
            {'value': "optout"}, 
            {'value': "enrolling",
            'subfields': ['enroll']},
            {'value': "delayed-enrolling",
            'subfields': ['enroll', 'offset-days']}, 
            {'value': "tagging",
            'subfields': 'tag'},
            {'value': "reset"},
            {'value': "feedback",
            'subfields': 'content'}]
    }
    "tag": {'type': 'text'},
    "enroll": {
        'type': 'select',
        'option': 'dynamic',
    },
}
$.extend(dynamicForm, action);