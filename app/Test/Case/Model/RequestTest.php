<?php
App::uses('Request', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');
App::uses('ScriptMaker', 'Lib');
App::uses('ProgramSpecificMongoModel', 'Model');


class RequestTestCase extends CakeTestCase
{
 
   
    public function setUp()
    {
        parent::setUp();
        $dbName = 'testdbprogram';
        $this->Request = ProgramSpecificMongoModel::init(
            'Request', $dbName);

        $this->Maker = new ScriptMaker();
    }


    public function tearDown()
    {
        $this->Request->deleteAll(true, false);
        unset($this->Request);
        parent::tearDown();
    }


    public function testUseKeyword_multipleKeywords()
    {
        $request = $this->Maker->getOneRequest('key request, keyword, otherkeyword request');
        $this->Request->create();
        $savedRequest = $this->Request->save($request);

        $this->assertEqual(
            array(
                'keyword' => array(
                    'request-id' => $savedRequest['Request']['_id']."",
                    'request-name' => $savedRequest['Request']['keyword'])), 
            $this->Request->useKeyword('keyword'));

        $this->assertEqual(
            null, 
            $this->Request->useKeyword('keywo'));

        $this->assertEqual(
            array(
                'keyword' => array(
                    'request-id' => $savedRequest['Request']['_id']."",
                    'request-name' => $savedRequest['Request']['keyword']), 
                'key' => array(
                    'request-id' => $savedRequest['Request']['_id']."",
                    'request-name' => $savedRequest['Request']['keyword'])), 
            $this->Request->useKeyword('keywor, keyword, key'));
        
        $this->assertEqual(
            array('key' => array(
                    'request-id' => $savedRequest['Request']['_id']."",
                    'request-name' => $savedRequest['Request']['keyword'])), 
            $this->Request->useKeyword('kÉy'));
        
        $this->assertEqual(
            null, 
            $this->Request->useKeyword('request'));
    }


    public function testUseKeyword()
    {
        $request = $this->Maker->getOneRequest('key');
        $this->Request->create();
        $savedRequest = $this->Request->save($request);

        $this->assertEqual(
            array(
                'key' => array(
                    'request-id' => $savedRequest['Request']['_id']."",
                    'request-name' => $savedRequest['Request']['keyword'])),
            $this->Request->useKeyword('key'));
    }


    public function testUseKeyword_excludeRequest()
    {
        $request = $this->Maker->getOneRequest('key');
        $this->Request->create();
        $savedRequest = $this->Request->save($request);

        $this->assertEqual(
            null,
            $this->Request->useKeyword('key', $savedRequest['Request']['_id'].''));
    }


    public function testGetKeywords()
    {
        $expected = array('key', 'keyword', 'otherkeyword', 'k2', 'frere');
        $request['Request'] = array(
            'keyword' => 'key request, keyword, otherkeyword request'
            );
        $this->Request->create();
        $this->Request->save($request);

        $request['Request'] = array(
            'keyword' => 'k2, frère, key stuff');
        $this->Request->create();
        $this->Request->save($request);
        
        $result = $this->Request->getKeywords();
        asort($expected);
        asort($result);
        $this->assertEqual(
            array_values($expected),
            array_values($result));
    }


    public function testGetRequestKeywords()
    {
        $request['Request'] = array(
            'keyword' => '11'
            );
        
        $this->assertEqual(
            array('11'),
            Request::getRequestKeywords($request));
    }


    public function testUseKeyphrase()
    {
        $request = $this->Maker->getOneRequest('key request, keyword, otherkeyword request');
        $this->Request->create();
        $savedRequest = $this->Request->save($request);

        $otherRequest = $this->Maker->getOneRequest('something else');
        $this->Request->create();
        $this->Request->save($otherRequest);

        $this->assertEqual(
            array(
                'otherkeyword request' => array(
                    'request-id' => $savedRequest['Request']['_id']."",
                    'request-name' => $savedRequest['Request']['keyword'])),
            $this->Request->useKeyphrase('otherkeyword request'));

        $this->assertEqual(
            array(
                'keyword' => array(
                    'request-id' => $savedRequest['Request']['_id']."",
                    'request-name' => $savedRequest['Request']['keyword'])),
            $this->Request->useKeyphrase('keyword'));

        $this->assertEqual(
            false,
            $this->Request->useKeyphrase('keywo'));

        $this->assertEqual(
            array(
                'otherkeyword request' => array(
                    'request-id' => $savedRequest['Request']['_id']."",
                    'request-name' => $savedRequest['Request']['keyword'])),  
            $this->Request->useKeyphrase('keywor, otherkeyword request'));
        
        $this->assertEqual(
            false, 
            $this->Request->useKeyphrase('kEy'));
        
        $this->assertEqual(
            array(
                'key request' => array(
                    'request-id' => $savedRequest['Request']['_id']."",
                    'request-name' => $savedRequest['Request']['keyword'])),   
            $this->Request->useKeyphrase('kEy request'));
        
        $this->assertEqual(
            false,
            $this->Request->useKeyphrase('request'));
        
        ## Exclude request parameter
        $this->assertEqual(
            false,
            $this->Request->useKeyphrase('kEy request', $savedRequest['Request']['_id']));
    }


    public function testUseKeyphrase_numeric()
    {
        $request = $this->Maker->getOneRequest('11');
        $this->Request->create();
        $request = $this->Request->save($request);

        ## Work with keyphrase numeric
        $this->assertEqual(
            array(
                '11' => array(
                    'request-id' => $request['Request']['_id']."",
                    'request-name' => $request['Request']['keyword'])),
            $this->Request->useKeyphrase('11'));
    }


    public function testGetRequestKeyphrases_numeric()
    {
        $request = $this->Maker->getOneRequest('11');

        ## Work with keyphrase numeric
        $this->assertEqual(
            array('11'),
            Request::getRequestKeyphrases($request, '11'));
    }


    public function testGetRequestFilterOptions()
    {
        $request['Request'] = array('keyword' => 'keyword1');
        $this->Request->create();
        $savedRequest = $this->Request->save($request);

        $otherRequest['Request'] = array('keyword' => 'keyword2');
        $this->Request->create();
        $savedOtherRequest = $this->Request->save($otherRequest);

        $expected = array(
            $savedRequest['Request']['_id'].'' => 'keyword1',
            $savedOtherRequest['Request']['_id'].'' => 'keyword2');
   
        $this->assertEqual(
            $expected,
            $this->Request->getRequestFilterOptions());
    }



    public function testSave_validateKeyword_ok()
    {
        $request['Request'] = array(
            'keyword' => 'key request, keyword, otherkeyword request, für'
            );
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $this->assertTrue(isset($savedRequest));
    }

    
    public function testSave_validateAction_ok()
    {
        $request['Request'] = array(
            'keyword' => 'key request',
            'actions' => array(
                array(
                    'type-action' => 'feedback',
                    'content' => 'Hello')));
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $this->assertTrue(isset($savedRequest));
        $this->assertTrue(isset($savedRequest['Request']['actions'][0]['model-version']));
    }


    public function testSave_validateKeyword_fail_format()
    {
        $request['Request'] = array(
            'keyword' => 'key request, keyw?ord, otherkeyword request'
            );
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $this->assertFalse($savedRequest);
        $this->assertEquals(
            'This keyword/keyphrase is not valid.',
            $this->Request->validationErrors['keyword'][0]);
    }


    public function testSave_validateKeyword_fail_alreadyUsedOtherProgram()
    {
        $request = $this->Maker->getOneRequest('key request, keyword, ÉotherkeYword request, für');
        $usedKeywords = array(
            'eotherkeyword' => array(
                'program-db' => 'otherprogram', 
                'program-name' => 'Other Program', 
                'by-type' => 'Dialogue'));

        $this->Request->create();
        $savedRequest = $this->Request->saveRequest($request, $usedKeywords);
        $this->assertFalse($savedRequest);
        $this->assertEquals(
            "'eotherkeyword' already used by a Dialogue of program 'Other Program'.",
            $this->Request->validationErrors['keyword'][0]);
    }


    public function testSave_validateKeyword_fail_alreadyUsedSameProgram()
    {
        $request = $this->Maker->getOneRequest('key request, keyword, ÉotherkeYword request, für');
        $this->Request->create();
        $savedRequest = $this->Request->saveRequest(
            $request, 
            array(
                'eotherkeyword request' => array(
                    'program-db' => 'testdbprogram', 
                    'program-name' => '', 
                    'by-type' => 'Request',
                    'request-name' => 'Another keyword')));

        $this->assertFalse($savedRequest);
        $this->assertEquals(
            "'eotherkeyword request' already used in Request 'Another keyword' of the same program.",
            $this->Request->validationErrors['keyword'][0]);
    }    


    public function testSave_validateKeyword_ok_edit()
    {
        $request = $this->Maker->getOneRequest('key request, keyword, ÉotherkeYword request, für');
        $this->Request->create();
        $savedRequest = $this->Request->saveRequest($request);

        $savedRequest['Request']['keyword'] = 'eotherkeYword request';
        $this->Request->create();
        $this->Request->id = $savedRequest['Request']['_id'];
        $savedRequest = $this->Request->saveRequest($request);
        $this->assertTrue(isset($savedRequest['Request']));
    }


    public function testSave_validateContent_fail_apostrophe_not_allowed()
    {
        $request = array(
            'Request' => array(
                'keyword' => 'keyword',
                'responses' => array(
                    array('content' => 'what`up'))
            ));
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $this->assertFalse($savedRequest);
        $this->assertEqual(
            'The apostrophe used is not allowed.',
            $this->Request->validationErrors['responses'][0]['content'][0]);
    }
    
    public function testSave_validateContent_ok_customized_content()
    {
        $request = array(
            'Request' => array(
                'keyword' => 'keyword',
                'responses' => array(
                    array('content' => 'the box is [contentVariable.mombasa.chicken.price]'))
            ));
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $this->assertTrue(isset($savedRequest['Request']));
    }

    public function testSave_validateContent_fail_customized_content()
    {
        $request = array(
            'Request' => array(
                'keyword' => 'keyword',
                'responses' => array(
                    array('content' => 'the box is [show.box]'))
            ));
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $this->assertFalse($savedRequest);
        $this->assertEqual(
            "To be used as customized content, 'show' can only be either: participant, contentVariable, time or context.",
            $this->Request->validationErrors['responses'][0]['content'][0]);
        
        $request['Request']['responses'][0]['content'] = "here is [participant.name.gender]";
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $this->assertFalse($savedRequest);
        $this->assertEqual(
            "To be used in message, participant only accepts one key.",
            $this->Request->validationErrors['responses'][0]['content'][0]);
        
        $request['Request']['responses'][0]['content'] = "here is [contentVariable.pen.%#color]";
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $this->assertFalse($savedRequest);
        $this->assertEqual(
            "To be used as customized content, '%#color' can only be composed of letter(s), digit(s) and/or space(s).",
            $this->Request->validationErrors['responses'][0]['content'][0]);
    }
    

    public function testSave_beforeValidate_removeEmptyReponses()
    {
        $request = array(
            'Request' => array(
                'keyword' => 'keyword',
                'responses' => array(
                    array('content' => '  '),
                    array('content' => 'what is up'))
            ));
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $this->assertEqual(
            array(array('content' => 'what is up')),
            $savedRequest['Request']['responses']);
    }


    public function testSave_fail_apostophe()
    {
        $request = array(
            'Request' => array(
                'keyword' => 'keyword',
                'responses' => array(
                    array('content' => 'what`is up'))
            ));
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $this->assertFalse($savedRequest);
        $this->assertEqual(
            'The apostrophe used is not allowed.',
            $this->Request->validationErrors['responses'][0]['content'][0]);
    }


    public function testSave_validateAction_fail()
    {
        $request = array(
            'Request' => array(
                'keyword' => 'keyword',
                'actions' => array(
                    array(
                        'type-action' => 'feedback',
                        'content' => 'what`up'))
            ));
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        
        $this->assertFalse($savedRequest);
        $this->assertEqual(
            'The apostrophe used is not allowed.',
            $this->Request->validationErrors['actions'][0]['content'][0]);
    }

  
}
