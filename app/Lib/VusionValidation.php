<?php 
App::uses('VusionConst', 'Lib');
App::uses('Validation', 'Utility');


class VusionValidation extends Validation {


    public static function customNot($check, $regex=null)
    {
        return !self::custom($check, $regex);
    }
    
    
    public static function validContentVariable($check, $allowedDomains=VusionConst::CUSTOMIZE_CONTENT_DOMAIN_DEFAULT)
    {
        reset($check);
        $field = key($check);
        return VusionValidation::validCustomizeContent($field, $check, $allowedDomains);
    }


    public static function validCustomizeContent($field, $data, $allowedDomains)
    {
        if (isset($data[$field])) {
            preg_match_all(VusionConst::CUSTOMIZE_CONTENT_MATCHER_REGEX, $data[$field], $matches, PREG_SET_ORDER);
            $allowed = array("domain", "key1", "key2", "key3", "otherkey");
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
                } else if ($match['domain'] == 'context') {
                    $contextValidation = VusionValidation::validCustomizeContentContext($match);
                    if (is_string($contextValidation)) {
                        return $contextValidation;
                    }
                } 
            }
        }
        return true;
    }

    private static function validCustomizeContentContext($match) 
    {
        if (isset($match['otherkey'])) {
            return __("To be used in message, context only accept maximum of 3 keys. %s not allowed.", $match['otherkey']);
        }
        if ($match['key1'] == 'message') {
            if (!isset($match['key2']))  {
                return true;
            }
            if (is_numeric($match['key2'])) {
                if (intval($match['key2']) < 1 || intval($match['key2']) > 2) {
                    return __("On [context.message.x], x has to be either 1 or 2.");
                }
                if (isset($match['key3'])) {
                    return __("On [context.message.x.y], y is not allowed if x is a number.");
                }
            } else if (in_array($match['key2'], array('after', 'before'))) {
                if (!isset($match['key3'])) {
                    return __("On [context.message.after/before.x], only after/before .");
                } 
                if (!is_numeric($match['key3']) || intval($match['key3']) < 1 || intval($match['key3']) > 2) {
                    return __("On [context.message.after/before.x], x has to be either 1 or 2.");
                } 
            } else {
                    return __("On [context.message.x], x can be 'after', 'before' or a number.");
            }      
        }
        return true;
    }

}