<?php header("Content-type: text/javascript"); ?>

var localized_errors = {
    past_date_error: "<?php echo __("Date must be greater than or equal to {0}.")?>",
    vusion_ajax_error: "<?php echo __("Poor network connection.")?>",
    validation_required_error: "<?php echo __("This field is required.")?>",
    validation_keyword_invalid_character_error: "<?php echo __(" has some invalid characters. Keywords must contain only numbers or letters separated by a comma.")?>",
    validation_keyword_blank_error: "<?php echo __("You cannot have a blank keyword.")?>",
    validation_keyword_used_same_script_error: "<?php echo __(" already used by the same script in another question.")?>",
};

var localized_labels= {
    "dialogue": "<?php echo __('dialogue')?>",
    "interaction": "<?php echo __('dialogue')?>",
    "keyword": "<?php echo __('keyword')?>",
    "content": "<?php echo __('content')?>",
    "date-time": "<?php echo __('time')?>",
};

