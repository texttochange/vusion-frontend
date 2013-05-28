<?php
App::uses('Action', 'Model');
App::uses('FieldValueIncorrect', 'Lib');
App::uses('MissingField', 'Lib');
App::uses('VirtualModel', 'Model');
App::uses('VusionConst', 'Lib');

class Interaction extends VirtualModel
{
    var $modelName = 'interaction';
    var $modelVersion = '3'; 

    var $payload = array();

    var $fields = array(
        'interaction-id',
        'type-schedule',
        'type-interaction',
        'activated',
        'prioritized');

    public function __construct()
    {        
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
                )
            'validValue' => array(
                'rule' => array('inlist', 'fixed-time', 'offset-days', 'offset-time', 'offset-condition')
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
                )
            'validValue' => array(
                'rule' => array('inList', 'announcement', 'question-answer', 'question-answer-keyword'),
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
                'message' => 'Question Answer required a set-use-template.',
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
                'rule' => array('inList', 'no-unmatching-feedback', 'program-unmatching-feedback', 'interaction-unmatching-feedback'),
                'message' => 'Type Question value is not valid.',
                )
            ),
        'type-question'=> array(
            'requiredConditional' => array(
                'rule' => array('requiredConditionalFieldValue', 'type-interaction', 'question-answer'),
                'message' => 'Question Answer required a type-question.',
                ),
            'validValue' => array(
                'rule' => array('inList', 'closed-question', 'open-question'),
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
                'rule' => array('inList', 'reminder-offset-days', 'reminder-offset-time')
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
        'reminder-at-time' array(
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
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Actived field cannot be empty.'
                )
            ),
        'prioritized'  => array(
            'required' => array(
                'rule' => 'required',
                'message' => 'Prioritized field is missing.'
                ),
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Prioritized field cannot be empty.'
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
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Content field cannot be empty.'
                ),
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
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'Content field cannot be empty.'
                ),
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


    public function validateAnswers()
    {

    }


    public function validateAnswerKeywords()
    {
        
    }


    public function validates()
    {
        $interaction = $this->data;
        foreach ($this->validate as $field => $validateField) {
            foreach ($validateField as $rule) {
                $defaultArgs = array($field, $interaction);
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


    public function trimArray($Input){
 
        if (!is_array($Input)) {
            if ($Input == null || $Input == ''){
                return null;
            }
            return trim(stripcslashes($Input));
        }
        return array_map(array($this,'TrimArray'), $Input);
    }


    public function beforeValidate($interaction)
    {
        $interaction = $this->trimArray($interaction);

        $interaction['object-type'] = $this->modelName;        
        $interaction['model-version'] = $this->modelVersion;

        foreach($this->fields as $field) {
            if (!isset($interaction[$field])) {
                if ($field=='interaction-id') {
                    $interaction['interaction-id'] = uniqid();  
                } elseif ($field=='activated') {
                    $interaction['activated'] = 0;
                } elseif ($field=='prioritized') {
                    $interaction['prioritized'] = null;
                } else {
                    throw new MissingField("$field is missing in an Interaction.");
                }
            }
        }
        
        foreach($this->SCHEDULE_TYPE[$interaction['type-schedule']] as $field => $check) {
            if (!call_user_func($check, $interaction[$field])){
                throw new MissingField("$field has incorrect value in an interaction.");
            }
        }
        foreach($this->INTERACTION_TYPE[$interaction['type-interaction']] as $field => $check) {
            if (!isset($interaction[$field])) {
                if ($field=='set-use-template') {
                    $interaction['set-use-template'] = null;
                } elseif ($field=='type-unmatching-feedback') {
                    $interaction['type-unmatching-feedback'] = 'none';                        
                } elseif ($field=='set-reminder') {
                    $interaction['set-reminder'] = null;
                } elseif ($field=='set-max-unmatching-answers') {
                    $interaction['set-max-unmatching-answers'] = null;
                } else {
                    throw new MissingField("$field is missing in an Interaction.");
                }
            }
            if (!call_user_func($check, $interaction[$field])){
                throw new FieldValueIncorrect("$field has incorrect value in an interaction.");
            }
        }
        if ($interaction['type-interaction'] == 'announcement') {
            return $interaction;
        }
        if ($interaction['type-interaction'] == 'question-answer') {
            foreach($this->QUESTION_TYPE[$interaction['type-question']] as $field => $check) {
                if (!isset($interaction[$field])) {
                    if ($field=='set-answer-accept-no-space') {
                        $interaction['set-answer-accept-no-space'] = null;
                    } elseif ($field=='label-for-participant-profiling') {
                        $interaction['label-for-participant-profiling'] = null;
                    } elseif ($field=='feedbacks') {
                        $interaction['feedbacks'] = array();
                    } else {
                        throw new MissingField("$field is missing in an Interaction.");
                    }    
                } 
                if (!call_user_func($check, $interaction[$field])){
                    throw new FieldValueIncorrect("$field has incorrect value in an interaction.");
                }
            }
            if ($interaction['set-max-unmatching-answers'] == 'max-unmatching-answers') {
                foreach($this->MAX_UNMATCHING_ANSWER_FIELDS as $field => $check) {
                    if (!isset($interaction[$field])) {
                        if ($field == 'max-unmatching-answer-actions') {
                            $interaction['max-unmatching-answer-actions'] = array();
                        } else {
                            throw new MissingField("$field is missing in the interaction.");
                        }
                    }
                    if (is_callable($check) && !call_user_func($check, &$interaction[$field])) {
                        throw new FieldValueIncorrect("$field has incorrect value in an interaction.");
                    }
                }
            }
        }

        if ($interaction['set-reminder'] == 'reminder') {
            foreach($this->REMINDER_FIELDS as $field => $check) {
                if (!isset($interaction[$field])){
                    if ($field == 'reminder-actions') {
                        $interaction['reminder-actions'] = array();
                    } else {
                        throw new MissingField("$field is missing in the interaction.");
                    }
                }
                if (is_callable($check) && !call_user_func($check, &$interaction[$field])) {
                    throw new FieldValueIncorrect("$field has incorrect value in an interaction.");
                }
            }
            foreach($this->REMINDER_SCHEDULE_TYPE[$interaction['type-schedule-reminder']] as $field => $check){
                if (!call_user_func($check, &$interaction[$field])){
                    throw new FieldValueIncorrect("$field has incorrect value in an interaction.");
                }
            }
        }

        //Specific Answer check for closed and multikeyword question
        if ($interaction['type-interaction'] == 'question-answer-keyword') {
            if (is_array($interaction['answer-keywords'])) {
                $interaction['answer-keywords'] = $this->beforeValidateAnswers($interaction['answer-keywords'], $this->ANSWER_KEYWORD);
            } else {
                $interaction['answer-keywords'] = array();                
            }
        } elseif ($interaction['type-interaction'] == 'question-answer' && $interaction['type-question'] == 'closed-question') {
            if (is_array($interaction['answers'])) {
                $interaction['answers'] = $this->beforeValidateAnswers($interaction['answers'], $this->ANSWER);
            } else {
                $interaction['answers'] = array();                
            }
        }
            
        return $interaction;
    }

    private function beforeValidateAnswers(&$answers, $validateRules) 
    {
        foreach($answers as &$answer) {
            foreach($validateRules as $field => $check) {
                if (!isset($answer[$field])) {
                    if ($field == 'feedbacks' or $field == 'answer-actions') { 
                        $answer[$field] = array();
                    }
                }
                if (call_user_func($check, &$answer[$field]) == false){
                    throw new FieldValueIncorrect("$field has an incorrect value in an Answer.");
                }
            }
        }
        return $answers;
    }


}