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
        
        $ticket = '012546333fg5554dr555seaa453355';
        $this->redisTicketPrefix = 'unittest';
        $key = $this->redisTicketPrefix.':'.$ticket;
        
        $this->TicketComponent->saveTicket($ticket);
        $ticketTest = $this->TicketComponent->checkTicket($ticket);
        $this->assertEqual($ticket, $ticketTest);
    }
    
    
    public function testNoTicket_InRedis()
    {
        
        $ticket = '012546333fg5554dr555seaa33364646';
        $this->redisTicketPrefix = 'unittest';
        $key = $this->redisTicketPrefix.':'.$ticket;        
        $savedTicket = $this->redis->get($key);
        
        $this->assertEqual(null, $savedTicket);
    }


    public function testSaveInvitedTicket()
    {
        $email = 'jack@vusion.com';
        $invite = array(
            'programs' => array('Program' => array(0 => 1)),
            'group_id' => 3,
            'invited_by' => 8
            );
        $ticket = 'inviteuser';
        $savedInvite = $this->TicketComponent->saveTicket($ticket, $email, $invite);        

        $savedTicket = $this->TicketComponent->checkTicket($ticket);
        $this->assertEqual($invite, $savedTicket);
    }


    public function testSaveInvitedTicket_more_than_once()
    {
        $email = 'jack@vusion.com';
        $invite = array(
            'programs' => array('Program' => array(0 => 1)),
            'group_id' => 3,
            'invited_by' => 8
            );
        $ticket = 'inviteuser';
        $savedInvite = $this->TicketComponent->saveTicket($ticket, $email, $invite);

        $ticket2 = 'inviteuser2';
        $savedInvite2 = $this->TicketComponent->saveTicket($ticket2, $email, $invite);        

        $savedTicket = $this->TicketComponent->checkTicket($ticket);
        $this->assertEqual(null, $savedTicket);

        $savedTicket2 = $this->TicketComponent->checkTicket($ticket2);
        $this->assertEqual($invite, $savedTicket2);
    }

}
