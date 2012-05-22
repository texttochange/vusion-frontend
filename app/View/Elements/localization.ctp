<?php header("Content-type: text/javascript"); ?>

var localized_errors = {
    past_date_error: "<?php echo __("Scheduling time cannot be in the past {0}.")?>",
    vusion_ajax_error: "<?php echo __("Error: action failed.")?>",
    vusion_ajax_timeout_error: "<?php echo __("Poor network connection (request timed out).")?>",
    validation_required_error: "<?php echo __("This field is required.")?>",
    validation_keyword_invalid_character_error: "<?php echo __(" has some invalid characters. Keywords must contain only numbers or letters separated by a comma.")?>",
    validation_keyword_blank_error: "<?php echo __("You cannot have a blank keyword.")?>",
    validation_keyword_used_same_script_error: "<?php echo __(" already used by the same script in another question.")?>",
};

var localized_messages = {
    vait_redirection: "<?php echo __('Wait for redirection.')?>",
};

var localized_labels = {
    "name": "<?php echo __('Name')?>",
    "auto-enrollment": "<?php echo __('Automatic enrollment')?>",
    "dialogue": "<?php echo __('Dialogue')?>",
    "interaction": "<?php echo __('dialogue')?>",
    "keyword": "<?php echo __('Keyword')?>",
    "choice": "<?php echo __('Choice')?>",
    "content": "<?php echo __('Content')?>",
    "date-time": "<?php echo __('Time')?>",
    "immediately": "<?php echo __('Immediately')?>",
    "fixed-time": "<?php echo __('Fixed time')?>",
    "wait": "<?php echo __('Wait')?>",
    "announcement": "<?php echo __('Announcement')?>",
    "question": "<?php echo __('Question')?>",
    "open-question": "<?php echo __('Open question')?>",
    "closed-question": "<?php echo __('Closed question')?>",
};

