<?php

class ScriptMaker
{

    public function getOneScript()
    {
        $script['Script'] = array(
            'script' => array(
                'dialogues' => array(
                    array(
                        'dialogue-id'=> 'script.dialogues[0]',
                        'interactions'=> array(
                            array(
                                'type-interaction' => 'question-answer', 
                                'content' => 'how are you', 
                                'keyword' => 'keyword', 
                                'interaction-id' => 'script.dialogues[0].interactions[0]'
                                )
                            )
                        )
                    )
                )
            );

        return $script;
    }

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
            'dialogue-id' => null,
            'interactions'=> array(
                array(
                    'type-schedule' => 'fixed-time',
                    'date-time' => '20/10/2013 20:20',
                    'type-interaction' => 'question-answer',
                    'type-question' => 'open-question',
                    'content' => 'how are you?', 
                    'keyword' => $keyword, 
                    )
                )
            );

        return $dialogue;
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
