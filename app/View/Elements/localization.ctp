<?php header("Content-type: text/javascript"); ?>

var localized_errors = {
    past_date_error: "<?php echo __("Scheduling time cannot be in the past {0}.")?>",
    vusion_ajax_error: "<?php echo __("Error: action failed.")?>",
    vusion_ajax_action_failed: "<?php echo __("Action {0} failed.")?>",
    vusion_ajax_action_failed_due: "<?php echo __("Action {0} failed due to {1}.")?>",
    vusion_ajax_timeout_error: "<?php echo __("Poor network connection (request timed out).")?>",
    vusion_ajax_connection_error: "<?php echo __("Cannot connect the server.")?>",
    validation_error: "<?php echo __("Saved failed due to validation error.")?>",
    validation_required_error: "<?php echo __("This field is required.")?>",
    validation_required_checked: "<?php echo __("Select one of the choices")?>",
    validation_required_answer_label: "<?php echo __("Required in order to label the participant when he is answering.")?>",
    validation_required_content: "<?php echo __("Required a message content.")?>",
    validation_required_letter_digit_space: "<?php echo __("Only letters, digits and spaces are allowed.")?>",
    validation_offset_days_min: "<?php echo __("Offset day is minimun 1 otherwise use offset-time.")?>",
    validation_offset_time_min: "<?php echo __("Offset time not valid, you can express only minutes '10' or minutes and seconds '00:30'.")?>",
    validation_number_min: "<?php echo __("Must be at least 1.")?>",
    validation_keyword_invalid_character_error: "<?php echo __(" has some invalid characters. Keywords must contain only numbers or letters separated by a comma.")?>",
    validation_keywords_invalid_character_error: "<?php echo __("There is some invalid character(s) or space(s). Keyword can only numbers or letters separated by a comma.")?>",
    validation_keyword_blank_error: "<?php echo __("You cannot have a blank keyword.")?>",
    validation_keyword_used_same_script_error: "<?php echo __(" already used by the same script in another question.")?>",
    validation_choice_duplicate: "<?php echo __("This choice is already used in this interaction.")?>",
    interaction_summary_error: "<?php echo __("There is an error inside this interaction.") ?>",
    timeout: "<?php echo __("timeout") ?>",
    validation_masstag: "<?php echo __("Your MassTag has special caharacters.These are not allowed")?>",
    validation_double_space: "<?php echo __(": You have double spaces ")?>",
    validation_apostrophe: "<?php echo __(" The apostrophe used in this message is not valid.")?>",
    validation_choice_format: "<?php echo __(" You are entering two choices or a special caharacter")?>",
    validation_choice_index: "<?php echo __("The choice is ambigious when answering by index, please Enter a different choice")?>",
    validation_unique_dialogue_name: "<?php echo __("This Dialogue Name already exists. Please choose another.")?>",
    warning_unattached_message: "<?php echo __("WARNING: Everything in the content area will be replaced.")?>",
};

var localized_messages = {
    wait_redirection: "<?php echo __('Wait for redirection.')?>",
    generating_file: "<?php echo __('Generating export file...')?>",
    download_should_start: "<?php echo __('The export file has been generated. The download should start shortly.')?>",
    select_one: "<?php echo __('Select one...')?>",
    table_saved: "<?php echo __('Table saved.')?>",
    content_variable_table_value: "<?php echo __('This value is editable and can be used in message with: ')?>",
};

var localized_actions= {
    save_dialogue: "<?php echo __('save dialogue')?>",
    save_request: "<?php echo __('save request')?>",
    export: "<?php echo __('Export')?>",
    filter: "<?php echo __('Filter')?>",
    filter_operator_prefix: "<?php echo __('Match')?>",
    filter_operator_suffix: "<?php echo __('of the following rules:')?>",
    filter_operator_any: "<?php echo __('any')?>",
    filter_operator_all: "<?php echo __('all')?>",
    mass_tag:"<?php echo __('Tag participants') ?>",
    mass_untag:"<?php echo __('Untag participants')?>"
};

