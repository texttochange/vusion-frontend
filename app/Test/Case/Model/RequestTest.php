<?php
App::uses('Request', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');
App::uses('ScriptMaker', 'Lib');


class RequestTestCase extends CakeTestCase
{
 
   
    public function setUp()
    {
        parent::setUp();

        $connections = ConnectionManager::enumConnectionObjects();

        $option        = array('database'=>'test');
        $this->Request = new Request($option);

        $this->Request->setDataSource('mongo_test');
        $this->Request->deleteAll(true, false);

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
        $request['Request'] = array(
            'keyword' => 'key request, keyword, otherkeyword request'
            );
        $this->Request->create();
        $this->Request->save($request);

        $request['Request'] = array(
            'keyword' => 'k2, frère, key stuff');
        $this->Request->create();
        $this->Request->save($request);
        
        $this->assertEqual(
            array('key', 'keyword', 'otherkeyword', 'k2', 'frere'),
            $this->Request->getKeywords());
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
            array('keyword'),
            $this->Request->useKeyphrase('keyword'));

        $this->assertEqual(
            null,
            $this->Request->useKeyphrase('keywo'));

        $this->assertEqual(
            array('keyword'),  
            $this->Request->useKeyphrase('keywor, keyword'));
        
        $this->assertEqual(
            null, 
            $this->Request->useKeyphrase('kEy'));
        
        $this->assertEqual(
            array('key request'), 
            $this->Request->useKeyphrase('kEy request'));
        
        $this->assertEqual(
            null,
            $this->Request->useKeyphrase('request'));
        
        ## Exclude request parameter
        $this->assertEqual(
            null,
            $this->Request->useKeyphrase('kEy request', $savedRequest['Request']['_id']));
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
        $usedKeywords = array('eotherkeyword' => array('programName' => 'otherprogram', 'type' => 'dialogue'));

        $this->Request->create();
        $savedRequest = $this->Request->saveRequest($request, $usedKeywords);
        $this->assertFalse($savedRequest);
        $this->assertEquals(
            "'eotherkeyword' already used by a dialogue of program 'otherprogram'.",
            $this->Request->validationErrors['keyword'][0]);
    }


    public function testSave_validateKeyword_fail_alreadyUsedSameProgram()
    {
        $request = $this->Maker->getOneRequest('key request, keyword, ÉotherkeYword request, für');
        $this->Request->create();
        $this->Request->saveRequest($request);

        $request = $this->Maker->getOneRequest('eotherkeYword request');
        $this->Request->create();
        $savedRequest = $this->Request->saveRequest($request);

        $this->assertFalse($savedRequest);
        $this->assertEquals(
            "'eotherkeyword request' already used in the same program by a request.",
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
            "To be used as customized content, 'show' can only be either 'participant' or 'contentVariable'.",
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
