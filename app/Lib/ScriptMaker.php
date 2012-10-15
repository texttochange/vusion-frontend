<?php

class ScriptMaker
{

    
    public function getOneDialogueWithKeyword($keyword="keyword")
    {
        $dialogue['Dialogue'] = array(
            'dialogue-id'=> 'script.dialogues[0]',
            'interactions'=> array(
                array(
                    'type-interaction' => 'annoucement', 
                    'content' => 'hello',
                    'keyword' => 'feel',
                    'interaction-id' => 'script.dialogues[0].interactions[0]'
                    ),	
                array(
                    'type-interaction' => 'question-answer', 
                    'content' => 'how are you', 
                    'keyword' => $keyword,
                    'type-question'=>'close-question',
                    'answers'=> array(
                        0 => array('choice'=>'Good'),
                        1 => array('choice'=>'Bad')
                        ),
                    'interaction-id' => 'script.dialogues[0].interactions[1]'
                    )
                )
            );

        return $dialogue;
    }


    public function getOneDialogueAnwerNoSpaceSupported($keyword="keyword")
    {
        $dialogue = $this->getOneDialogueWithKeyword($keyword);
        $dialogue['Dialogue']['interactions'][1]['answer-accept-no-space'] =  'answer-accept-no-space';
        return $dialogue;

    }


    public function getOneDialogue($keyword='keyword')
    {
        $dialogue['Dialogue'] = array(
            'name' => 'my dialogue',
            'auto-enrollment' => 'none',
            'dialogue-id' => null,
            'interactions'=> array(
                array(
                    'type-schedule' => 'fixed-time',
                    'date-time' => '20/10/2013 20:20',
                    'type-interaction' => 'question-answer',
                    'type-question' => 'open-question',
                    'content' => 'how are you?', 
                    'keyword' => $keyword, 
                    'type-unmatching-feedback' => 'no-unmatching-feedback'
                    )
                )
            );

        return $dialogue;
    }

    public function getInteractionOpenQuestion()
    {
        return array(
            "type-schedule" => "offset-days",
            "days" => "1",
            "at-time" => "09:00",
            "type-interaction" => "question-answer",
            "content" => "What is your name?",
            "keyword" => "name",
            "set-use-template" => "use-template",
            "type-question" => "open-question",
            "type-unmatching-feedback" => "program-unmatching-feedback",
            "set-reminder" => "reminder",
            "reminder-minutes"=> "6",
            "interaction-id" => "3a9538055402",
            "activated" => "0",
            "reminder-number" => "1",
            "type-schedule-reminder" => "reminder-offset-time",
            "reminder-actions" => array(
                array("type-action" => "optout")));
    }

    public function getInteractionClosedQuestion()
    {
        return array(
            "type-schedule" => "fixed-time",
			"date-time" => "16/10/2012 10:00",
			"type-interaction"=> "question-answer",
			"content"=> "How are you?",
			"keyword" => "Feel",
			"type-question" => "closed-question",
			"label-for-participant-profiling" => "feel",
			"answers" => array(
			    array("choice"=> "Fine",
			        "feedbacks" => array(
			            array("content" => "Good for you!")),
			        "answer-actions" => array(
			            array(
			                "type-answer-action" => "tagging",
			                "tag" => "TagMe"))),
			    array("choice"=> "Bad",
			        "feedbacks" => array(
			            array("content" => "Need to relax this weekend!")),
			        "answer-actions" => array(
			            array(
			                "type-answer-action" => "tagging",
			                "tag" => "Relaxing")))
			    ),
			"type-unmatching-feedback" => "interaction-unmatching-feedback",
			"unmatching-feedback-content" => "You can only reply Fine and Bad",
			"interaction-id" => "3a9538055402",
			"activated" => "0");
	}

    public function getInteractionMultiKeywordQuestion()
    {
        return array(
            "type-schedule" => "offset-condition",
            "offset-condition-interaction-id" => "3a9538055402",
            "type-interaction" => "question-answer-keyword",
            "content" => "What is your gender?",
            "label-for-participant-profiling" => "gender",
            "answer-keywords" => array(
                array("keyword" => "Female"),
                array(
                    "keyword" => "Male",
                    "feedbacks" => array(
                        array("content" => "It's a Female only program, you are going to be optout.")),
                    "answer-actions" => array(
                        array("type-answer-action" => "optout")))),
            "type-unmatching-feedback" => "no-unmatching-feedback",
            "set-reminder" => "reminder",
            "reminder-number" => "3",
            "type-schedule-reminder" => "reminder-offset-days",
            "reminder-days" => "2",
            "reminder-at-time" => "07:00",
            "reminder-actions" => array(
                array("type-action" => "optout")),
            "interaction-id" => "3a9538055402",
            "activated" => "0");
    }


    public function getOneRequest()
    {
        $request['Request'] = array(
            'keyword' => 'KEYWORD request',
            'responses' => array(
                array(
                    'content' => 'thanks message',
                    )
                ),
            'actions' => array(
                array(
                    'type-action' => 'optin'
                    )
                ),
            );
        return $request;
    }

    public function getDialogueSchedule($participantPhone='08', $dialogueId='01', $interactionId='01')
    {
        return array('Schedule' => array(
                'object-type' => 'dialogue-schedule',
                'participant-phone' => $participantPhone,
                'dialogue-id' => $dialogueId,
                'interaction-id' => $interactionId,
                'date-time' => '2013/12/01T12:12'
            ));
    }


}
