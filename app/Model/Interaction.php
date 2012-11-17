<?php
App::uses('Action', 'Model');
App::uses('FieldValueIncorrect', 'Lib');
App::uses('MissingField', 'Lib');


class Interaction
{
    var $modelName = 'interaction';
    var $modelVersion = '2'; 

    var $payload = array();

    var $fields = array(
        'interaction-id',
        'type-schedule',
        'type-interaction',
        'activated');

    public function __construct()
    {        
        $unmatchingFeedbackFct = function($v) { 
             if (in_array($v, array('no-unmatching-feedback', 'program-unmatching-feedback', 'interaction-unmatching-feedback')))
                 return true;
             else 
                 throw new FieldValueIncorrect("Unmatching feedback cannot be $v"); 
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
            'announcement' => array('content' => function($v) {return ($v!=null);}),
            'question-answer'=> array(
                'content'=> function($v) {return ($v!=null);},
                'keyword'=> function($v) {return ($v!=null);},
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
            'keyword' => function($v) {return ($v!=null);},
            'feedbacks' => function($v) {return true;},
            'answer-actions' => $actionsFct);

        $this->ANSWER = array(
            'choice' => function($v) {return ($v!=null);},
            'feedbacks' => function($v) {return true;},
            'answer-actions' => $actionsFct);
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