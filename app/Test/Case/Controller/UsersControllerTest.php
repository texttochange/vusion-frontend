<?php
/* Users Test cases generated on: 2012-01-24 15:40:09 : 1327408809*/
App::uses('UsersController', 'Controller');

/**
* TestUsersController *
*/
class TestUsersController extends UsersController
{
    /**
    * Auto render
    *
    * @var boolean
    */
    public $autoRender = false;
    
    
    /**
    * Redirect action
    *
    * @param mixed $url
    * @param mixed $status
    * @param boolean $exit
    * @return void
    */
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
    
    
}

/**
* UsersController Test Case
*
*/
class UsersControllerTestCase extends ControllerTestCase
{
    /**
    * Fixtures
    *
    * @var array
    */
    public $fixtures = array('app.user', 'app.group', 'app.program');
    
    /**
    * setUp method
    *
    * @return void
    */
    public function setUp()
    {
        parent::setUp();
        
        $this->Users = new TestUsersController();
        $this->Users->constructClasses();       
        
    }
    
    
    
    /**
    * tearDown method
    *
    * @return void
    */
    public function tearDown()
    {
        unset($this->Users);
        
        parent::tearDown();
    }
    
    /*
    public function testIndex() 
    {
        
    }
    
    
    public function testView()
    {
        
    }
    
    
    public function testAdd() 
    {
        
    }
    
    */
    public function testEdit_grant_unmatchable_reply_access() 
    {
        $users = $this->generate('Users', array(
            'components' => array(
                'Acl' => array('check', 'allow', 'deny'),
                'Session' => array('read')
                ),
            'models' => array(
                'User' => array('exists', 'save'),
                )
            ));
        
        $users->Session
        ->expects($this->any())
        ->method('read')
        ->will($this->returnValue('User'));
        
        $users->User
        ->expects($this->once())
        ->method('exists')
        ->will($this->returnValue('true'));
        
        $users->User
        ->expects($this->once())
        ->method('save')
        ->with(array(
            'User' =>array(
                'id' => 1,
                'username' => 'jared',
                'unmatchable_reply_access' => 1
                )
            ))
        ->will($this->returnValue(array(
            'User' =>array(
                'id' => 1,
                'username' => 'jared',
                'unmatchable_reply_access' => 1)
            )));
        
        $users->Acl
        ->expects($this->once())
        ->method('allow')
        ->with(array(
            'User' =>array(
                'id' => 1,
                'username' => 'jared',
                'unmatchable_reply_access' => 1)), 
            "controllers/UnmatchableReply")
        ->will($this->returnValue('true'));
        
        $users->Acl
        ->expects($this->once())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $user = array(
            'User'=> array(
                'id' => 1,
                'username' => 'gerald'
                )
            );
        
        $this->testAction("/users/edit/".$user['User']['id'],array(
            'method' => 'post',
            'data' => array(
                'User' => array (
                    'id' => $user['User']['id'],
                    'username' => 'jared',
                    'unmatchable_reply_access' => 1
                    )
                )
            ));

        $this->assertContains('/users/index', $this->headers['Location']);
    }
    
