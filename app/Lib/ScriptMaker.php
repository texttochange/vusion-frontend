<?php

class ScriptMaker
{

    
    public function getOneDialogueWithKeyword($keyword="keyword")
    {
        $dialogue['Dialogue'] = array(
            'activated' => 0,
            'auto-enrollment' => 'none',
            'interactions'=> array(
                array(
                    'type-schedule' => 'fixed-time',
                    'date-time' => '20/10/2013 20:20',
                    'type-interaction' => 'announcement',
                    'content' => 'hello',
                    ),	
                array(
                    'type-interaction' => 'question-answer',
                    'type-schedule' => 'fixed-time',
                    'date-time' => '20/10/2013 20:20',
                    'content' => 'how are you', 
                    'keyword' => $keyword,
                    'type-question'=>'closed-question',
                    'answers'=> array(
                        0 => array('choice'=>'Good'),
                        1 => array('choice'=>'Bad')
                        ),
                    )
                )
            );

        return $dialogue;
    }


    public function getOneDialogueAnwerNoSpaceSupported($keyword="keyword")
    {
        $dialogue = $this->getOneDialogueWithKeyword($keyword);
        $dialogue['Dialogue']['interactions'][0] = array(
            'type-schedule' => 'fixed-time',
            'date-time' => '20/10/2013 20:20',
            'type-interaction' => 'question-answer',
            'content' => 'hello this is a question',
            'keyword' => 'feel');
        $dialogue['Dialogue']['interactions'][1]['set-answer-accept-no-space'] =  'answer-accept-no-space';
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
                    'answer-label' => 'feel',
                    'keyword' => $keyword, 
                    'type-unmatching-feedback' => 'no-unmatching-feedback'
                    )
                )
            );
        return $dialogue;
    }


    public function getOneDialogueMultikeyword()
    {
        $dialogue['Dialogue'] = array(
            'name' => 'my dialogue',
            'auto-enrollment' => 'none',
            'dialogue-id' => null,
            'interactions'=> array(
                $this->getInteractionMultiKeywordQuestion()
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
            "answer-label" => "name",
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
			"set-answer-accept-no-space" => null,
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


    public function getOneRequest($keyphrase = 'KEYWORD request')
    {
        $request['Request'] = array(
            'keyword' => $keyphrase,
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

    public function getParticipant($phone='+256788601462')
    {
        return array('Participant' => array('phone' => $phone));
    }


    public function getSettings($shortcode='256-8181', $timezone="Africa/Kampala", 
                                $creditType="none", $creditNumber=null, $creditFromDate=null, $creditToDate=null)
    {
        return array(
            'shortcode' => $shortcode,
            'timezone' => $timezone,
            'credit-type' => $creditType,
            'credit-number' => $creditNumber,
            'credit-from-date' => $creditFromDate,
            'credit-to-date' => $creditToDate,
            );
    }


    public function getCreditStatus($count='10', $status='ok', $since='2013-07-21T10:10:10')
    {
        return array(
            'count' => $count,
            'manager' => array(
                'status' => $status,
                'since' => $since));
    }

    static public function mkCreditLog($objectType='program-credit-log', $date='2014-04-10',
                                $programDatabase='mydatabase', $code='256-8181', $incoming=2,
                                $outgoing=1)
    {
        switch($objectType) {
        case 'program-credit-log':
            return array(
                'object-type' => $objectType,
                'date' => $date,
                'code' => $code,
                'program-database' => $programDatabase,
                'incoming' => $incoming,
                'outgoing' => $outgoing);
            break;
        case 'garbage-credit-log':
            return array(
                'object-type' => $objectType,
                'date' => $date,
                'code' => $code,
                'incoming' => $incoming,
                'outgoing' => $outgoing);
            break;
        case 'deleted-program-credit-log':
            return array(
                'object-type' => $objectType,
                'date' => $date,
                'code' => $code,
                'program-name' => 'My Deleted Program',
                'incoming' => $incoming,
                'outgoing' => $outgoing);
            break;
        } 
        return null;
    }

}
