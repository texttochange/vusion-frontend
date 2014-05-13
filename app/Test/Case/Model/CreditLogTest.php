<?php
App::uses('CreditLog', 'Model');
App::uses('ScriptMaker', 'Lib');


class CreditLogTestCase extends CakeTestCase
{

    public function setUp()
    {
        parent::setUp();
        $options         = array('database'=>'test'); 
        $this->CreditLog = new CreditLog($options);
        $this->CreditLog->setDataSource('mongo_test');

        $this->dropData();
    }


    public function tearDown()
    {
        $this->dropData();
        unset($this->CreditLog);
        parent::tearDown();
    }


    public function dropData()
    {   
        $this->CreditLog->deleteAll(true, false);
    }


    public function testSaveCreditLogs()
    {
        $creditLog = ScriptMaker::mkCreditLog();

        $this->CreditLog->save($creditLog);

        $this->assertEqual(1, $this->CreditLog->find('count'));
    } 


    public function testCalculateCreditPerProgram()
    {
        $creditLog = ScriptMaker::mkCreditLog();
        $this->CreditLog->create();
        $this->CreditLog->save($creditLog);

        $creditLog = ScriptMaker::mkCreditLog();
        $this->CreditLog->create();
        $this->CreditLog->save($creditLog);

        $creditLog = ScriptMaker::mkCreditLog('program-credit-log', '2014-04-10', 'mydatabase2');
        $this->CreditLog->create();
        $this->CreditLog->save($creditLog);

        ##not to be counted as out of the dateframe
        $creditLog = ScriptMaker::mkCreditLog('program-credit-log', '2013-02-10', 'mydatabase2');
        $this->CreditLog->create();
        $this->CreditLog->save($creditLog);

        ##not to be counted as not in the list of database
        $creditLog = ScriptMaker::mkCreditLog('program-credit-log', '2013-04-10', 'mydatabase3');
        $this->CreditLog->create();
        $this->CreditLog->save($creditLog);

        $creditLog = ScriptMaker::mkCreditLog('garbage-credit-log');
        $this->CreditLog->create();
        $this->CreditLog->save($creditLog);

        $conditions = array('date' => array('$gte' => '2014-04-00', '$lt' => '2014-05-00'));

        $programs = array('mydatabase', 'mydatabase2');
        $shortcodes = array('256-8181');
        $result = $this->CreditLog->calculateProgramShortcodeCredits($programs, $shortcodes, $conditions);
        $this->assertEqual(
            $result[0], 
            array( 'object-type' => 'program-credit-log',
                'code' => '256-8181',
                'program-database' => 'mydatabase',
                'incoming' => 4,
                'outgoing' => 2,
                'outgoing-ack' => 0,
                'outgoing-nack' => 0,
                'outgoing-failed' => 0,
                'outgoing-delivered' => 0));
        $this->assertEqual(
            $result[1], 
            array( 'object-type' => 'program-credit-log',
                'code' => '256-8181',
                'program-database' => 'mydatabase2',
                'incoming' => 2,
                'outgoing' => 1,
                'outgoing-ack' => 0,
                'outgoing-nack' => 0,
                'outgoing-failed' => 0,
                'outgoing-delivered' => 0));
        $this->assertEqual(
            $result[2], 
            array( 'object-type' => 'garbage-credit-log',
                'code' => '256-8181',
                'incoming' => 2,
                'outgoing' => 1,
                'outgoing-ack' => 0,
                'outgoing-nack' => 0,
                'outgoing-failed' => 0,
                'outgoing-delivered' => 0));
    }


