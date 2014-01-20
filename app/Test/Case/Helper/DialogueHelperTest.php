<?php

App::uses('DialogueHelper', 'Lib');
App::uses('ScriptMaker', 'Lib');

class DialogueHelperTestCase extends CakeTestCase
{
 
    public function setUp()
    {
        parent::setUp();
        
        $this->Maker = new ScriptMaker();
    }
  
   
    public function testHasKeyword()
    {
        $DialogueHelper = new DialogueHelper();
        $dialogue = array('keyword'=>'keyword');
        $this->assertEquals($dialogue['keyword'],$DialogueHelper->hasKeyword($dialogue, "keyword"));
        
        $dialogue = $this->Maker->getOneDialogueWithKeyword('keyword');
        $this->assertEquals(
            $dialogue['Dialogue']['interactions'][0]['keyword'],
            $DialogueHelper->hasKeyword($dialogue, "feel")
        );
        
        $dialogue = $this->Maker->getOneDialogueWithKeyword('keyword');
        $this->assertEquals(
            $dialogue['Dialogue']['interactions'][1]['keyword'],
            $DialogueHelper->hasKeyword($dialogue, "keyword")
        );
    }


    public function testHasKeywork_answerAcceptNoSpace()
    {
        $DialogueHelper = new DialogueHelper();
        $dialogue = $this->Maker->getOneDialogueAnwerNoSpaceSupported('fel');
        $this->assertEquals(
            'felGood',
            $DialogueHelper->hasKeyword($dialogue, "felGood")
            );
        
        $this->assertEquals(
            'fel',
            $DialogueHelper->hasKeyword($dialogue, "fel")
            );
        $this->assertEquals(
            'felBad',
            $DialogueHelper->hasKeyword($dialogue, "felBad")
            );
        $this->assertEquals(
            false,
            $DialogueHelper->hasKeyword($dialogue, "felOk")
            );
    }

    public function testGetKeywork()
    {
        $DialogueHelper = new DialogueHelper();
        $dialogue = $this->Maker->getOneDialogueAnwerNoSpaceSupported('fel');
        $this->assertEquals(
            array('feel', 'fel', 'felGood','felBad'), 
            $DialogueHelper->getKeywords($dialogue)
            );

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');
        $this->assertEquals(
            array('usedKeyword'), 
            $DialogueHelper->getKeywords($dialogue)
            );
    }
    
    public function testHasNoMatchingAnswers()
    {
        $DialogueHelper = new DialogueHelper();
        $dialogue = array('keyword'=>'keyword',
        	'answers'=> array(
        	    0 => array('choice' => 'Bad'),
        	    1 => array('choice' => 'Good')
        	)
        );
        
        $status = array('message-content'=>'keyword Good');
        $this->assertFalse($DialogueHelper->hasNoMatchingAnswers($dialogue, $status));
                
        $status = array('message-content'=>'keyword Goo');
        $this->assertTrue($DialogueHelper->hasNoMatchingAnswers($dialogue, $status));
        
        $dialogue = $this->Maker->getOneDialogueWithKeyword('keyword');
        $status = array('message-content'=>'keyword Bad');
        $this->assertFalse($DialogueHelper->hasNoMatchingAnswers($dialogue, $status));
        
        $status = array('message-content'=>'keyword 2');
        $this->assertFalse($DialogueHelper->hasNoMatchingAnswers($dialogue, $status));
    }

    public function testGetInteraction()
    {
        $DialogueHelper = new DialogueHelper();
        $dialogue = $this->Maker->getOneDialogueWithKeyword('keyword');
        $this->assertEqual(
            $dialogue['Dialogue']['interactions'][0],	
            $DialogueHelper->getInteraction($dialogue, "script.dialogues[0].interactions[0]")
            );
        
        $this->assertEqual(
            $dialogue['Dialogue']['interactions'][1],	
            $DialogueHelper->getInteraction($dialogue, "script.dialogues[0].interactions[1]")
            );
        
        $this->assertEqual(
            array(),	
            $DialogueHelper->getInteraction($dialogue, "other")
            );
    }

