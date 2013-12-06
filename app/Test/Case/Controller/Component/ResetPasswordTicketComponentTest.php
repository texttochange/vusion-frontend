<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('ResetPasswordTicketComponent', 'Controller/Component');
App::uses('ScriptMaker', 'Lib');

class TestResetPasswordTicketComponentController extends Controller
{
    var $components = array('Ticket');

    function constructclasses()
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1');
        $this->redisTicketPrefix = 'unittest';
    
    }

}


class ResetPasswordTicketComponentTest extends CakeTestCase
{
    public $ResetPasswordTicketComponent = null;
    public $Controller = null;
    
    public function setup()
    {
        parent::setUp();
        $Collection = new ComponentCollection();
        $this->ResetPasswordTicketComponent = new ResetPasswordTicketComponent($Collection);
        $CakeRequest = new CakeRequest();
        $CakeResponse = new CakeResponse();
        
        $this->Controller = new TestResetPasswordTicketComponentController($CakeRequest, $CakeResponse);
        $this->Controller->constructClasses();
        $this->ResetPasswordTicketComponent->initialize($this->Controller);
        $this->redis = $this->Controller->redis;
        
    }
    
    
    public function tearDown()
    {
        $keys = $this->redis->keys('unittest*');
        foreach ($keys as $key){
            $this->redis->delete($key);
        }
        unset($this->ResetPasswordTicketComponent);
        parent::tearDown();
    }
    
    public function testcheckTicket()
    {
        
        $token = '012546333fg5554dr555seaa453355';
        $this->redisTicketPrefix = 'unittest';
        $key = $this->redisTicketPrefix.':'.$token;
        
        $this->redis->set($key, $token);
        $ticketTest = $this->ResetPasswordTicketComponent->checkTicket($token);
        $this->assertEqual(
            $token,
            $ticketTest
            );
    }
    
    
    public function testNoTicket_InRedis()
    {
        
        $token = '012546333fg5554dr555seaa33364646';
        $this->redisTicketPrefix = 'unittest';
        $key = $this->redisTicketPrefix.':'.$token;        
        $ticket = $this->redis->get($key);
        
        $this->assertEqual(
            null,
            $ticket
            );
    }
}