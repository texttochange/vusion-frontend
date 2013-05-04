<?php

class VusionConst
{
    const TAG_REGEX='/^[a-z0-9A-Z\s]+$/';
    const TAG_FAIL_MESSAGE='Only letters and numbers. Must be tag, tag, ... e.g cool, nice, ...';

    const APOSTROPHE_REGEX='/.*[’`’‘]/';
    const APOSTROPHE_FAIL_MESSAGE='The apostrophe used is not allowed.';
}