    public function testGetRequestKeywordToValidate()
    {
        $DialogueHelper = new DialogueHelper();
        $requestKeywords = "www, www join, ww";
        $this->assertEqual('www, www, ww', 
            $DialogueHelper->getRequestKeywordToValidate($requestKeywords)
            );

    }

    public function testConvertDataFormat()
    {
        $DialogueHelper = new DialogueHelper();

        $vusionHtmlFormDate = "10/12/2012 10:12";
        $vusionHtmlSearchDate = "10/12/2012";
        $isoDate = "2012-12-10T10:12:00";
        
        $this->assertEqual('2012-12-10T10:12:00', $DialogueHelper->convertDateFormat($vusionHtmlFormDate));
        $this->assertEqual('2012-12-10T00:00:00', $DialogueHelper->convertDateFormat($vusionHtmlSearchDate));
        $this->assertEqual('2012-12-10T10:12:00', $DialogueHelper->convertDateFormat($isoDate));
        $this->assertEqual(null, $DialogueHelper->convertDateFormat(''));
        $this->assertEqual(null, $DialogueHelper->convertDateFormat(null));
    }


    //TODO: Fail to work well with unicode character with accent
    public function testIsUsedKeyword()
    {
        $usedKeywords = array(
            'keyword1' => array('programName' => 'myprog1', 'type' => 'dialogue'),
            'keyword2' => array('programName' => 'myprog2', 'type' => 'dialogue'),
            'kéÉywórd2' => array('programName' => 'myprog2', 'type' => 'dialogue'));

        $this->assertEqual(
            array('keyword1' => array('programName' => 'myprog1', 'type' => 'dialogue')),
            DialogueHelper::isUsedKeyword('keyword1', $usedKeywords));

        $this->assertEqual(
            array('keyword2' => array('programName' => 'myprog2', 'type' => 'dialogue')),
            DialogueHelper::isUsedKeyword('keyWord2', $usedKeywords));
        $this->assertEqual(
            array('keyword2' => array('programName' => 'myprog2', 'type' => 'dialogue')),
            DialogueHelper::isUsedKeyword('keYword2', $usedKeywords));

        $this->assertEqual(
            array('kéÉywórd2' => array('programName' => 'myprog2', 'type' => 'dialogue')),
            DialogueHelper::isUsedKeyword('kéÉywórd2', $usedKeywords));
    }


    public function testCleanKeyword()
    {
        //NOT CASE SENSITIVE
        $this->assertEqual(
            DialogueHelper::cleanKeyword('KeywOrd1'), 
            'keyword1');
        
        //French
        //NOT ACCENT SENSITIVE 
        $this->assertEqual(
            DialogueHelper::cleanKeyword('áàâä éèêë íîï óô úùûü'), 
            'aaaa eeee iii oo uuuu');
        $this->assertEqual(
            DialogueHelper::cleanKeyword('ÁÀÂÄ ÉÈÊË ÍÎÏ ÓÔ ÚÙÛÜ'), 
            'aaaa eeee iii oo uuuu');
        //NOT LIGATURE SENSITIVE
        $this->assertEqual(
            DialogueHelper::cleanKeyword('æÆœŒ'), 
            'aeaeoeoe');        
        //Other
        $this->assertEqual(
            DialogueHelper::cleanKeyword('çÇ'),
            'cc');


        //Spanish Accent
        //NOT ACCENT SENSITIVE
        $this->assertEqual(
            DialogueHelper::cleanKeyword('áéíóúüñ'),
            'aeiouun');
        $this->assertEqual(
            DialogueHelper::cleanKeyword('ÁÉÍÓÚÜÑ'),
            'aeiouun');
    }
    
}
