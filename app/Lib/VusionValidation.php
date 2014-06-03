<?php 
App::uses('VusionConst', 'Lib');
App::uses('Validation', 'Utility');


class VusionValidation extends Validation {


    public static function customNot($check, $regex=null)
    {
        return !self::custom($check, $regex);
    }
    
    
    public static function validContentVariable($check)
    {
        reset($check);
        $field = key($check);
        return VusionValidation::validCustomizeContent($field, $check, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_DEFAULT);
    }


    public function validCustomizeContent($field, $data, $allowedDomains)
    {
        if (isset($data[$field])) {
            preg_match_all(VusionConst::CUSTOMIZE_CONTENT_MATCHER_REGEX, $data[$field], $matches, PREG_SET_ORDER);
            $allowed = array("domain", "key1", "key2", "keys3", "otherkey");
            foreach ($matches as $match) {
                $match = array_intersect_key($match, array_flip($allowed));
                foreach ($match as $key=>$value) {
                    if (!preg_match(VusionConst::CONTENT_VARIABLE_KEY_REGEX, $value)) {
                        return __("To be used as customized content, '%s' can only be composed of letter(s), digit(s) and/or space(s).", $value);
                    }
                }
                $allowedDomainsRegex = '/^('.$allowedDomains.')$/';
                if (!preg_match($allowedDomainsRegex, $match['domain'])) {
                    return __("To be used as customized content, '%s' can only be either: %s or %s.", 
                            $match['domain'],
                            implode(', ', array_slice(explode('|', $allowedDomains), 0, -1)),
                            end(explode('|', $allowedDomains)));
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
        }
        return true;
    }

}