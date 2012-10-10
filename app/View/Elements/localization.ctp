<?php header("Content-type: text/javascript"); ?>

var localized_errors = {
    past_date_error: "<?php echo __("Scheduling time cannot be in the past {0}.")?>",
    vusion_ajax_error: "<?php echo __("Error: action failed.")?>",
    vusion_ajax_timeout_error: "<?php echo __("Poor network connection (request timed out).")?>",
    validation_required_error: "<?php echo __("This field is required.")?>",
    validation_required_checked: "<?php echo __("Select one of the choices")?>",
    validation_required_answer_label: "<?php echo __("Required in order to label the participant when he is answering.")?>",
    validation_required_content: "<?php echo __("Required a message content.")?>",
    validation_offset_days_min: "<?php echo __("Offset day is minimun 1 otherwise use offset-time.")?>",
    validation_offset_time_min: "<?php echo __("Offset time minutes cannot be negative.")?>",
    validation_reminder_min: "<?php echo __("Require at least 1 reminder.")?>",
    validation_keyword_invalid_character_error: "<?php echo __(" has some invalid characters. Keywords must contain only numbers or letters separated by a comma.")?>",
    validation_keyword_blank_error: "<?php echo __("You cannot have a blank keyword.")?>",
    validation_keyword_used_same_script_error: "<?php echo __(" already used by the same script in another question.")?>",
    validation_choice_duplicate: "<?php echo __("This choice is already used in this interaction.")?>",
    vusion_ajax_action_failed: "<?php echo __("Action failed: ")?>",
};

var localized_messages = {
    vait_redirection: "<?php echo __('Wait for redirection.')?>",
};

var localized_actions= {
    save_dialogue: "<?php echo __('Save dialogue.')?>",
    save_request: "<?php echo __('Save request.')?>",
};

var localized_labels = {
    "name": "<?php echo __('Name')?>",
    "auto-enrollment": "<?php echo __('Automatic enrollment')?>",
    "dialogue": "<?php echo __('Dialogue')?>",
    "interactions": "<?php echo __('Interactions')?>",
    "interaction": "<?php echo __('Interaction')?>",
    "keyword": "<?php echo __('Keyword')?>",
    "choice": "<?php echo __('Choice')?>",
    "content": "<?php echo __('Content')?>",
    "date-time": "<?php echo __('Time')?>",
    "immediately": "<?php echo __('Immediately')?>",
    "fixed-time": "<?php echo __('Fixed time')?>",
    "offset-days": "<?php echo __('Offset days')?>",
    "offset-time": "<?php echo __('Offset time')?>",
    "minutes": "<?php echo __('Minutes')?>",
    "at-time": "<?php echo __('At Time')?>",
    "days": "<?php echo __('Days')?>",
    "announcement": "<?php echo __('Announcement')?>",
    "question": "<?php echo __('Question')?>",
    "question-multi-keyword": "<?php echo __('Question multi-keyword')?>",
    "open-question": "<?php echo __('Open question')?>",
    "closed-question": "<?php echo __('Closed question')?>",
    "answers": "<?php echo __('Answers')?>",
    "answer": "<?php echo __('Answer')?>",
    "answer-keywords": "<?php echo __('Answers')?>",
    "answer-keyword": "<?php echo __('Answer')?>",
    "label-for-participant-profiling": "<?php echo __("Profile participant's reply with label")?>",
    "answer-label": "<?php echo __('Answer label')?>",
    "feedbacks": "<?php echo __('Feedbacks')?>",
    "feedback": "<?php echo __('Feedback')?>",
    "answer-actions": "<?php echo __('Actions')?>",
    "answer-action": "<?php echo __('Action')?>",
    "optin": "<?php echo __('Opt-in')?>",
    "optout": "<?php echo __('Opt-out')?>",
    "enrolling": "<?php echo __('Enroll')?>",
    "delayed-enrolling": "<?php echo __('Delayed Enroll')?>",
    "tagging": "<?php echo __('Tag')?>",
    "reset": "<?php echo __('Reset')?>",
    "add": "<?php echo __('Add')?>",
    "remove": "<?php echo __('Remove')?>",
    "responses": "<?php echo __('Responses')?>",
    "response": "<?php echo __('Response')?>",
    "actions": "<?php echo __('Actions')?>",
    "action": "<?php echo __('Action')?>",
    "save": "<?php echo __('Save')?>",
    "reminder": "<?php echo __('Set Reminder')?>",
    "reminder-number": "<?php echo __('Number of Reminder(s)')?>",
    "reminder-minutes": "<?php echo __('Reminder every x minutes')?>",        
    "reminder-days": "<?php echo __('Reminder every x days')?>",
    "reminder-at-time": "<?php echo __('At the time')?>",        
    "reminder-actions": "<?php echo __('Actions')?>",
    "reminder-action": "<?php echo __('Action')?>",
    "offset-condition": "<?php echo __('Answer Required')?>",
    "answer-accept-no-space": "<?php echo __('Accept no space between the keyword and the choice')?>",
    "use-template": "<?php echo __("Use template from program settings")?>",
    "no-request-matching-try-keyword-only": "<?php echo __("In case no matching on other requests, try to match only the message first word.")?>",
};