var localized_labels = {
    "name": "<?php echo __('Name')?>",
    "auto-enrollment": "<?php echo __('Automatic enrollment')?>",
    "dialogue": "<?php echo __('Dialogue')?>",
    "interactions": "<?php echo __('Interactions')?>",
    "interaction": "<?php echo __('Interaction')?>",
    "keyword": "<?php echo __('Keyword')?>",
    "characters": "<?php echo __('characters')?>",
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
    "question-answer": "<?php echo __('Question')?>",
    "question-answer-keyword": "<?php echo __('Question multi-keyword')?>",
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
    "enrolling": "<?php echo __('Enrolling')?>",
    "delayed-enrolling": "<?php echo __('Delayed Enrolling')?>",
    "tagging": "<?php echo __('Tagging')?>",
    "reset": "<?php echo __('Reseting')?>",
    "add": "<?php echo __('Add')?>",
    "remove": "<?php echo __('Remove')?>",
    "responses": "<?php echo __('Responses')?>",
    "response": "<?php echo __('Response')?>",
    "actions": "<?php echo __('Actions')?>",
    "action": "<?php echo __('Action')?>",
    "save": "<?php echo __('Save')?>",
    "set-reminder": "<?php echo __('Set Reminder')?>",
    "reminder-number": "<?php echo __('Number of Reminder(s)')?>",
    "reminder-minutes": "<?php echo __('Reminder every x minutes')?>",        
    "reminder-days": "<?php echo __('Reminder every x days')?>",
    "reminder-at-time": "<?php echo __('At the time')?>",        
    "reminder-actions": "<?php echo __('Actions')?>",
    "reminder-action": "<?php echo __('Action')?>",
    "offset-condition": "<?php echo __('Answer Required')?>",
    "answer-accept-no-space": "<?php echo __('Accept no space between the keyword and the choice')?>",
    "set-use-template": "<?php echo __('Use template from program settings')?>",
    "set-no-request-matching-try-keyword-only": "<?php echo __('In case no matching on other requests, try to match only the message first word.')?>",
    "set-max-unmatching-answers": "<?php echo __('Set maximum number of accepted unmatching answers')?>",
    "max-unmatching-answer-number": "<?php echo __('Maximum number of accepted unmatching answers')?>",        
    "max-unmatching-answer-actions": "<?php echo __('Actions')?>",
    "max-unmatching-answer-action": "<?php echo __('Action')?>",
    "no-unmatching-feedback": "<?php echo __('No unmatching feedback')?>",
    "program-unmatching-feedback":  "<?php echo __('Default program umatching feedback')?>",
    "interaction-unmatching-feedback":  "<?php echo __('Custom umatching message')?>",
    "unmatching-feedback-content": "<?php echo __('Content')?>",
    "prioritized": "<?php echo __('Has priority')?>",
    "request-content": "<?php echo __('Content')?>",
    "dialogue-content": "<?php echo __('Content')?>",
    "set-condition": "<?php echo __('Set Condition on Participant')?>",
    "all-subconditions": "<?php echo __('Match All Subcondition')?>",
    "any-subconditions": "<?php echo __('Match Any Subcondition')?>",
    "subconditions": "<?php echo __('Subconditions')?>",
    "subcondition": "<?php echo __('Subcondition')?>",
    "set-prioritized": "<?php echo __('Prioritize')?>",
    "reminder-offset-time": "<?php echo __('Remind at offset time')?>",
    "reminder-offset-days": "<?php echo __('Remind at offset days')?>",
    "all-subconditions": "<?php echo __('All subcondition have to be true')?>",
    "any-subconditions": "<?php echo __('Only one subcondition has to be true')?>",
    "set-answer-accept-no-space": "<?php echo __('Accept no space between the keyword and the choice')?>",
    "with": "<?php echo __('with')?>",
    "not-with": "<?php echo __('not with')?>",
    "none": "<?php echo __('none')?>",
    "all": "<?php echo __('all participants')?>",
    "tagged": "<?php echo __('tagged')?>",
    "labelled": "<?php echo __('labelled')?>",
    "proportional-tagging": "<?php echo __('Proportional Tagging')?>",
    "proportional-tags": "<?php echo __('Proportional Tags')?>",
    "proportional-tag": "<?php echo __('Proportional Tag')?>",
    "url-forwarding": "<?php echo __('URL Forward')?>",
    "forward-url": "<?php echo __('Forward URL')?>",
    "sms-forwarding": "<?php echo __('SMS Forward')?>",
    "forward-to": "<?php echo __('Receiver Tag')?>",
    "forward-content": "<?php echo __('Content')?>",
    "tag": "<?php echo __('Tag')?>",
    "weight": "<?php echo __('Weight')?>",
    "is-not": "<?php echo __('is not')?>",
    "start-with": "<?php echo __('start with')?>",
    "equal-to": "<?php echo __('equal to')?>",
    "start-with-any": "<?php echo __('start with any')?>",
    "now": "<?php echo __('now')?>",
    "date-from": "<?php echo __('date from')?>",
    "date-to": "<?php echo __('date to')?>",
    "in": "<?php echo __('in')?>",
    "not-in": "<?php echo __('not in')?>",
    "is": "<?php echo __('is')?>",
    "is-any": "<?php echo __('is any')?>",
    "not-is-any": "<?php echo __('not is any')?>",
    "not-is": "<?php echo __('not is')?>",
    "from": "<?php echo __('from')?>",
    "to": "<?php echo __('to')?>", 
    "contain": "<?php echo __('contain')?>",
    "has-keyword": "<?php echo __('has keyword')?>", 
    "has-keyword-any": "<?php echo __('has keyword any')?>", 
    "matching": "<?php echo __('matching')?>",
    "not-matching": "<?php echo __('not matching')?>",
    "many": "<?php echo __('many')?>",
};

