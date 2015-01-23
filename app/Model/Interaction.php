<?php
App::uses('Action', 'Model');
App::uses('FieldValueIncorrect', 'Lib');
App::uses('MissingField', 'Lib');
App::uses('VirtualModel', 'Model');
App::uses('VusionConst', 'Lib');
App::uses('DialogueHelper', 'Lib');
App::uses('VusionValidation', 'Lib');


class Interaction extends VirtualModel
{
    var $name       = 'interaction';
    var $version    = '5'; 
    var $databaName = null;    
    
    var $payload = array();
    
    var $fields = array(
        'interaction-id',
        'type-schedule',
        'type-interaction',
        'activated',
        'prioritized');
    
    
    //DatabaseName required for Keyword Validation
    public function __construct($databaseName)
    {     
        parent::__construct();
        $this->Action = new Action();
        $this->databaseName = $databaseName;
    }
    
    
    public $validate = array(
        'interaction-id' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'Interaction Id field is missing.'
                ),
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Interaction Id field cannot be empty.'    
                )
            ),
        // Type Schedule
        'type-schedule' => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'Type Schedule field is missing.'
                ),
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Type Schedule field cannot be empty.'
                ),
            'validValue' => array(
                'rule' => array(
                    'inlist', array(
                        'fixed-time', 'offset-days', 'offset-time', 'offset-condition')),
                'message' => 'Type Schedule has not a valid value.'
                ),
            'valueRequireFields' => array(
                'rule' => array(
                    'valueRequireFields', array(
                        'fixed-time' => array('date-time'),
                        'offset-days' => array('days', 'at-time'),
                        'offset-time' => array('minutes'),
                        'offset-condition' => array(
                            'offset-condition-interaction-id',
                            'offset-condition-delay'))),
                'message' => 'Type schedule required field are not present.'
                )
            ),
        // Type Schedule subtype
        'date-time' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-schedule', 'fixed-time'),
                'message' => 'Fixed time required a date-time.',
                ),
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The date time has to be set.'
                ),
            'validFormat' => array(
                'rule' => array('regex', VusionConst::DATE_TIME_REGEX),
                'message' => VusionConst::DATE_TIME_FAIL_MESSAGE
                )
            ),
        'days' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-schedule', 'offset-days'),
                'message' => 'Offset-day required a day.',
                )
            ),
        'at-time' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-schedule', 'offset-days'),
                'message' => 'Offset-day required a at-time field.',
                )
            ),
        'minutes' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-schedule', 'offset-time'),
                'message' => 'Minutes required a offset-time.',
                )
            ),
        'offset-condition-interaction-id' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-schedule', 'offset-condition'),
                'message' => 'Schedule Condition required a offset-condition-interaction-id.',
                )
            ),
        'offset-condition-delay' => array(
            'isInt' => array(
                'rule' => array('regex', '/^[0-9]*$/'),
                'message' => 'The delay only accept full minutes.',
                ),
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-schedule', 'offset-condition'),
                'message' => 'Schedule Condition required a offset-condition-delay.',
                )
            ),
        // Type Interaction
        'type-interaction'  => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'Type Interaction field is missing.'
                ),
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Type Interaction value cannot be empty.'
                ),
            'validValue' => array(
                'rule' => array('inList', array('announcement', 'question-answer', 'question-answer-keyword')),
                'message' => 'Type Interaction value is not valid.'
                ),
            'valueRequireFields' => array(
                'rule' => array(
                    'valueRequireFields', array(
                        'announcement' => array('content'),
                        'question-answer' => array(
                            'content', 
                            'keyword', 
                            'set-use-template', 
                            'type-question', 
                            'type-unmatching-feedback',
                            'set-matching-answer-actions',
                            'set-max-unmatching-answers', 
                            'set-reminder'),
                        'question-answer-keyword' => array(
                            'content', 
                            'label-for-participant-profiling', 
                            'answer-keywords', 
                            'set-reminder'),
                        'message' => 'Type interaction required fields are missing.'
                        )
                    )
                )
            ),
        // Type Interaction Subtype
        'content' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldOrValue', 'type-interaction', 'announcement', 'question-answer', 'question-answer-keyword'),
                'message' => 'The interaction required a content.',
                ),
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Content field cannot be empty.'
                ),
            'validApostrophe' => array(
                'rule' => array('notRegex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                ),
            'validCustomizeContent' => array(
                'rule' => array('validCustomizeContent', VusionConst::CUSTOMIZE_CONTENT_DOMAIN_DEFAULT),
                'message' => 'noMessage'
                ),
            ),
        'keyword'=> array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-interaction', 'question-answer'),
                'message' => 'Question Answer required a keyword.',
                ),
            'validValue' => array(
                'rule' => array('regex', VusionConst::KEYWORD_REGEX),
                'message' => VusionConst::KEYWORD_FAIL_MESSAGE
                ),
            'notUsedKeyword' => array(
                'rule' => 'notUsedKeyword',
                'message' => null,
                ),
            'notUsedKeywordNoSpace' => array(
                'rule' => 'notUsedKeywordNoSpace',
                'message' => null,
                ),
            ),
        'set-use-template'=> array( 
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-interaction', 'question-answer'),
                'message' => 'A set-use-template field is required.',
                ),
            ),
        'set-matching-answer-actions'=> array(
            'requiredConditional' => array(
                'rule' => array(
                    'requiredConditionalFieldValue', 
                    'type-interaction', 
                    'question-answer'),
                'message' => 'A set-matching-answer-action field is required.',
                ),
            'validValue' => array(
                'rule' => array('inList', array(null, 'matching-answer-actions')),
                'message' => 'Type unmatching feedback is not valid.',
                )
            ),  
        'matching-answer-actions' => array(
            'validateActions' => array(
                'rule' => 'validateActions',
                'message' => null
                ),
            ),
        'set-max-unmatching-answers' => array( 
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-interaction', 'question-answer'),
                'message' => 'A set-max-unmatching-answer field is required.',
                ),
            ),
        'set-reminder'=> array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldOrValue', 'type-interaction', 'question-answer', 'question-answer-keyword'),
                'message' => 'A set-reminder field is required.',
                ),
            'valueRequireFields' => array(
                'rule' => array(
                    'valueRequireFields', array(
                        'reminder' => array(
                            'type-schedule-reminder',
                            'reminder-number',
                            'reminder-actions'))),
                'message' => 'A reminder required field.',
                ),
            ),
        'type-unmatching-feedback' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-interaction', 'question-answer'),
                'message' => 'A type-unmatching-feedback field is required.',
                ),
            'validValue' => array(
                'rule' => array('inList', array('no-unmatching-feedback', 'program-unmatching-feedback', 'interaction-unmatching-feedback')),
                'message' => 'Type unmatching feedback is not valid.',
                )
            ),
        'type-question'=> array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-interaction', 'question-answer'),
                'message' => 'Question Answer required a type-question.',
                ),
            'validValue' => array(
                'rule' => array('inList', array('closed-question', 'open-question')),
                'message' => 'Please select a valid type of Question.',
                )
            ),
        'answer-keywords' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-interaction', 'question-answer-keyword'),
                'message' => 'A answer-keywords is required.',
                ),
            'validValues' => array(
                'rule' => 'validateAnswerKeywords',
                'message' => 'One of the Answer Keyword is not valide.'
                )
            ),
        // Type Interaction Subtype - Type Question Subtype
        'label-for-participant-profiling'=> array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldOrKeyValue', array(
                    'type-interaction'=>'question-answer-keyword',
                    'type-question' => 'closed-question')),
                'message' => 'A label-for-participant-profiling is required.',
                ),
            ),
        'set-answer-accept-no-space'=> array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-question', 'closed-question'),
                'message' => 'A set-answer-accept-no-space field is required.',
                ),
            'validValue' => array(
                'rule' => array('inList', array('answer-accept-no-space', null)),
                'message' => 'Unvalid choice.', 
                ),
            ),
        'answers'=> array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-question', 'closed-question'),
                'message' => 'A answers field is required.',
                ),
            'validValues' => array(
                'rule' => 'validateAnswers',
                'message' => 'One of the Answers is not valide.'
                )
            ),
        'answer-label' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-question', 'open-question'),
                'message' => 'A answer-label field is required.',
                ),
            ),
        'feedbacks' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-question', 'open-question'),
                'message' => 'A feedbacks field is required.',
                ),
            'validValues' => array(
                'rule' => 'validateFeedbacks',
                'message' => 'noMessage')
            ),
        // Unmatching Answers Subtype
        'max-unmatching-answer-number' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'set-max-unmatching-answers', 'max-unmatching-answers'),
                'message' => 'A max-unmatching-answer-number field is required.',
                ),
            ),
        'max-unmatching-answer-actions' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'set-max-unmatching-answers', 'max-unmatching-answers'),
                'message' => 'A max-unmatching-answer-actions field is required.',
                ),
            ),
        // Reminder Subtype
        'type-schedule-reminder' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'This field has to be set.',
                ),
            'validValue' => array(
                'rule' => array('inList', array('reminder-offset-days', 'reminder-offset-time')),
                'message' => 'The value of type-schedule-reminder is not valid.',
                ),
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'set-reminder', 'reminder'),
                'message' => 'A type-schedule-reminder field is required.',
                ),
            ),
        'reminder-number' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'set-reminder', 'reminder'),
                'message' => 'A reminder-number field is required.',
                ),
            ),
        'reminder-actions' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'set-reminder', 'reminder'),
                'message' => 'A reminder-actions field is required.',
                ),
            'validValue' => array(
                'rule' => 'validateActions',
                'message' => null
                ),
            ),
        // Reminder Schedule Subtype
        'reminder-days' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-schedule-reminder', 'reminder-offset-days'),
                'message' => 'A reminder-days field is required.',
                ),
            ),
        'reminder-at-time' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-schedule-reminder', 'reminder-offset-days'),
                'message' => 'A reminder-at-time field is required.',
                ),
            ),
        'reminder-minutes' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-schedule-reminder', 'reminder-offset-time'),
                'message' => 'A reminder-minutes field is required.',
                ),
            ),
        // type-unmatching-feedback
        'unmatching-feedback-content' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-unmatching-feedback', 'interaction-unmatching-feedback'),
                'message' => 'Custom unmatching feedback must have content.',
                ),
            'validApostrophe' => array(
                'rule' => array('notRegex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                ),
            'validCustomizeContent' => array(
                'rule' => array('validCustomizeContent', VusionConst::CUSTOMIZE_CONTENT_DOMAIN_DEFAULT),
                'message' => 'noMessage'
                ),
            ),
        // Other Interaction Fields
        'activated'  => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'Activated field is missing.'
                ),
            'validValue' => array(
                'rule' => array('inList', array(0, 1)),
                'message' => 'Actived field value is not valid.'
                )
            ),
        'prioritized'  => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'Prioritized field is missing.'
                ),
            'validValue' => array(
                'rule' => array('inList', array(null, 'prioritized')),
                'message' => 'Prioritized field value is not valid.'
                )
            )
        );
    
    
    public $validateReminderAction = array(
        'type-action' => array( 
            'validateActions' => array(
                'rule' => 'validateActions',
                'message' => null
                ),
            )
        );
    
    
    //TODO: need clever validation over:
    // 1) on numbering of choice
    // 2) notUsedKeyword when ticking allow no space
    public $validateAnswer = array(
        'choice' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Actived field cannot be empty.'
                ),
            'validValue' => array(
                'rule' => array('regex', VusionConst::CHOICE_REGEX),
                'message' => VusionConst::CHOICE_FAIL_MESSAGE
                ),
            ),
        'feedbacks' => array(
            'validFeedback' => array(
                'rule' => 'validateFeedbacks',
                'message' => null
                )
            ),
        'answer-actions' => array( 
            'validateActions' => array(
                'rule' => 'validateActions',
                'message' => null
                ),
            )
        );
    
    
    public $validateAnswerKeyword = array(
        'keyword' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'keyword field cannot be empty.'
                ),  
            'validValue' => array(
                'rule' => array('regex', VusionConst::KEYWORD_REGEX),
                'message' => VusionConst::KEYWORD_FAIL_MESSAGE
                ),
            'notUsedKeyword' => array(
                'rule' => 'notUsedKeyword',
                'message' => null,
                )
            ),
        'feedbacks' => array(
            'validFeedback' => array(
                'rule' => 'validateFeedbacks',
                'message' => null
                )
            ),
        'answer-actions' => array( 
            'validateActions' => array(
                'rule' => 'validateActions',
                'message' => null
                ),
            )
        );
    
    
    public $validateFeedback = array(
        'content' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Content field cannot be empty.'
                ),
            'validApostrophe' => array(
                'rule' => array('notRegex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                ),
            'validCustomizeContent' => array(
                'rule' => array('validCustomizeContent', VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE),
                'message' => 'noMessage'
                ),
            )
        );
    
    
    public function validCustomizeContent($field, $data, $allowedDomain)
    {
        return VusionValidation::validCustomizeContent($field, $data, $allowedDomain);
    }
    
    public function validateAnswers($field, $data)
    {
        return $this->validList($field, $data, $this->validateAnswer); 
    }
    
    
    public function validateAnswerKeywords($field, $data)
    {
        return $this->validList($field, $data, $this->validateAnswerKeyword);      
    }
    
    
    public function validateFeedbacks($field, $data)
    {
        return $this->validList($field, $data, $this->validateFeedback);
    }
    
    
    public function validateActions($field, $data)
    {
        if (isset($data[$field])) {
            $count            = 0;
            $validationErrors = array();
            foreach ($data[$field] as $action) {
                $this->Action->set($action);
                if (!$this->Action->validates()) {
                    $validationErrors[$count] = $this->Action->validationErrors;
                }
                $count++;
            }
            if ($validationErrors != array()) {
                return $validationErrors;
            }
        }
        return true;
    }
    
    
    public function setUsedKeywords($usedKeywords = array())
    {
        $this->usedKeywords = $usedKeywords;
    }
    
    
    public function notUsedKeyword($field, $data)
    {
        if (!isset($data[$field])) {
            return true;
        }
        $keywords = DialogueHelper::cleanKeywords($data[$field]);
        foreach($keywords as $keyword) {
            if (isset($this->usedKeywords[$keyword])) {
                return DialogueHelper::foundKeywordsToMessage($this->databaseName, $keyword, $this->usedKeywords[$keyword]);
            }
        }
        return true;
    }
    
    
    public function notUsedKeywordNoSpace($field, $data)
    {
        if (!isset($data[$field]) || !isset($data['set-answer-accept-no-space'])) {
            return true;
        }
        $keywords = DialogueHelper::cleanKeywords($data[$field]);
        $noSpaceKeywords = Interaction::getInteractionNoSpaceKeywords($data, $keywords);
        foreach ($noSpaceKeywords as $keyword) {
            if (isset($this->usedKeywords[$keyword])) {
                return DialogueHelper::foundKeywordsToMessage($this->databaseName, $keyword, $this->usedKeywords[$keyword]);
            }
        }
        return true;
    }
    
    
    static public function hasInteractionKeywords($interaction, $keywords)
    {
        $interactionKeywords = Interaction::getInteractionKeywords($interaction);
        return array_intersect($keywords, $interactionKeywords);
    }
    
    
    static public function getInteractionKeywords($interaction)
    {
        if (isset($interaction['keyword'])) {
            $keywords = DialogueHelper::cleanKeywords($interaction['keyword']);
            $noSpacedCurrentKeywords = Interaction::getInteractionNoSpaceKeywords($interaction, $keywords);
            return array_merge($keywords, $noSpacedCurrentKeywords); 
        } elseif (isset($interaction['answer-keywords'])) {
            $keywords = array();
            foreach ($interaction['answer-keywords'] as $answer) {
                if (isset($answer['keyword'])) {
                    $foundKeywords = DialogueHelper::cleanKeywords($answer['keyword']);
                    $keywords = array_merge($keywords, $foundKeywords);
                }
            }
            return $keywords;
        }
        return array();
    }
    
    
    static public function getInteractionNoSpaceKeywords($interaction, $keywords)
    {
        $usedKeywords = array();
        if (isset($interaction['set-answer-accept-no-space']) && $interaction['set-answer-accept-no-space'] != null && $interaction['answers']) {
            foreach($interaction['answers'] as $answer) {
                foreach($keywords as $keyword) {
                    $usedKeywords[] = $keyword . DialogueHelper::cleanKeyword($answer['choice']);
                }
            }
        }
        return $usedKeywords;
    }
    
    
    static public function replaceLocalIds(&$interactions)
    {
        $localIds = array();
        foreach ($interactions as &$interaction) {
            if (isset($interaction['interaction-id']) && preg_match('/^local:/', $interaction['interaction-id'])) {
                $newId = uniqid();
                $localIds[$interaction['interaction-id']] = $newId;
                $interaction['interaction-id'] = $newId;
            }
        }
        
        foreach ($interactions as &$interaction) {
            if (isset($interaction['offset-condition-interaction-id'])) {
                if (array_key_exists($interaction['offset-condition-interaction-id'], $localIds)) {
                    $interaction['offset-condition-interaction-id'] = $localIds[$interaction['offset-condition-interaction-id']];
                }
            }
        }
    }
    
    
    static public function hasAnswer($interaction, $answering)
    {
        if (!in_array($interaction['type-interaction'], array('question-answer', 'question-answer-keyword' ))) {
            return array('interaction-id' => __("This interaction is not a question."));
        }
        if ($interaction['type-interaction'] === 'question-answer') {
            if ($interaction['type-question'] === 'open-question') {
                if (in_array($answering, array(null, ''))) {
                    return array('answer' => __("The interaction doesn't accept empty answer."));
                }
                return true;
            } else if ($interaction['type-question'] === 'closed-question') {
                foreach ($interaction['answers'] as $answer) {
                    if (DialogueHelper::keywordCmp($answer['choice'], $answering)) {
                        return true;
                    }
                }
                return array('answer' => __("The interaction has not such answer: %s.", $answering));
            }
        } else if ($interaction['type-interaction'] === 'question-answer-keyword') {
            foreach ($interaction['answer-keywords'] as $answerKeyword) {
                if (DialogueHelper::keywordCmp($answerKeyword['keyword'], $answering)) {
                    return true;
                }
            }
            return array('answer' => __("The interaction has not such answer: %s.", $answering));
        }
    }
    
    
    public function beforeValidate()
    {
        parent::beforeValidate();
        $this->_setDefault('interaction-id', uniqid());
        $this->_setDefault('activated', 0);
        $this->data['activated'] = intval($this->data['activated']);
        $this->_setDefault('prioritized', null);
        
        $this->_setDefault('type-interaction', null);
        $this->_setDefault('type-schedule', null);
        if ($this->data['type-schedule'] == 'offset-condition') {
            $this->_setDefault('offset-condition-delay', '0');
        }
        
        if (isset($this->data['date-time'])) {
            $this->data['date-time'] = DialogueHelper::convertDateFormat($this->data['date-time']);
        }
        //Exit the function in case of announcement
        if (!in_array($this->data['type-interaction'], array('question-answer', 'question-answer-keyword')))
            return true;
        
        if ($this->data['type-interaction'] == 'question-answer') {
            $this->_setDefault('type-question', null);
            $this->_setDefault('set-use-template', null);
            $this->_setDefault('type-unmatching-feedback', 'no-unmatching-feedback');                        
            $this->_setDefault('set-reminder', null);
            $this->_setDefault('set-matching-answer-actions', null);
            if ($this->data['set-matching-answer-actions'] == 'matching-answer-actions') {
                $this->_setDefault('matching-answer-actions', array());
                $this->_beforeValidateActions(&$this->data['matching-answer-actions']);
            }
            $this->_setDefault('set-max-unmatching-answers', null);
            if ($this->data['set-max-unmatching-answers'] == 'max-unmatching-answers') {
                $this->_setDefault('max-unmatching-answer-actions', array());
            }
            if ($this->data['type-question'] == 'closed-question') {
                $this->_setDefault('set-answer-accept-no-space', null);
                $this->_setDefault('label-for-participant-profiling', null);
                $this->_setDefault('answers', array());
                $this->_beforeValidateAnswers();
            } elseif ($this->data['type-question'] == 'open-question') {
                $this->_setDefault('answer-label', null);
                $this->_setDefault('feedbacks', array());
            }
        }
        
        if ($this->data['type-interaction'] == 'question-answer-keyword') {
            $this->_setDefault('set-reminder', null);
            $this->_setDefault('answer-keywords', array()); 
            $this->_beforeValidateAnswerKeywords();
        }
        
        if ($this->data['set-reminder'] == 'reminder') {
            $this->_setDefault('reminder-actions', array());
            $this->_setDefault('type-schedule-reminder', null);
            $this->_setDefault('reminder-number', null);
            $this->_beforeValidateActions(&$this->data['reminder-actions']);
        }
        
        return true;
    }
    
    
    protected function _beforeValidateActions($actions)
    {
        foreach ($actions as &$action) {
            $this->Action->set($action);
            $this->Action->beforeValidate();
            $action = $this->Action->getCurrent();
        }
    }
    
    
    protected function _beforeValidateAnswerKeywords() 
    {
        foreach ($this->data['answer-keywords'] as &$answer) {
            if (!isset($answer['feedbacks']))
                $answer['feedbacks'] = array();
            if (!isset($answer['answer-actions']))
                $answer['answer-actions'] = array();
            $this->_beforeValidateActions(&$answer['answer-actions']);
        }
    }
    
    
    protected function _beforeValidateAnswers() 
    {
        foreach ($this->data['answers'] as &$answer) {
            if (!isset($answer['feedbacks']))
                $answer['feedbacks'] = array();
            if (!isset($answer['answer-actions']))
                $answer['answer-actions'] = array();
            $this->_beforeValidateActions(&$answer['answer-actions']);
        }
    }
    
    
}