<?php

App::uses('ScriptHelper', 'Lib');

class ScriptHelperTestCase extends CakeTestCase
{
    protected function getOneScript($keyword)
    {
        $script['Script'] = array(
            'script' => array(
                'dialogues' => array(
                    array(
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
                                'interaction-id' => 'script.dialogues[0].interactions[0]'
                                )
                            )
                        )
                    )
                )
            );

        return $script;
    }
    
    
    public function testHasKeyword()
    {
        $scriptHelper = new ScriptHelper();
        $script = array('keyword'=>'keyword');
        $this->assertTrue($scriptHelper->hasKeyword($script, "keyword"));
        
        $script = array();
        $this->assertFalse($scriptHelper->hasKeyword($script, "keyword"));
        
        $script = $this->getOneScript('keyword');
        $this->assertTrue($scriptHelper->hasKeyword($script, "feel"));
        
        $script = $this->getOneScript('keyword');
        $this->assertTrue($scriptHelper->hasKeyword($script, "keyword"));
    }
    
    
    public function testHasNoMatchingAnswers()
    {
        $scriptHelper = new ScriptHelper();
        $script = array('keyword'=>'keyword',
        	'answers'=> array(
        	    0 => array('choice' => 'Bad'),
        	    1 => array('choice' => 'Good')
        	)
        );
        
        $status = array('message-content'=>'keyword Good');
        $this->assertFalse($scriptHelper->hasNoMatchingAnswers($script, $status));
                
        $status = array('message-content'=>'keyword Goo');
        $this->assertTrue($scriptHelper->hasNoMatchingAnswers($script, $status));
        
        $script = $this->getOneScript('keyword');
        $status = array('message-content'=>'keyword Bad');
        $this->assertFalse($scriptHelper->hasNoMatchingAnswers($script, $status));
        
        $status = array('message-content'=>'keyword 2');
        $this->assertFalse($scriptHelper->hasNoMatchingAnswers($script, $status));
    }
    
    
}
