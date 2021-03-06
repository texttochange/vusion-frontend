<?php

App::uses('Component', 'Controller');

class DynamicFormComponent extends Component 
{
    
    var $localizedValueLabels = array();
    
    public function startup($controller)
    {
        $this->localizedValueLabels = array(
            "name" => __('Name'),
            "auto-enrollment" => __('Automatic enrollment'),
            "dialogue" => __('Dialogue'),
            "interactions" => __('Interactions'),
            "interaction" => __('Interaction'),
            "keyword" => __('Keyword'),
            "characters" => __('characters'),
            "choice" => __('Choice'),
            "content" => __('Content'),
            "date-time" => __('Time'),
            "immediately" => __('Immediately'),
            "fixed-time" => __('Fixed time'),
            "offset-days" => __('Offset days'),
            "offset-time"=> __('Offset time'),
            "minutes"=> __('Minutes'),
            "at-time"=> __('At Time'),
            "days"=> __('Days'),
            "announcement"=> __('Announcement'),
            "question-answer"=> __('Question'),
            "question-answer-keyword"=> __('Question multi-keyword'),
            "open-question"=> __('Open question'),
            "closed-question"=> __('Closed question'),
            "answers"=> __('Answers'),
            "answer"=> __('Answer'),
            "answer-keywords"=> __('Answers'),
            "answer-keyword"=> __('Answer'),
            "label-for-participant-profiling"=> __("Profile participant's reply with label"),
            "answer-label"=> __('Answer label'),
            "feedbacks"=> __('Feedbacks'),
            "feedback"=> __('Feedback'),
            "answer-actions"=> __('Actions'),
            "answer-action"=> __('Action'),
            "optin"=> __('Opt-in'),
            "optout"=> __('Opt-out'),
            "enrolling"=> __('Enrolling'),
            "delayed-enrolling"=> __('Delayed Enrolling'),
            "tagging"=> __('Tagging'),
            "reset"=> __('Reseting'),
            "add"=> __('Add'),
            "remove"=> __('Remove'),
            "responses"=> __('Responses'),
            "response"=> __('Response'),
            "actions"=> __('Actions'),
            "action"=> __('Action'),
            "save"=> __('Save'),
            "set-reminder"=> __('Set Reminder'),
            "reminder-number"=> __('Number of Reminder(s)'),
            "reminder-minutes"=> __('Reminder every x minutes'),        
            "reminder-days"=> __('Reminder every x days'),
            "reminder-at-time"=> __('At the time'),        
            "reminder-actions"=> __('Actions'),
            "reminder-action"=> __('Action'),
            "offset-condition"=> __('Answer Required'),
            "offset-condition-interaction-id"=> __('Participant has to answer'),
            "offset-condition-delay"=> __('Delaying for (in minute)'),
            "answer-accept-no-space"=> __('Accept no space between the keyword and the choice'),
            "set-use-template"=> __('Use template from program settings'),
            "set-no-request-matching-try-keyword-only"=> __('In case no matching on other requests, try to match only the message first word.'),
            "set-matching-answer-actions"=> __('Set actions for any matching answer'),
            "matching-answer-actions"=> __('Actions'),
            "matching-answer-action"=> __('Action'),
            "set-max-unmatching-answers"=> __('Set maximum number of accepted unmatching answers'),
            "max-unmatching-answer-number"=> __('Maximum number of accepted unmatching answers'),        
            "max-unmatching-answer-actions"=> __('Actions'),
            "max-unmatching-answer-action"=> __('Action'),
            "no-unmatching-feedback"=> __('No unmatching feedback'),
            "program-unmatching-feedback" => __('Default program umatching feedback'),
            "interaction-unmatching-feedback" => __('Custom umatching message'),
            "unmatching-feedback-content"=> __('Content'),
            "prioritized"=> __('Has priority'),
            "request-content"=> __('Content'),
            "dialogue-content"=> __('Content'),
            "set-condition"=> __('Set Condition on Participant'),
            "all-subconditions"=> __('Match All Subcondition'),
            "any-subconditions"=> __('Match Any Subcondition'),
            "subconditions"=> __('Subconditions'),
            "subcondition"=> __('Subcondition'),
            "set-prioritized"=> __('Prioritize'),
            "reminder-offset-time"=> __('Remind at offset time'),
            "reminder-offset-days"=> __('Remind at offset days'),
            "all-subconditions"=> __('All subcondition have to be true'),
            "any-subconditions"=> __('Only one subcondition has to be true'),
            "set-answer-accept-no-space"=> __('Accept no space between the keyword and the choice'),
            "none"=> __('none'),
            "all-participants"=> __('all participants'),
            "tagged"=> __('tagged'),
            "labelled"=> __('labelled'),
            "proportional-tagging"=> __('Proportional Tagging'),
            "proportional-tags"=> __('Proportional Tags'),
            "proportional-tag"=> __('Proportional Tag'),
            "proportional-labelling" => __('Proportional Labelling'),
            "proportional-labels" => __('Proportional Labels'),
            "proportional-label" => __('Proportional Label'),
            "url-forwarding"=> __('URL Forward'),
            "forward-url"=> __('Forward URL'),
            "sms-forwarding"=> __('SMS Forward'),
            "forward-to"=> __('Receiver Tag(s) and/or Label(s)'),
            "forward-content"=> __('Content'),
            "sms-invite"=> __('SMS Invite'),
            "sms-mo" => __('SMS MO'),
            "mo-content" => __('Content'),
            "invite-content"=> __('Content'),
            "invitee-tag"=> __('Invitee Tag'),
            "feedback-inviter"=> __('Feedback inviter'),
            "tag"=> __('Tag'),
            "weight"=> __('Weight'),
            "label-name" => __("Label name"),
            "label-value" => __("Label value"),
            "equal-to"=> __('equal to'),
            "start-with-any"=> __('start with any'),
            "matching"=> __('matching'),
            "not-matching"=> __('not matching'),
            "many"=> __('many'),
            "any"=> __('any'),
            "all"=> __('all'),
            "match" => __('match the following conditions'),
    		"auto-enrollment-box" => __('Auto enrollment'),
    		"phone-number" => __('Phone number'),
    		'with' => __('with'),
            'not-with' => __('not with'),
            "set-forward-message-condition" => __("Retrive a condition in the message"),
            "forward-message-no-participant-feedback" => __("Feedback in case no participant is matching"),
            "set-only-optin-count" => __("Set count only optin participant"),
            'keep-tags' => __('Keep these tags'),
            'keep-labels' => __('Keep these labels'),
            "announcement-actions" => __("Action at sending time"),
            'save-content-variable-table' => __("Save Content Variable Table"),
            'scvt-row-keys' => __("Row Keys"),
            'scvt-row-key' => __("Row Key"),
            'scvt-row-header' => __('Header'),
            'scvt-row-value' => __('Row Value'),
            'scvt-col-key-header' => __("Column Key Header"),
            'scvt-col-extras' => __("Extra Columns"),
            'scvt-col-extra' => __("Extra Column"),
            'scvt-attached-table' => __("Attached Table"),
            'scvt-col-extra-header' => __('Header'),
            'scvt-col-extra-value' => __('Row Value'),
            );
        $controller->set('dynamicFormLabels', $this->localizedValueLabels);
    }
}