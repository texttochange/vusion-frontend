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


    public function testCleanKeyword_numeric()
    {
        $this->assertEqual(
            DialogueHelper::cleanKeyword('11'), 
            '11');
    }


    public function testCleanPhrase_numeric()
    {
        $this->assertEqual(
            DialogueHelper::cleanKeyphrases('11'), 
            array('11'));

        $this->assertEqual(
            DialogueHelper::cleanKeyphrases('11 something'), 
            array('11 something'));
    }


    public function testLoadCountry_byPrefix()
    {
        $fileName = WWW_ROOT . Configure::read('vusion.countriesPrefixesFile');
        $countries = DialogueHelper::loadCountries($fileName, "Prefix");
        $this->assertEqual(
            $countries[93],
            'Afghanistan');
    }

    public function testLoadCountry_byIso()
    {
        $fileName = WWW_ROOT . Configure::read('vusion.countriesPrefixesFile');
        $countries = DialogueHelper::loadCountries($fileName, "Iso");
        $this->assertEqual(
            $countries['AFG'],
            'Afghanistan');
    }
    
}


