<?php

App::uses('MongoModel', 'Model');
App::uses('VusionValidation', 'Lib');
App::uses('VusionConst', 'Lib');

class PredefinedMessage extends MongoModel
{
    var $specific = true;
    var $name = 'PredefinedMessage';
    var $useDbConfig = 'mongo';
    
    function getModelVersion()
    {
        return '1';
    }
    
    
    function getRequiredFields($objectType=null)
    {
        return array(
            'name',
            'content'
            );
    }
    
    
    public $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'A predefined message must have a name.'
                ),
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This name already exists. Please choose another.'
                ),
            ),
        'content' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter some content for this message.'
                ),
            'validApostrophe' => array(
                'rule' => array('notRegex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                ),
            'validContentVariable' => array(
                'rule' => 'validContentVariable',
                'message' => 'noMessage'
                ),
            ),
        );
    
    
    public function isUnique($check)
    {
        if ($this->id) {
            $conditions = array('id'=>array('$ne'=> $this->id),'name' => $check['name']);
        } else {
            $conditions = array('name' => $check['name']);
        }
        $result = $this->find('count', array(
            'conditions' => $conditions
            ));
        return $result < 1;
    }
    
    
    public function notRegex($check, $regex=null) 
    {
        return VusionValidation::customNot($check['content'], $regex);
    }
    
    public function validContentVariable($check)
    {
        preg_match_all(VusionConst::CUSTOMIZE_CONTENT_MATCHER_REGEX, $check['content'], $matches, PREG_SET_ORDER);
        $allowed = array("domain", "key1", "key2", "key3", "otherkey");
        foreach ($matches as $match) {
            $match = array_intersect_key($match, array_flip($allowed));
            foreach ($match as $key=>$value) {
                if (!preg_match(VusionConst::CONTENT_VARIABLE_KEY_REGEX, $value)) {
                    return __("To be used as customized content, '%s' can only be composed of letter(s), digit(s) and/or space(s).", $value);
                }
            }
            if (!preg_match(VusionConst::CUSTOMIZE_CONTENT_DOMAIN_REGEX, $match['domain'])) {
                return __("To be used as customized content, '%s' can only be either 'participant' or 'contentVariable' or 'time'.", $match['domain']);
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
