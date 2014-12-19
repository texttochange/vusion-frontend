<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('TicketComponent', 'Controller/Component');
App::uses('ScriptMaker', 'Lib');

class TestTicketComponentController extends Controller
{
    var $components = array('Ticket');

    function constructclasses()
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1');
        $this->redisTicketPrefix = 'unittest';
    
    }

}


class TicketComponentTest extends CakeTestCase
{
    public $TicketComponent = null;
    public $Controller = null;
    
    public function setup()
    {
        parent::setUp();
        $Collection = new ComponentCollection();
        $this->TicketComponent = new TicketComponent($Collection);
        $CakeRequest = new CakeRequest();
        $CakeResponse = new CakeResponse();
        
        $this->Controller = new TestTicketComponentController($CakeRequest, $CakeResponse);
        $this->Controller->constructClasses();
        $this->TicketComponent->initialize($this->Controller);
        $this->redis = $this->Controller->redis;
        
    }
    
    
    public function tearDown()
    {
        $keys = $this->redis->keys('unittest*');
        foreach ($keys as $key){
            $this->redis->delete($key);
        }
        unset($this->TicketComponent);
        parent::tearDown();
    }
    

    public function testcheckTicket()
    {
        
        $token = '012546333fg5554dr555seaa453355';
        $this->redisTicketPrefix = 'unittest';
        $key = $this->redisTicketPrefix.':'.$token;
        
        //$this->redis->set($key, $token);
        $this->TicketComponent->saveTicket($token);
        $ticketTest = $this->TicketComponent->checkTicket($token);
        $this->assertEqual($token, $ticketTest);
    }
    
    
    public function testNoTicket_InRedis()
    {
        
        $token = '012546333fg5554dr555seaa33364646';
        $this->redisTicketPrefix = 'unittest';
        $key = $this->redisTicketPrefix.':'.$token;        
        $ticket = $this->redis->get($key);
        
        $this->assertEqual(null, $ticket);
    }


    public function testSaveInvitedTicket()
    {
        $email = 'jack@vusion.com';
        $invite = array(
            'programs' => array('Program' => array(0 => 1)),
            'group_id' => 3,
            'invited_by' => 8
            );
        $token = 'inviteuser';
        $savedInvite = $this->TicketComponent->saveTicket($token, $email, $invite);        

        $ticket = $this->TicketComponent->checkTicket($token);
        $this->assertEqual($invite, $ticket);
    }


    public function testSaveInvitedTicket_more_than_once()
    {
        $email = 'jack@vusion.com';
        $invite = array(
            'programs' => array('Program' => array(0 => 1)),
            'group_id' => 3,
            'invited_by' => 8
            );
        $token = 'inviteuser';
        $savedInvite = $this->TicketComponent->saveTicket($token, $email, $invite);

        $token2 = 'inviteuser2';
        $savedInvite2 = $this->TicketComponent->saveTicket($token2, $email, $invite);        

        $ticket = $this->TicketComponent->checkTicket($token);
        $this->assertEqual(null, $ticket);

        $ticket2 = $this->TicketComponent->checkTicket($token2);
        $this->assertEqual($invite, $ticket2);
    }

}
