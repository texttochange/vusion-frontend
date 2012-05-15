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

    public function getOneDialogue()
    {
        $dialogue['Dialogue'] = array(
            'interactions'=> array(
                array(
                    'type-interaction' => 'question-answer', 
                    'content' => 'how are you', 
                    'keyword' => 'keyword', 
                    )
                )
            );

        return $dialogue;
    }

    public function getOneRequest()
    {
        $request['Request'] = array(
            'keyword' => 'KEYWORD',
            'responses' => array(
                array(
                    'choice' => 'Something',
                    'actions' => array(
                        array(
                            'do' => 'something'
                            )
                        ),
                    'feedback' => 'thank you',
                    )
                )
            );
        return $request;
    }

}
