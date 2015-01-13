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
    sending_report: "<?php echo __('Sending the report by email, it might take a few minutes....')?>",
    sending_invite: "<?php echo __('Sending the invite by email, it might take a few minutes....')?>",
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
    <?php
    $localizedLabels = array();
    if (isset($dynamicFormLabels)) {
        $localizedLabels = array_merge($localizedLabels, $dynamicFormLabels);
    }
    if (isset($filterLabels)) {
        $localizedLabels = array_merge($localizedLabels, $filterLabels);
    }
    if (isset($messageLabels)) {
        $localizedLabels = array_merge($localizedLabels, $messageLabels);
    }
    if (isset($statsLabels)) {
        $localizedLabels = array_merge($localizedLabels, $statsLabels);
    }

    foreach ($localizedLabels as $value => $label) {
        echo __('"%s":"%s",', $value, $label);
    } 
    ?>
};