    public function testCalculateCreditPerCountry()
    {
        $creditLog = ScriptMaker::mkCreditLog();
        $this->CreditLog->create();
        $this->CreditLog->save($creditLog);

        $creditLog = ScriptMaker::mkCreditLog();
        $this->CreditLog->create();
        $this->CreditLog->save($creditLog);

        $creditLog = ScriptMaker::mkCreditLog('program-credit-log', '2014-04-10', 'mydatabase2');
        $this->CreditLog->create();
        $this->CreditLog->save($creditLog);

        ##not to be counted as out of the dateframe
        $creditLog = ScriptMaker::mkCreditLog('program-credit-log', '2013-02-10', 'mydatabase2');
        $this->CreditLog->create();
        $this->CreditLog->save($creditLog);

        ##not to be counted as not in the list of database
        $creditLog = ScriptMaker::mkCreditLog('program-credit-log', '2013-04-10', 'mydatabase3');
        $this->CreditLog->create();
        $this->CreditLog->save($creditLog);

        ##same program but on a different shortcode
        $creditLog = ScriptMaker::mkCreditLog('program-credit-log', '2014-04-10', 'mydatabase3', '255-15001');
        $this->CreditLog->create();
        $this->CreditLog->save($creditLog);

        $creditLog = ScriptMaker::mkCreditLog('garbage-credit-log');
        $this->CreditLog->create();
        $this->CreditLog->save($creditLog);

        $conditions = array('date' => array('$gte' => '2014-04-00', '$lt' => '2014-05-00'));

        $countriesByPrefixes = array(
            '256' => 'Uganda',
            '255' => 'Tanzania'); 

        $result = $this->CreditLog->calculateCreditPerCountry($conditions, $countriesByPrefixes);
        $this->assertEqual(
            $result[0], 
            array(
                'country' => 'Uganda',
                'prefix' => '256',
                'incoming' => 8,
                'outgoing' => 4,
                'codes' => array(
                    array(
                        'code' => '256-8181',
                        'incoming' => 8,
                        'outgoing' => 4,
                        'garbage' => array(
                            'object-type' => 'garbage-credit-log',
                            'code' => '256-8181',
                            'incoming' => 2,
                            'outgoing' => 1,
                            'outgoing-ack' => 0,
                            'outgoing-nack' => 0,
                            'outgoing-failed' => 0,
                            'outgoing-delivered' => 0),
                        'programs' => array(
                            array(
                                'object-type' => 'program-credit-log',
                                'code' => '256-8181',
                                'program-database' => 'mydatabase',
                                'incoming' => 4,
                                'outgoing' => 2,
                                'outgoing-ack' => 0,
                                'outgoing-nack' => 0,
                                'outgoing-failed' => 0,
                                'outgoing-delivered' => 0),
                            array('object-type' => 'program-credit-log',
                                'code' => '256-8181',
                                'program-database' => 'mydatabase2',
                                'incoming' => 2,
                                'outgoing' => 1,
                                'outgoing-ack' => 0,
                                'outgoing-nack' => 0,
                                'outgoing-failed' => 0,
                                'outgoing-delivered' => 0)))))
            );
        $this->assertEqual(
            $result[1],         
            array(
                'country' => 'Tanzania',
                'prefix' => '255',
                'incoming' => 2,
                'outgoing' => 1,
                'codes' => array(
                    array(
                        'code' => '255-15001',
                        'garbage' => array(),
                        'incoming' => 2,
                        'outgoing' => 1,
                        'programs' => array(
                            array('object-type' => 'program-credit-log',
                                'code' => '255-15001',
                                'program-database' => 'mydatabase3',
                                'incoming' => 2,
                                'outgoing' => 1,
                                'outgoing-ack' => 0,
                                'outgoing-nack' => 0,
                                'outgoing-failed' => 0,
                                'outgoing-delivered' => 0)))))
            );
    }


    public function testCalculateCreditPerCountry_noCredit()
    {
        $result = $this->CreditLog->calculateCreditPerCountry(array(), array());
        $this->assertEqual($result, array());
    }
   

    public function testFromTimeframeParametersToQueryConditions() 
    {
        $timeframeParameters = array(
            'timeframe-type' => 'predefined-timeframe',
            'predefined-timeframe' => 'current-month');

        $this->assertEqual(
            array('date' => array('$gte' => date('Y-m-01'))),
            CreditLog::fromTimeframeParametersToQueryConditions($timeframeParameters)
            );        
    }

}