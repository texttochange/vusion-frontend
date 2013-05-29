<?php
App::uses('Action', 'Model');
App::uses('FieldValueIncorrect', 'Lib');
App::uses('MissingField', 'Lib');
App::uses('VirtualModel', 'Model');
App::uses('VusionConst', 'Lib');

class Interaction extends VirtualModel
{
    var $name = 'interaction';
    var $version = '3'; 

    var $payload = array();

    var $fields = array(
        'interaction-id',
        'type-schedule',
        'type-interaction',
        'activated',
        'prioritized');

    public function __construct()
    {     
        parent::__construct();
        $this->Action = new Action();
        /*
        $unmatchingFeedbackFct = function($v) { 
             if (in_array($v, array('no-unmatching-feedback', 'program-unmatching-feedback', 'interaction-unmatching-feedback')))
                 return true;
             else 
                 throw new FieldValueIncorrect("Unmatching feedback cannot be $v"); 
        };
        
        $keywordFct = function($keywords) {
            $keywordRegex = '/^[a-zA-Z0-9]+(,(\s)?[a-zA-Z0-9]+)*$/';
            if (preg_match($keywordRegex, $keywords)) 
                return true;
            throw new FieldValueIncorrect("The keyword/alias '$keywords' is not valid.");
        };

        $this->SCHEDULE_TYPE = array(
            'fixed-time' => array('date-time' => function($v) {return true;}),
            'offset-days'=> array(
                'days' => function($v) { return ($v!=null);},
                'at-time'=> function($v) { return ($v!=null);}),
            'offset-time'=> array('minutes'=> function($v) { return ($v!=null);}),
            'offset-condition'=> array( 
                'offset-condition-interaction-id' => function($v) { return ($v!=null);}));
        
        $this->INTERACTION_TYPE = array(
            'announcement' => array(
                'content' => function($v) {return ($v!=null);}),
            'question-answer'=> array(
                'content'=> function($v) {return ($v!=null);},
                'keyword'=> $keywordFct,
                'set-use-template'=> function($v) {return true;},
                'type-question'=> function($v) {return ($v!=null);},
                'type-unmatching-feedback'=> $unmatchingFeedbackFct,
                'set-max-unmatching-answers'=> function($v) {return true;},
                'set-reminder'=> function($v) {return true;}),
            'question-answer-keyword'=> array(
                'content'=> function($v) {return ($v!=null);},
                'label-for-participant-profiling'=> function($v) {return ($v!=null);},
                'answer-keywords'=> function($v) {return ($v!=null);},
                'set-reminder'=> function($v) {return true;}));
        
        $this->QUESTION_TYPE = array(
            'closed-question'=> array(
                'label-for-participant-profiling'=> function($v) {return true;},
                'set-answer-accept-no-space'=> function($v) {return true;},
                'answers'=> function($v) {return true;}),
            'open-question'=> array(
                'answer-label'=> function($v) {return ($v!=null);},
                'feedbacks' => function($v) {return true;}));

        $actionsFct = function(&$actions) {
            $actionModel = new Action(); 
            foreach($actions as &$action) {
               $action = $actionModel->beforeValidate($action);
            };
            return true;
        };
        
        $this->MAX_UNMATCHING_ANSWER_FIELDS = array(
            'max-unmatching-answer-number'=> function($v) {return ($v>=1);},
            'max-unmatching-answer-actions'=> $actionsFct);

        $this->REMINDER_FIELDS = array(
            'type-schedule-reminder' => null,
            'reminder-number' => function($v) {return ($v>=1);},
            'reminder-actions' => $actionsFct);

        $this->REMINDER_SCHEDULE_TYPE = array(
            'reminder-offset-days' => array(
                'reminder-days' => function($v) {return ($v!=null);},
                'reminder-at-time' => function($v) {return ($v!=null);}),
            'reminder-offset-time' => array(
                'reminder-minutes' => function($v) {return ($v!=null);}));
        
        $this->ANSWER_KEYWORD = array(
            'keyword' => $keywordFct,
            'feedbacks' => function($v) {return true;},
            'answer-actions' => $actionsFct);

        $this->ANSWER = array(
            'choice' => function($v) {return ($v!=null);},
            'feedbacks' => function($v) {return true;},
            'answer-actions' => $actionsFct);
            */
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
        # Type Schedule
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
                'rule' => array('inlist', array('fixed-time', 'offset-days', 'offset-time', 'offset-condition')),
                'message' => 'Type Schedule has not a valid value.'
                )
            ),
        ## Type Schedule subtype
        'date-time' => array(
            'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'type-schedule', 'fixed-time'),
                'message' => 'Fixed time required a date-time.',
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
        # Type Interaction
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
                )
            ),
        ## Type Interaction Subtype
        'content' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldOrValue', 'type-interaction', 'announcement', 'question-answer', 'question-answer-keyword'),
                'message' => 'Fixed time required a date-time.',
                ),
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Content field cannot be empty.'
                ),
            'validApostrophe' => array(
                'rule' => array('notRegex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                )
            ),
        'keyword'=> array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-interaction', 'question-answer'),
                'message' => 'Question Answer required a keyword.',
                ),
            'validValue' => array(
                'rule' => array('regex', VusionConst::KEYWORD_REGEX),
                'message' => VusionConst::KEYWORD_FAIL_MESSAGE
                )
            ),
        'set-use-template'=> array( 
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-interaction', 'question-answer'),
                'message' => 'A set-use-template field is required.',
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
            ),
        'type-unmatching-feedback' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-interaction', 'question-answer'),
                'message' => 'A type-unmatching-feedback field is required.',
                ),
            'validValue' => array(
                'rule' => array('inList', array('no-unmatching-feedback', 'program-unmatching-feedback', 'interaction-unmatching-feedback')),
                'message' => 'Type Question value is not valid.',
                )
            ),
        'type-question'=> array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-interaction', 'question-answer'),
                'message' => 'Question Answer required a type-question.',
                ),
            'validValue' => array(
                'rule' => array('inList', array('closed-question', 'open-question')),
                'message' => 'Type Question value is not valid.',
                )
            ),
        'label-for-participant-profiling' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-interaction', 'question-answer-keyword'),
                'message' => 'A label-for-participant-profiling is required.',
                ),
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
        ### Type Interaction Subtype - Type Question Subtype
        'label-for-participant-profiling'=> array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-question', 'closed-question'),
                'message' => 'A label-for-participant-profiling is required.',
                ),
            ),
        'set-answer-accept-no-space'=> array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-question', 'closed-question'),
                'message' => 'A set-answer-accept-no-space field is required.',
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
            ),
        ### Unmatching Answers Subtype
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
        ### Reminder Subtype
        'type-schedule-reminder' => array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'set-reminder', 'reminder'),
                'message' => 'A type-schedule-reminder field is required.',
                ),
            'validValue' => array(
                'rule' => array('inList', array('reminder-offset-days', 'reminder-offset-time')),
                'message' => 'The value of type-schedule-reminder is not valid.',
                )
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
            ),
        ### Reminder Schedule Subtype
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
        # Other Interaction Fields
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
    
    public $validateAnswer = array(
        'choice' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Actived field cannot be empty.'
                )
            ),
        'feedbacks' => array(
            'validApostrophe' => array(
                'rule' => array('notRegex', VusionConst::APOSTROPHE_REGEX),
                'message' => VusionConst::APOSTROPHE_FAIL_MESSAGE
                )
            ),
        'answer-actions' => array( 
            'validateActions' => array(
                'rule' => 'validateActions',
                'message' => 'One Action is not valid.'
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
                )
            ),
        'feedbacks' => array(
            'validFeedback' => array(
                'rule' => 'validateFeedbacks',
                'message' => 'On Feedback is not valid.'
                )
            ),
        'answer-actions' => array( 
            'validateActions' => array(
                'rule' => 'validateActions',
                'message' => 'One Action is not valid.'
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
                )
            )
        );


    public function validateAnswers($field, $data)
    {
        if (!isset($data[$field])) {
            return true;
        }
        foreach($data[$field] as $answer) {
            $this->_validates($answer, $this->validateAnswer);
        }
        if (isset($this->validationErrors[$field])) {
            return false;
        }
        return true;
    }


    public function validateAnswerKeywords($field, $data)
    {
        if (!isset($data[$field])) {
            return true;
        }
        foreach($data[$field] as $answerKeyword) {
            $this->_validates($answerKeyword, $this->validateAnswerKeyword);
        }
        if (isset($this->validationErrors[$field])) {
            return false;
        }
        return true;        
    }


    public function validateFeedbacks($field, $data)
    {
        if (!isset($data[$field])) {
            return true;
        }
        foreach($data[$field] as $answer) {
            $this->_validates($answer, $this->validateFeedback);
        }
        if (isset($this->validationErrors[$field])) {
            return false;
        }
        return true;
    }

    
    public function validateActions($field, $data)
    {
        foreach($data[$field] as $action) {
            $this->Action->set($action);
            if (!$this->Action->validates()) {
                if (!isset($this->validationErrors['actions'])) {
                    $this->validationErrors['actions'] = array();
                }
                array_push($this->validationErrors['actions'], $this->Action->validationErrors[0]);
                return false;
            }
        }
        return true;
    }


    public function validates()
    {
        $interaction = $this->data;
        return $this->_validates($interaction, $this->validate);
    }

    protected function _validates($data, $validationRules)
    {
        foreach ($validationRules as $field => $validateField) {
            foreach ($validateField as $rule) {
                $defaultArgs = array($field, $data);
                if (is_array($rule['rule'])) {
                    $func = $rule['rule'][0];
                    $args = array_slice($rule['rule'], 1);
                } else {
                    $func = $rule['rule'];
                    $args = array();
                }
                $args = array_merge($defaultArgs, $args);
                if (!call_user_func_array(array($this, $func), $args)) {
                    if (!isset($this->validationErrors[$field])) {
                        $this->validationErrors[$field] = array();
                    }
                    array_push($this->validationErrors[$field], $rule['message']);
                    break;
                }
            }
        }
        if ($this->validationErrors != array()) {
            return false;
        }
        return true;
    }

