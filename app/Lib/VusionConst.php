<?php

class VusionConst
{
    const KEYWORD_REGEX = '/^[a-zA-Z0-9]+(,(\s)?[a-zA-Z0-9]+)*$/';
    const KEYWORD_FAIL_MESSAGE = 'The keyword/alias is(are) not valid.';

    const TAG_REGEX='/^[a-z0-9A-Z\s]+$/';
    const TAG_FAIL_MESSAGE='Only letters and numbers. Must be tag, tag, ... e.g cool, nice, ...';

    const APOSTROPHE_REGEX='/.*[’`’‘]/';
    const APOSTROPHE_FAIL_MESSAGE='The apostrophe used is not allowed.';

    const DATE_TIME_REGEX='/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})$/';
    const DATE_TIME_FAIL_MESSAGE='The date time is not in an ISO format.';
}