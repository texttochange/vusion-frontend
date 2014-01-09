<?php
App::uses('MongoModel', 'Model');
App::uses('Action', 'Model');
App::uses('VusionConst', 'Lib');
App::uses('VusionValidation', 'Lib');
App::uses('DialogueHelper', 'Lib');


class Request extends MongoModel
{
    
    var $specific     = true;
    var $name         = 'Request';
    var $usedKeywords = array();
    
    
    function getModelVersion()
    {
        return '2';
    }
    
    
    function getRequiredFields($objectType=null)
    {
        return array(
            'keyword',
            'set-no-request-matching-try-keyword-only',
            'actions',
            'responses'
            );
    }


    // Construtor
    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        
        $this->Action         = new Action();
    }
    
    
    // Validate
    public $validate = array(
        'keyword' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please enter a keyword for this request.'
                ),
            'validFormat' => array(
                'rule' => VusionConst::KEYPHRASE_REGEX,
                'message' => VusionConst::KEYPHRASE_FAIL_MESSAGE
                ),
            'notUsedKeyword' => array(
                'rule' => 'notUsedKeyword',
                'message' => 'noMessage'
                )
            ),
        'set-no-request-matching-try-keyword-only' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The field set-no-request-matching-try-keyword-only is not set.'
                ),
            'validValue' => array(
                'rule' => array('inlist', array(0, 'no-request-matching-try-keyword-only')),
                'message' => 'The field no-request-matching-try-keyword-only value is not valid.'
                )
            ),
        'responses' => array(
            'validateArray' => array(
                'rule' => 'validateArray',
                'message' => 'The responses has to be an array.'
                ),
            'validateReponses' => array(
                'rule' => 'validateResponses',
                'message' => 'noMessage'
                )
            ),
        'actions' => array(
            'validateArray' => array(
                'rule' => 'validateArray',
                'message' => 'The actions has to be an array.'
                ),
            'validateAction' => array(
                'rule' => 'validateAction',
                'message' => 'noMessage'
                )
            )
        );
    
    public $validateResponse = array(
        'content' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Please enter a content for this response.'
                ),
            'noForbiddenApostrophe' => array(
                'rule' => array('customNot', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                ),
            'validContentVariable' => array(
                'rule' => 'validContentVariable',
                'message' => 'noMessage'
                ),
            )
        );

    
    public function notUsedKeyword($check)
    {
        $keywords = DialogueHelper::fromKeyphrasesToKeywords($check['keyword']);
        foreach($keywords as $keyword) {
            if (isset($this->usedKeywords[$keyword])) {
                return __("'%s' already used by a %s of program '%s'.",  $keyword, $this->usedKeywords[$keyword]['type'],  $this->usedKeywords[$keyword]['programName']);
            }
        }
        return true;
    }
    

    public function validateArray($check)
    {
        if (!is_array(reset($check))) {
            return false;
        }
        return true;
    }
    
    
    public function validateResponses($check)
    {
        $count = 0;
        foreach ($check['responses'] as $response) {
            $validationErrors = array();
            foreach ($this->validateResponse as $field => $rules) {
                foreach ($rules as $rule) {
                    if (is_array($rule['rule'])) {
                        $func = $rule['rule'][0];
                        $args = array_slice($rule['rule'], 1);
                        $args = $args[0];
                    } else {
                        $func = $rule['rule'];
                        $args = null;
                    }
                    
                    if (method_exists($this, $func)) {
                        $valid           = call_user_func_array(array($this, $func), array($response, $args));
                        $rule['message'] = $valid;
                    } else {
                        $valid = forward_static_call_array(array("VusionValidation", $func), array($response[$field], $args));
                    }
                    if (!is_bool($valid) || $valid == false) {
                        $validationErrors[$field][] = $rule['message']; 
                    }
                }
            }
            if ($validationErrors != array()) {
                $this->validationErrors['responses'][$count] = $validationErrors;
            }
            $count++;
        }
        if (isset($this->validationErrors['responses'])) {
            return false;
        }
        return true;
    }
    
    
    public function validateAction($check)
    {
        $count = 0;
        foreach ($check['actions'] as $action) {
            $this->Action->set($action);
            if (!$this->Action->validates()) {
                if (!isset($this->validationErrors['actions'][$count])) {
                    $this->validationErrors['actions'][$count] = array();
                }
                $this->validationErrors['actions'][$count] = $this->Action->validationErrors;
            }
            $count++;
        }
        if (isset($this->validationErrors['actions'])) {
            return false;
        }
        return true;
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
    
    
    var $findMethods = array(
        'count' => true,
        'first' => true,
        'all' => true,
        'keyword' => true,
        'keyphrase' => true,
        );
    
    
    public function beforeValidate()
    {
        parent::beforeValidate();
        
        $this->data['Request']['object-type'] = strtolower($this->name);
        
        $this->_setDefault('actions', array());
        $this->_setDefault('responses', array());
        $this->_setDefault('set-no-request-matching-try-keyword-only', 0);
        
        $this->_beforeValidateRequests();
        $this->_beforeValidateActions();
    }
    
    
    protected function _beforeValidateRequests()
    {
        $this->data['Request']['responses'] = array_map(
            function ($element) {
                $element['content'] = trim($element['content']); 
            return $element; }, 
            $this->data['Request']['responses']
        );
        $this->data['Request']['responses'] = array_filter(
            $this->data['Request']['responses'], 
            function ($element) {
                return ($element['content'] != '');
            }
        );
        $this->data['Request']['responses'] = array_values($this->data['Request']['responses']);
    }
    
    
    protected function _beforeValidateActions()
    {
        foreach ($this->data['Request']['actions'] as &$action) {
            $this->Action->set($action);
            $this->Action->beforeValidate();
            $action = $this->Action->getCurrent();
        }
    }
    
    
    protected function _findKeyword($state, $query, $results = array())
    {
        if ($state == 'before') {
            $keywords = explode(', ', $query['keywords']);
            foreach ($keywords as $keyword) {
                $conditions[] = array('Request.keyword' => new MongoRegex('/(,\s|^)'.$keyword.'($|\s|,)/i'));
            }
            if (count($conditions)>1)
                $query['conditions'] = array('$or'=>$conditions);
            else
            $query['conditions'] = $conditions[0];
            return $query;
        }
        if ($results) {
            $keywords = explode(', ', $query['keywords']);
            foreach ($keywords as $keyword) {
                if (preg_match('/(,\s|^)'.$keyword.'($|\s|,)/i', $results[0]['Request']['keyword']))
                    return $keyword;
            } 
        } 
        return null;
    }
    
    
    public function getKeywords()
    {
        $requests = $this->find('all');
        $keywords = array();
        foreach ($requests as $request) {
            $keyphrases = explode(', ', $request['Request']['keyword']);
            foreach ($keyphrases as $keyphrase) {
                $words = explode(' ', $keyphrase);
                array_push($keywords, $words[0]);
            }
        }
        return $keywords;
    }
    
    
    protected function _findKeyphrase($state, $query, $results = array())
    {
        if ($state == 'before') {
            $keywords = explode(', ', $query['keywords']);
            foreach ($keywords as $keyword) {
                $conditions[] = array('Request.keyword' => new MongoRegex('/(,\s|^)'.$keyword.'($|,)/i'));
            }
            if (count($conditions)>1)
                $conditions = array('$or'=>$conditions);
            else
            $conditions = $conditions[0];
            if (isset($query['excludeRequest']) and $query['excludeRequest'] != '') {
                $exclude    = array('Request._id' => array('$ne' => new MongoId($query['excludeRequest'])));
                $conditions = array(
                    '$and' =>  array($conditions, $exclude)
                    );
            }
            $query['conditions'] = $conditions;
            return $query;
        }
        if ($results) {
            $keywords = explode(', ', $query['keywords']);
            foreach ($keywords as $keyword) {
                if (preg_match('/(,\s|^)'.$keyword.'($|,)/i', $results[0]['Request']['keyword']))
                    return $keyword;
            } 
        } 
        return null;
    }


    public function saveRequest($request, $usedKeywords = array())
    {
        $this->usedKeywords = $usedKeywords;
        return $this->save($request);
    }
    
    
}
