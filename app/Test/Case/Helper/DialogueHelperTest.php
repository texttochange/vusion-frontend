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
  
/*   
    #Deprecated
    public function testHasKeyword()
    {
        $dialogue = $this->Maker->getOneDialogue('kÉyword');
        $this->assertEquals(
            array('keyword'),
            DialogueHelper::hasKeywords($dialogue, array('keyword')));
        
        $dialogue = $this->Maker->getOneDialogueWithKeyword();
        $this->assertEquals(
            array('feel', 'keyword'),
            DialogueHelper::hasKeywords($dialogue, array('feel', 'keyword')));
        
        $dialogue = $this->Maker->getOneDialogueWithKeyword('keyword');
        $this->assertEquals(
            array('keyword'),
            DialogueHelper::hasKeywords($dialogue, array('keyword')));
    }

    #Deprecated
    public function testHasKeywork_answerAcceptNoSpace()
    {
        $dialogue = $this->Maker->getOneDialogueAnwerNoSpaceSupported('fÉl');
        $this->assertEquals(
            array('felgood'),
            DialogueHelper::hasKeywords($dialogue, array('felgood')));
        $this->assertEquals(
            array('fel'),
            DialogueHelper::hasKeywords($dialogue, array('fel')));
        $this->assertEquals(
            array('felbad'),
            DialogueHelper::hasKeywords($dialogue, array('felbad')));
        $this->assertEquals(
            array(),
            DialogueHelper::hasKeywords($dialogue, array('felok')));
    }

    #Deprecated
    public function testGetKeyworks()
    {
        $dialogue = $this->Maker->getOneDialogueAnwerNoSpaceSupported('fÉl');
        $this->assertEquals(
            array('feel', 'fel', 'felgood','felbad'), 
            DialogueHelper::getKeywords($dialogue));

        $dialogue = $this->Maker->getOneDialogue('usedKeyword');
        $this->assertEquals(
            array('usedkeyword'), 
            DialogueHelper::getKeywords($dialogue));
        
        $dialogue = $this->Maker->getOneDialogueMultikeyword();
        $this->assertEqual(
            array('female', 'male'),
            DialogueHelper::getKeywords($dialogue));
    }

    #Deprecated
    public function testHasNoMatchingAnswers()
    {
        $dialogue = array('keyword'=>'keyword',
        	'answers'=> array(
        	    0 => array('choice' => 'Bad'),
        	    1 => array('choice' => 'Good')
        	)
        );
        
        $status = array('message-content'=>'keyword Good');
        $this->assertFalse(DialogueHelper::hasNoMatchingAnswers($dialogue, $status));
                
        $status = array('message-content'=>'keyword Goo');
        $this->assertTrue(DialogueHelper::hasNoMatchingAnswers($dialogue, $status));
        
        $dialogue = $this->Maker->getOneDialogueWithKeyword('keyword');
        $status = array('message-content'=>'keyword Bad');
        $this->assertFalse(DialogueHelper::hasNoMatchingAnswers($dialogue, $status));
        
        $status = array('message-content'=>'keyword 2');
        $this->assertFalse(DialogueHelper::hasNoMatchingAnswers($dialogue, $status));
    }

    #Deprecated
    public function testGetInteraction()
    {
        $dialogue = $this->Maker->getOneDialogueWithKeyword('keyword');
        $this->assertEqual(
            $dialogue['Dialogue']['interactions'][0],	
            DialogueHelper::getInteraction($dialogue, "script.dialogues[0].interactions[0]"));
        
        $this->assertEqual(
            $dialogue['Dialogue']['interactions'][1],	
            DialogueHelper::getInteraction($dialogue, "script.dialogues[0].interactions[1]"));
        
        $this->assertEqual(
            array(),	
            DialogueHelper::getInteraction($dialogue, "other"));
    }
*/

    public function testGetRequestKeywordToValidate()
    {
        $requestKeywords = "www, www join, ww";
        $this->assertEqual(
            array('www', 'www', 'ww'), 
            DialogueHelper::fromKeyphrasesToKeywords($requestKeywords)
            );
    }

    public function testConvertDataFormat()
    {
        $vusionHtmlFormDate = "10/12/2012 10:12";
        $vusionHtmlSearchDate = "10/12/2012";
        $isoDate = "2012-12-10T10:12:00";
        
        $this->assertEqual('2012-12-10T10:12:00', DialogueHelper::convertDateFormat($vusionHtmlFormDate));
        $this->assertEqual('2012-12-10T00:00:00', DialogueHelper::convertDateFormat($vusionHtmlSearchDate));
        $this->assertEqual('2012-12-10T10:12:00', DialogueHelper::convertDateFormat($isoDate));
        $this->assertEqual(null, DialogueHelper::convertDateFormat(''));
        $this->assertEqual(null, DialogueHelper::convertDateFormat(null));
    }


    //TODO: Fail to work well with unicode character with accent
 /* 
    #Deprecated  
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
    }*/


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

    
    public function testCleanKeywords()
    {
        $keywords = array(
            'k1, k2, k3',
            'k4 stuff, k4 other',
            'k5');
        $this->assertEqual(
            DialogueHelper::cleanKeywords($keywords), 
            array('k1', 'k2', 'k3', 'k4', 'k5'));
    }

    
}


