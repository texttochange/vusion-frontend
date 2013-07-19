<?php
App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('CreditManagerHelper', 'View/Helper');
App::uses('ScriptMaker', 'Lib');


class CreditManagerHelperTest extends CakeTestCase {

    public $CreditManagerRenderer = null;

    public function setUp()
    {
        parent::setUp();
        $Controller = new Controller();
        $View = new View($Controller);
        $this->CreditManagerRenderer = new CreditManagerHelper($View);

        $this->maker = new ScriptMaker();
    }


    public function testGetCreditManagerStatusMessage_none() 
    {
        $settings = $this->maker->getSettings(
            $shortcode='256-8181', $timezone="Africa/Kampala", 
            $creditType="none");

        $creditStatus = $this->maker->getCreditStatus($count=null, $status='none');
        
        $this->assertEqual(
            '',
            $this->CreditManagerRenderer->getStatusMessage($creditStatus, $settings));
    }


    public function testGetCreditManagerStatusMessage_ok() 
    {
        $settings = $this->maker->getSettings(
            $shortcode='256-8181', $timezone="Africa/Kampala", 
            $creditType="outgoing-only", $creditNumber="100",
            $creditFromDate="2013-07-07T00:00:00", $creditToDate="2014-07-07T00:00:00");

        $creditStatus = $this->maker->getCreditStatus($count='10', $status='ok');
        
        $this->assertEqual(
            '',
            $this->CreditManagerRenderer->getStatusMessage($creditStatus, $settings));
    }


    public function testGetCreditManagerStatusMessage_ok_warning_credit() 
    {
        $settings = $this->maker->getSettings(
            $shortcode='256-8181', $timezone="Africa/Kampala", 
            $creditType="outgoing-only", $creditNumber="100",
            $creditFromDate="2013-07-07T00:00:00", $creditToDate="2014-07-07T00:00:00");

        $creditStatus = $this->maker->getCreditStatus($count='60', $status='ok');
        
        $this->assertEqual(
            'Warning only 40 credits are available for sending message.',
            $this->CreditManagerRenderer->getStatusMessage($creditStatus, $settings));
    }


    public function testGetCreditManagerStatusMessage_ok_warning_timeframe() 
    {
        $now = new DateTime();
        $toDate = $now->format('Y-m-d\T00:00:00');

        $settings = $this->maker->getSettings(
            $shortcode='256-8181', $timezone="Africa/Kampala", 
            $creditType="outgoing-only", $creditNumber="100",
            $creditFromDate="2013-07-07T00:00:00", $creditToDate=$toDate);

        $creditStatus = $this->maker->getCreditStatus($count='0', $status='ok');
        
        $this->assertEqual(
            'Warning the credits timeframe is ending tomorrow.',
            $this->CreditManagerRenderer->getStatusMessage($creditStatus, $settings));
    }


    public function testGetCreditManagerStatusMessage_noCredit() 
    {
        $settings = $this->maker->getSettings(
            $shortcode='256-8181', $timezone="Africa/Kampala", 
            $creditType="outgoing-only", $creditNumber="100",
            $creditFromDate="2013-07-07T00:00:00", $creditToDate="2020-07-07T00:00:00");

        $creditStatus = $this->maker->getCreditStatus($count='100',
            $status='no-credit', $since="2013-07-19T13:15:00");
        
        $this->assertEqual(
            'The program cannot send any message since Fri, Jul 19th 2013, 13:15.',
            $this->CreditManagerRenderer->getStatusMessage($creditStatus, $settings));
    }
     

    public function testGetCreditManagerStatusMessage_noCreditExceding() 
    {
        $settings = $this->maker->getSettings(
            $shortcode='256-8181', $timezone='Africa/Kampala', 
            $creditType='outgoing-only', $creditNumber='100',
            $creditFromDate='2013-07-07T00:00:00', $creditToDate='2020-07-07T00:00:00');

        $creditStatus = $this->maker->getCreditStatus($count='200',
            $status='no-credit', $since='2013-07-19T13:15:00');
        
        $this->assertEqual(
            'The program cannot send any message since Fri, Jul 19th 2013, 13:15. It is exeeding allowed credit by 100.',
            $this->CreditManagerRenderer->getStatusMessage($creditStatus, $settings));
    }  


    public function testGetCreditManagerStatusMessage_noCreditTimeframe_before() 
    {
        $now = new DateTime();
        $now->modify('+1 days');
        $fromDate = $now->format('Y-m-d\T00:00:00');

        $settings = $this->maker->getSettings(
            $shortcode='256-8181', $timezone="Africa/Kampala", 
            $creditType="outgoing-only", $creditNumber="100",
            $creditFromDate=$fromDate, $creditToDate="2020-07-18T00:00:00");

        $creditStatus = $this->maker->getCreditStatus($count='0', $status='no-credit-timeframe');
        
        $this->assertEqual(
            'The program cannot send any messages before Sat, Jul 20th 2013, 00:00.',
            $this->CreditManagerRenderer->getStatusMessage($creditStatus, $settings));
    }


    public function testGetCreditManagerStatusMessage_noCreditTimeframe_after() 
    {

        $settings = $this->maker->getSettings(
            $shortcode='256-8181', $timezone='Africa/Kampala', 
            $creditType='outgoing-only', $creditNumber='100',
            $creditFromDate='2013-07-07T00:00:00', $creditToDate='2013-07-18T00:00:00');

        $creditStatus = $this->maker->getCreditStatus($count='0', $status='no-credit-timeframe');
        
        $this->assertEqual(
            'The program cannot send any messages after Thu, Jul 18th 2013, 00:00.',
            $this->CreditManagerRenderer->getStatusMessage($creditStatus, $settings));
    }

}