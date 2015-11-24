<?php
App::uses('ProgramSpecificMongoModel', 'Model');
App::uses('Action', 'Model');
App::uses('VusionConst', 'Lib');
App::uses('VusionValidation', 'Lib');
App::uses('DialogueHelper', 'Lib');


class Request extends ProgramSpecificMongoModel
{
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
    }
    
    public function initializeDynamicTable($forceNew=false)
    {
        parent::initializeDynamicTable();
        $this->Action = new Action($this->databaseName);
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
        $keyphrases = DialogueHelper::cleanKeyphrases($check['keyword']);
        foreach($keyphrases as $keyphrase) {
            if (isset($this->usedKeywords[$keyphrase])) {
                return DialogueHelper::foundKeywordsToMessage(
                    $this->databaseName, $keyphrase, $this->usedKeywords[$keyphrase], $this->contactEmail);
            }
        }
        $keywords = DialogueHelper::cleanKeywords($check['keyword']);
        foreach($keywords as $keyword) {
            if (isset($this->usedKeywords[$keyword])) {
                return DialogueHelper::foundKeywordsToMessage(
                    $this->databaseName, $keyword, $this->usedKeywords[$keyword], $this->contactEmail);
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
        return VusionValidation::validContentVariable($check, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
    }
    
    
    var $findMethods = array(
        'count' => true,
        'first' => true,
        'all' => true,
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
    
    
    static public function hasRequestKeywords($request, $keywords)
    {
        if (isset($request['Request'])) {
            $request = $request['Request'];
        }
        if (!isset($request['keyword'])) {
            return array();
        }
        $foundKeywords = DialogueHelper::fromKeyphrasesToKeywords($request['keyword']);
        $usedKeywords = array_intersect($keywords, $foundKeywords);
        return $usedKeywords;
    }
    
    
    static public function hasRequestKeyphrases($request, $keyphrases)
    {
        if (isset($request['Request'])) {
            $request = $request['Request'];
        }
        if (!isset($request['keyword'])) {
            return array();
        }
        $foundKeyphrases = DialogueHelper::cleanKeyphrases($request['keyword']);
        $usedKeyphrases = array_intersect($keyphrases, $foundKeyphrases);
        return $usedKeyphrases;
    }
    
    
    static public function getRequestKeywords($request)
    {
        if (isset($request['Request'])) {
            $request = $request['Request'];
        }
        if (!isset($request['keyword'])) {
            return array();
        }
        return DialogueHelper::fromKeyphrasesToKeywords($request['keyword']);
    }
    
    
    static public function getRequestKeyphrases($request)
    {
        if (isset($request['Request'])) {
            $request = $request['Request'];
        }
        if (!isset($request['keyword'])) {
            return array();
        }
        return DialogueHelper::cleanKeyphrases($request['keyword']);
    }
    
    
    static public function getRequestId($request)
    {
        if (isset($request['Request'])) {
            $request = $request['Request'];
        }
        if (!isset($request['_id'])) {
            return null;
        }
        return $request['_id']."";
    }
    
    
    public function getKeywords()
    {
        $requests = $this->find('all');
        $keywords = array();
        foreach ($requests as $request) {
            $requestKeywords = DialogueHelper::fromKeyphrasesToKeywords($request['Request']['keyword']);
            $keywords = array_merge($keywords, $requestKeywords);
        }
        return array_values(array_unique($keywords));
    }
    
    
    public function useKeyword($keywords, $excludeRequest=null)
    {
        $params = array();
        if ($excludeRequest != null) {
            $params = array('conditions' => array('_id' => array('$ne' => new MongoId($excludeRequest))));   
        }
        $keywords = DialogueHelper::cleanKeywords($keywords);
        $usedKeywords = array();
        foreach ($this->find('all', $params) as $request) {
            $foundKeywords = Request::hasRequestKeywords($request, $keywords);
            $foundKeywords = array_flip($foundKeywords);
            foreach ($foundKeywords as $key => $value) {
                $foundKeywords[strval($key)] = array(
                    'request-id' => $request['Request']['_id']."",
                    'request-name' => $request['Request']['keyword']);
            }
            $usedKeywords = $usedKeywords + $foundKeywords;
        }
        if ($usedKeywords === array()) {
            return false;
        }
        return $usedKeywords;
    }
    
    
    public function useKeyphrase($keyphrases, $excludeRequest=null)
    {
        $params = array();
        if ($excludeRequest != null) {
            $params = array('conditions' => array('_id' => array('$ne' => new MongoId($excludeRequest))));   
        } 
        $keyphrases = DialogueHelper::cleanKeyphrases($keyphrases);
        $usedKeyphrases = array();
        foreach ($this->find('all', $params) as $request) {
            $foundKeyphrases = Request::hasRequestKeyphrases($request, $keyphrases);
            $foundKeyphrases = array_flip($foundKeyphrases);
            foreach ($foundKeyphrases as $key => $value) {
                $foundKeyphrases[strval($key)] = array(
                    'request-id' => $request['Request']['_id']."",
                    'request-name' => $request['Request']['keyword']);
            }
            $usedKeyphrases = $usedKeyphrases + $foundKeyphrases;
        }
        if ($usedKeyphrases === array()) {
            return false;
        }
        return $usedKeyphrases;
    }
    
    
    public function getRequestFilterOptions()
    {
        $requests = $this->find('all');
        $requestFilterOptions = array();
        foreach($requests as $request) {
            $requestFilterOptions[$request['Request']['_id'].''] = $request['Request']['keyword'];
        }
        return $requestFilterOptions;
    }
    
    
    public function saveRequest($request, $usedKeywords = array())
    {
        $this->create();
        if (isset($request['Request']['_id'])) {
            $this->id = $request['Request']['_id'];
        }
        $this->usedKeywords = $usedKeywords;
        return $this->save($request);
    }


    public function fromRequestIdsToKeywords($requestIds)
    {
        $keyword = array();
        $requests = $this->find('all', array('fields' => array('_id', 'keyword')));
        foreach ($requests as $request) {
            $keywords[$request['Request']['_id'].''] = $request['Request']['keyword'];
        } 
        foreach ($requestIds as &$requestId) {
            $requestId['request-name'] = $keywords[$requestId['request-id'].''];
        }
        return $requestIds;
    }
    
    
}
