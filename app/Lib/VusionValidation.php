<?php 
App::uses('VusionConst', 'Lib');

class VusionValidation extends Validation {


    public static function customNot($check, $regex=null)
    {
        return !self::custom($check, $regex);
    }

    
    public static function validContentVariable($check)
    {
        preg_match_all(VusionConst::CUSTOMIZE_CONTENT_MATCHER_REGEX, $check['double-matching-answer-feedback']['double-optin-error-feedback'], $matches, PREG_SET_ORDER);
        $allowed = array("domain", "key1", "key2", "key3", "otherkey");
        foreach ($matches as $match) {
            $match = array_intersect_key($match, array_flip($allowed));
            foreach ($match as $key=>$value) {
                if (!preg_match(VusionConst::CONTENT_VARIABLE_KEY_REGEX, $value)) {
                    return __("To be used as customized content, '%s' can only be composed of letter(s), digit(s) and/or space(s).", $value);
                }
            }
            if (!preg_match(VusionConst::CUSTOMIZE_CONTENT_DOMAIN_REGEX, $match['domain'])) {
                return __("To be used as customized content, '%s' can only be either 'participant' or 'contentVariable'.", $match['domain']);
            }
            if ($match['domain'] == 'participant') {
                if (isset($match['key2'])) {
                    return VusionConst::CUSTOMIZE_CONTENT_DOMAIN_PARTICIPANT_FAIL;
                }
            } else if ($match['domain'] == 'contentVariable') {
                if (isset($match['otherkey'])) {
                    return VusionConst::CUSTOMIZE_CONTENT_DOMAIN_CONTENTVARIABLE_FAIL;
                }
            } 
        }
        return true;
    }
    

}