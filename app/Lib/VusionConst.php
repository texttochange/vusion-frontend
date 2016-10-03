<?php

class VusionConst
{
    const KEYWORD_REGEX = '/^[\p{L}\p{Mn}\p{N}]+$/u';
    const KEYWORD_FAIL_MESSAGE = 'The keyword is not valid.';

    const KEYWORDS_REGEX = '/^[\p{L}\p{Mn}\p{N}]+(,(\s)?[\p{L}\p{Mn}\p{N}]+)*$/u';
    const KEYWORDS_FAIL_MESSAGE = 'The keyword/alias is(are) not valid.';

    const KEYPHRASE_REGEX = '/^[\p{L}\p{Mn}\p{N}\s]+(,(\s)?[\p{L}\p{Mn}\p{N}\s]+)*$/u';
    const KEYPHRASE_FAIL_MESSAGE = 'This keyword/keyphrase is not valid.';

    const CHOICE_REGEX = '/^[\p{L}\p{N}\s]+$/u';
    const CHOICE_FAIL_MESSAGE = 'The choice is(are) not valid.';

    const TAG_REGEX = '/^[\p{L}\p{Mn}\p{N}\p{Zs}]+$/u';
    const TAG_FAIL_MESSAGE = "Use only space, letters and numbers for tag, e.g 'group 1'.";

    const TAG_LIST_REGEX = '/^[\p{L}\p{Mn}\p{N}\p{Zs}]+(,(\s)?[\p{L}\p{Mn}\p{N}\p{Zs}]+)*$/u';
    const TAG_LIST_FAIL_MESSAGE = 'Only space letters and numbers separate by coma. Must be tag1, tag2, ... e.g cool, nice, ...';

    const LABEL_NAMES_LIST_REGEX = '/^[\p{L}\p{Mn}\p{N}\p{Zs}]+(,(\s)?[\p{L}\p{Mn}\p{N}\p{Zs}]+)*$/u';
    const LABEL_NAMES_LIST_FAIL_MESSAGE = 'Only space letters and numbers separate by coma. Must be label1, label2, ... e.g age, name, ...';

    const LABEL_REGEX = '/^[\p{L}\p{Mn}\p{N}\p{Zs}]+$/u';
    const LABEL_FAIL_MESSAGE = 'Use only space, letters and numbers for the label name.';

    const LABEL_VALUE_REGEX = '/^[\p{L}\p{Mn}\p{N}\p{Zs}@\.-:\'\/]+$/u';
    const LABEL_VALUE_FAIL_MESSAGE = "Use only DOT, space, letters, :, /, ' and numbers for the label value.";

    const LABEL_FULL_REGEX = '/^[\p{L}\p{Mn}\p{N}\p{Zs}]+:[\p{L}\p{Mn}\p{N}\p{Zs}@\.-]+$/u';
    const LABEL_FULL_FAIL_MESSAGE = "The correct format is 'label:value'.";

    const LABEL_SELECTOR_REGEX = '/^[\p{L}\p{Mn}\p{N}\p{Zs}]+:([\p{L}\p{Mn}\p{N}\p{Zs}@\.]+|\[(participant)\.[\p{L}\p{Mn}\p{N}\p{Zs}]+\])$/u';

    //const APOSTROPHE_REGEX = '/.*[’`’‘]/';
    const APOSTROPHE_REGEX = '/.*[’`’‘]+[?!\p{Arabic}]/';
    const APOSTROPHE_FAIL_MESSAGE = 'The apostrophe used is not allowed.';

    const DATE_REGEX = '/^\d{4}-\d{2}-\d{2}$/';
    const DATE_FAIL_MESSAGE = 'The date is not in an ISO format.';
    
    const DATE_TIME_ISO_FORMAT = 'Y-m-d\TH:i:s';
    const DATE_TIME_REGEX = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/';
    const DATE_TIME_FAIL_MESSAGE = 'The date time is not in an ISO format.';

    const ATTIME_REGEX = '/^([0-1]\d|2[0-4]):([0-5]\d|60)$/';
    const ATTIME_FAIL_MESSAGE = 'The at-time is not valid.';

    const FORWARD_URL_REGEX = "/^http:\/\/[A-Za-z0-9.-]+(:[0-9]+)?((\/[\+~%\/.\w-_]*)?\??(([-\+;%@.\w_]*=[\['-\+;%@.\w_\s\]]*)(&[-\+;%@.\w_]*=[\['-\+;%@.\w_\s\]]*)*)?)?$/";
    const FORWARD_URL_FAIL_MESSAGE = 'The forward url is not valid.';
    const FORWARD_URL_REPLACEMENT_REGEX = '/\[[-\+&;%@.\w_]*\]/';

    const CONTENT_VARIABLE_KEY_REGEX = '/^[\p{L}\p{Mn}\p{N}\p{Zs}\,\+\/-\:\#]+$/u';
    const CONTENT_VARIABLE_KEY_FAIL_MESSAGE = "Use only space, letters, numbers or the characters ,+/-:# for a key, e.g 'uganda 1'.";
    const CONTENT_VARIABLE_VALUE_REGEX = '/^[\p{L}\p{Mn}\p{N}\p{Zs}\.\,\+\/-\:\#]*$/u';
    const CONTENT_VARIABLE_VALUE_FAIL_MESSAGE = "Use only space, letters, numbers or the characters ,.+/-:# for a value, e.g 'new value1'.";

    const PHONE_NORMAL_REGEX = '/^\+[0-9]+$/';
    const PHONE_NORMAL_REGEX_FAIL_MESSAGE = "A phone number must begin with a '+' sign and end with a serie of digits such as +335666555.";
    const PHONE_SIMULATED_REGEX = '/^\#[0-9]+$/';
    const PHONE_SIMULATED_REGEX_FAIL_MESSAGE = "A phone number must begin with a '#' sign and end with a serie of digits such as #33.";
    # group of regex to hepl at different stage of the validation of dynamic content => (content variables)
    const PARTICIPANT_CUSTOMIZED_CONTENT_KEY_REGEX = '/^[\p{L}\p{Mn}\p{N}\p{Zs}]*(_raw)?$/u';

    const CUSTOMIZE_CONTENT_MATCHER_REGEX = '/\[(?P<domain>[^\.\]]*)\.(?P<key1>[^\.\]]*)(\.(?P<key2>[^\.\]]*))?(\.(?P<key3>[^\.\]]*))?(\.(?P<otherkey>[^\.\]]*))?\]/';
    const CUSTOMIZE_CONTENT_DOMAIN_DEFAULT = 'participant|contentVariable|time';
    const CUSTOMIZE_CONTENT_DOMAIN_RESPONSE = 'participant|contentVariable|time|context';
    
    const CUSTOMIZE_CONTENT_DOMAIN_PARTICIPANT_FAIL = 'To be used in message, participant only accepts one key.';
    const CUSTOMIZE_CONTENT_DOMAIN_CONTENTVARIABLE_FAIL = 'To be used in message, contentVariable only accepts maximum three keys.';

    const PREFIXED_LOCAL_CODE_REGEX = '/^[0-9]*\-[0-9]*$/';
    const INTERNATIONAL_CODE_REGEX = '/^\+[0-9]*$/';
    const PHONE_REGEX = '/^\+[0-9]*$/';
    const EMAIL_REGEX = '/^[\w\-\.+]+@([\w+\-]+\.)+[a-zA-Z]{2,5}/';


    const MAX_JOIN_PHONES = 500000;
}