    /*
    public function testEdit_deny_unmatchable_reply_access() 
    {
        $users = $this->generate('Users', array(
            'components' => array(
                'Acl' => array('check', 'allow', 'deny'),
                'Session' => array('read')
                ),
            'models' => array(
                'User' => array('exists', 'save'),
                )
            ));
        
        $users->Session
        ->expects($this->any())
        ->method('read')
        ->will($this->returnValue('User'));
        
        $users->User
        ->expects($this->once())
        ->method('exists')
        ->will($this->returnValue('true'));
        
        $users->User
        ->expects($this->once())
        ->method('save')
        ->with(array(
            'User' =>array(
                'id' => 1,
                'username' => 'jared',
                'unmatchable_reply_access' => 0
                )
            ))
        ->will($this->returnValue('true'));
        
        $users->Acl
        ->expects($this->once())
        ->method('deny')
        ->will($this->returnValue('true'));
        
        $users->Acl
        ->expects($this->once())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $user = array(
            'User'=> array(
                'id' => 1,
                'username' => 'jared',
                'unmatchable_reply_access' => 1
                )
            );
        
        $this->testAction("/users/edit/".$user['User']['id'],array(
            'method' => 'post',
            'data' => array(
                'User' => array (
                    'id' => $user['User']['id'],
                    'username' => 'jared',
                    'unmatchable_reply_access' => 0
                    )
                )
            ));

        $this->assertContains('/users/index', $this->headers['Location']);
    }
    
    
    public function testDelete() 
    {
        
    }
    
    
    public function testChangePassword()
    {
        $hash = Configure::read('Security.salt');
        
        $users = $this->generate('Users', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
                ),
            'models' => array(
                'User' => array('exists', 'read', 'save')
                )
            ));
        
        $users->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $users->Session
        ->expects($this->any())
        ->method('read')
        ->will($this->returnValue('User'));
        
        $users->User
        ->expects($this->once())
        ->method('exists')
        ->will($this->returnValue('true'));
        
        $user = array(
            'User'=> array(
                'id' => 1,
                'password' => Security::hash($hash.'gerald')
                )
            );
        
        $users->User
        ->expects($this->once())
        ->method('read')
        ->will($this->returnValue($user));
        
        $users->User
        ->expects($this->once())
        ->method('save')
        ->with(array(
            'User' =>array(
                'id' => 1,
                'password' => 'jared'
                )
            ))
        ->will($this->returnValue('true'));       
        
        $this->testAction("/users/changePassword/".$user['User']['id'],array(
            'method' => 'post',
            'data' => array(
                'id' => $user['User']['id'],
                'oldPassword' => 'gerald',
                'newPassword' => 'jared',
                'confirmNewPassword' => 'jared'
                )
            ));
        
        $this->assertContains('/users/view/', $this->headers['Location']);
    }
    
    
    public function testFilters()
    {
        $users = $this->generate('Users', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
                )));
        
        $users->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $users->Session
        ->expects($this->any())
        ->method('read')
        ->will($this->returnValue('User'));
        
        $expected = array(
            'id' => 2,
            'username' => 'oliv',
            'password' => 'olivpassword',
            'email' => 'oliv@there.com',
            'group_id' => 2,
            'created' => '2012-01-24 15:34:07',
            'modified' => '2012-01-24 15:34:07'
            );
        
        $expected01 = array(
            'id' => 1,
            'username' => 'gerald',
            'password' => 'geraldpassword',
            'email' => 'gerald@here.com',
            'group_id' => 1,
            'created' => '2012-01-24 15:34:07',
            'modified' => '2012-01-24 15:34:07'
            );
        
        // filter by username only
        $this->testAction("/users/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=username&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=o");
        $this->assertEquals($this->vars['users'][0]['User'], $expected);
        
        //filter by group_id only
        $this->testAction("/users/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=group_id&filter_param%5B1%5D%5B2%5D=is&filter_param%5B1%5D%5B3%5D=1");
        $this->assertEquals($this->vars['users'][0]['User'], $expected01);
        
        // filter by username AND group_id
        $this->testAction("/users/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=username&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=o&filter_param%5B2%5D%5B1%5D=group_id&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=1");
        $this->assertEqual(count($this->vars['users']), 0);
        
        // filter by username OR group_id
        $this->testAction("/users/index?filter_operator=any&filter_param%5B1%5D%5B1%5D=username&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=o&filter_param%5B2%5D%5B1%5D=group_id&filter_param%5B2%5D%5B2%5D=is&filter_param%5B2%5D%5B3%5D=1");
        $this->assertEqual(count($this->vars['users']), 2);
        
    }
    
    
    public function testRequestPasswordReset_fail_invalidEmail()
    {
        $users = $this->generate('Users', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('setFlash')
                ),
            'models' => array(
                'User' => array('find')
                )
            ));
        
        $users->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
         
        $user = array(
            'User'=> array(
                'id' => 1,
                'email' => 'vusion@ttc.com'
                )
            );         
        
        $users->Session
            ->expects($this->once())
            ->method('setFlash')
            ->with('Invalid Email : vusion@ttc2.com');            
        
        $this->testAction('/users/requestPasswordReset', array(  
            'method' => 'post',
            'data' => array(
                'emailEnter' => 'vusion@ttc2.com',
                'captchaField' => '452fGH'
                )
            ));
    }
    
    
    public function testNewPassWord_Reset_Successfully()
    {
        $hash = Configure::read('Security.salt');
        $users = $this->generate('Users', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read', 'setFlash')
                ),
            'models' => array(
                'User' => array('read', 'save')
                )
            ));
        
        $users->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
        
        $user = array(
            'User'=> array(
                'id' => 1,
                'password' => Security::hash($hash.'maxmass')
                )
            );
         
        $users->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->returnValue('User'));       
        
        $users->User
            ->expects($this->once())
            ->method('read')
            ->will($this->returnValue($user));
        
        $users->User
            ->expects($this->once())
            ->method('save')
            ->with(array(
            'User' =>array(
                'id' => 1,
                'password' => 'mark'
                )
            ))
            ->will($this->returnValue('true')); 
            
        $users->Session
            ->expects($this->once())
            ->method('setFlash')
            ->with('Password changed successfully.');
            
        $this->testAction('/users/newPassword',array(
            'method' => 'post',
            'data' => array(                
                'newPassword' => 'mark',
                'confirmPassword' => 'mark'
                )
            ));
    }
    */
    
}