/*
    public function trimArray($Input){
 
        if (!is_array($Input)) {
            if ($Input == null || $Input == ''){
                return null;
            }
            return trim(stripcslashes($Input));
        }
        return array_map(array($this,'TrimArray'), $Input);
    }
*/


    public function beforeValidate()
    {
        parent::beforeValidate();
        $this->_setDefault('interaction-id', uniqid());
        $this->_setDefault('activated', 0);
        $this->_setDefault('prioritized', null);

        if (!isset($this->data['type-interaction'])) {
            return false;
        }

        if ($this->data['type-interaction'] == 'announcement')
            return true;
 
        if ($this->data['type-interaction'] == 'question-answer') {
            $this->_setDefault('set-use-template', null);
            $this->_setDefault('type-unmatching-feedback', 'none');                        
            $this->_setDefault('set-reminder', null);
            $this->_setDefault('set-max-unmatching-answers', null);
            if ($this->data['set-max-unmatching-answers'] == 'max-unmatching-answers') {
                $this->_setDefault('max-unmatching-answer-actions', array());
            }
            if ($this->data['type-question'] == 'closed-question') {
                $this->_setDefault('set-answer-accept-no-space', null);
                $this->_setDefault('label-for-participant-profiling', null);
                $this->_setDefault('feedbacks', array());
                $this->_setDefault('answers', array());
                $this->_beforeValidateAnswers();
            }
        }
      
        if ($this->data['type-interaction'] == 'question-answer-keyword') {
            $this->_setDefault('set-reminder', null);
            $this->_setDefault('answer-keywords', array()); 
            $this->_beforeValidateAnswerKeywords();
        }

        if ($this->data['set-reminder'] == 'reminder') {
            $this->_setDefault('reminder-actions', array());
            $this->_beforeValidateActions(&$this->data['reminder-actions']);
        }

        return true;
    }

    protected function _beforeValidateActions($actions)
    {
        foreach($actions as &$action) {
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