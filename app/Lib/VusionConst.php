<?php

class VusionConst
{
    const KEYWORD_REGEX = '/^[a-zA-Z0-9]+(,(\s)?[a-zA-Z0-9]+)*$/';
    const KEYWORD_FAIL_MESSAGE = 'The keyword/alias is(are) not valid.';

    const TAG_REGEX = '/^[a-z0-9A-Z\s]+$/';
    const TAG_FAIL_MESSAGE = "Use only space, letters and numbers for tag, e.g 'group 1'.";

    const TAG_LIST_REGEX = null;
    const TAG_LIST_FAIL_MESSAGE = 'Only space letters and numbers separate by coma. Must be tag1, tag2, ... e.g cool, nice, ...';

    const LABEL_VALUE_REGEX = '/^[a-z0-9A-Z\s\.]+$/';
    const LABEL_VALUE_FAIL_MESSAGE = 'Use only DOT, space, letters and numbers for the label value.';

    const LABEL_FULL_REGEX = '/^[a-z0-9A-Z\s]+:[a-z0-9A-Z\s\.]+$/';
    const LABEL_FULL_FAIL_MESSAGE = "The correct format is 'label:value'.";

    const LABEL_REGEX = '/^[a-z0-9A-Z\s]+$/';
    const LABEL_FAIL_MESSAGE = 'Use only space, letters and numbers for the label name.';

    const APOSTROPHE_REGEX = '/.*[’`’‘]/';
    const APOSTROPHE_FAIL_MESSAGE = 'The apostrophe used is not allowed.';

    const DATE_TIME_REGEX = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/';
    const DATE_TIME_FAIL_MESSAGE = 'The date time is not in an ISO format.';

    const ATTIME_REGEX = '/^([0-1]\d|2[0-4]):([0-5]\d|60)$/';
    const ATTIME_FAIL_MESSAGE = 'The at-time is not valid.';
    
    const CONTENT_VARIABLE_KEYS_REGEX = '/^[a-z0-9A-Z\s]+\.[a-z0-9A-Z\s]+$/';
    const CONTENT_VARIABLE_KEYS_FAIL_MESSAGE = 'Only letters and numbers for keys. The correct format is "key.key".';
}