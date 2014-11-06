<?php
App::uses('UsersController', 'Controller');


class TestUsersController extends UsersController
{
    public $autoRender = false;
    
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }    
}


class UsersControllerTestCase extends ControllerTestCase
{
    
    public $fixtures = array('app.user', 'app.group', 'app.program');
    
    
    public function setUp()
    {
        parent::setUp();
        
        $this->Users = new TestUsersController();
        $this->Users->constructClasses();       
        
    }
    
    
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
        
        $mockedUser = array(
            'User' =>array(
                'id' => 1,
                'username' => 'jared'
                )
            );
        
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
        ->with($mockedUser)
        ->will($this->returnValue($mockedUser));
        
        $users->Acl
        ->expects($this->once())
        ->method('allow')
        ->with($mockedUser, 
            "controllers/UnmatchableReply")
        ->will($this->returnValue('true'));
        
        $users->Acl
        ->expects($this->once())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $formUser = $mockedUser;
        $formUser['User']['unmatchable_reply_access'] = true;
        $formUser['User']['can_invite_users'] = false;
        
        $this->testAction("/users/edit/".$formUser['User']['id'],array(
            'method' => 'post',
            'data' => $formUser
            ));

        $this->assertContains('/users/index', $this->headers['Location']);
    }
    
    
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
        
        
        
        $mockedUser = array(
            'User' =>array(
                'id' => 1,
                'username' => 'jared'
                )
            );
        
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
        ->with($mockedUser)
        ->will($this->returnValue($mockedUser));
        
        $users->Acl
        ->expects($this->at(0))
        ->method('deny')
        ->with($mockedUser, 
            "controllers/UnmatchableReply")
        ->will($this->returnValue('true'));

        $users->Acl
        ->expects($this->at(1))
        ->method('deny')
        ->with($mockedUser, 
            "controllers/Users")
        ->will($this->returnValue('true'));
        
        $users->Acl
        ->expects($this->once())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $formUser = $mockedUser;
        $formUser['User']['unmatchable_reply_access'] = false;
        $formUser['User']['can_invite_users'] = false;
        
        $this->testAction("/users/edit/".$formUser['User']['id'],array(
            'method' => 'post',
            'data' => $formUser
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
    
    
    public function testNewPassWord_reset_ok()
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
    
    
    public function testReportIssue_fail_fieldsEmpty()
    {
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
       
        $this->testAction('/users/reportIssue', array(  
            'method' => 'post',
            'data' => array('ReportIssue' => array(
                'subject' => '',
                'message' => '',
                'screenshot'=> array(
                    'name' => 'hi.gif' ,
                    'error'=>0)
                ))
            ));
        
        $this->assertEqual($this->vars['validationErrors']['subject'][0],
            'Please describe the expect vs current behavior.');
        $this->assertEqual($this->vars['validationErrors']['message'][0],
            'Please explain us how to reproduce the issue on our computers.');
        $this->assertEqual($this->vars['validationErrors']['screenshot'][0],
            'The file format ".gif" is not supported. Please upload an image .jpg or .png.');
        
    }
    
    
    public function testReportIssue_ok()
    {
        $users = $this->generate('Users', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read', 'setFlash', 'write'),
                'Auth' => array('user')
                ),
            'models' => array(
                'User' => array('read', 'save')
                )
            ));
        
        $users->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $users->Session
        ->expects($this->at(0))
        ->method('read')
        ->with('Auth.User.username')
        ->will($this->returnValue('maxmass'));
        
        $users->Session
        ->expects($this->at(1))
        ->method('read')
        ->with('Auth.User.email')
        ->will($this->returnValue('vusion@ttc.com'));
        
        $CakeEmail = $this->getMock('CakeEmail', array(
            'from',
            'send'));
        
        $CakeEmail
        ->expects($this->any())
        ->method('send')
        ->will($this->returnValue('true')); 
        
        $CakeEmail
        ->expects($this->any())
        ->method('from')
        ->with($this->equalTo('vusion@ttc.com')); 
        
        $users->CakeEmail = $CakeEmail;
        
        $users->Session
        ->expects($this->any())
        ->method('setFlash')
        ->with('The tech team will contact you in the next 2 days by Email. Thank you.');
        
        $this->testAction('/users/reportIssue', array(  
            'method' => 'post',
            'data' => array('ReportIssue' => array(
                'subject' => 'testing email subject',
                'message' => 'testing email message',
                'screenshot'=> array(
                    'name' => 'hi.jpg',
                    'error'=>0,
                    'tmp_name'=> TESTS . 'files/reportIssue_test_image.png')
                ))
            ));
    }
    
    
    public function testReportIssue_fail_connectionRefused()
    {
        $users = $this->generate('Users', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read', 'setFlash', 'write'),
                'Auth' => array('user')
                ),
            'models' => array(
                'User' => array('read', 'save')
                )
            ));
        
        $users->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $users->Session
        ->expects($this->at(0))
        ->method('read')
        ->with('Auth.User.username')
        ->will($this->returnValue('maxmass'));
        
        $users->Session
        ->expects($this->at(1))
        ->method('read')
        ->with('Auth.User.email')
        ->will($this->returnValue('vusion@ttc.com'));
        
        $CakeEmail = $this->getMock('CakeEmail', array('send'));
        
        $CakeEmail
        ->expects($this->any())
        ->method('send')
        ->will($this->throwException(new SocketException('Email server connection is down. Please send report to vusion-issue@texttochange.com')));
        
        $users->CakeEmail = $CakeEmail;
        
        $users->Session
        ->expects($this->any())
        ->method('setFlash')    
        ->with('Email server connection is down. Please send report to vusion-issues@texttochange.com');
        
        $this->testAction('/users/reportIssue', array(  
            'method' => 'post',
            'data' => array('ReportIssue' => array(
                'subject' => 'testing email subject',
                'message' => 'testing email message',
                'screenshot'=> array(
                    'name' => 'hi.jpg',
                    'error'=>0,
                    'tmp_name'=> TESTS . 'files/reportIssue_test_image.png')
                ))
            ));
    }
    
    
    public function testReportIssue_fail_otherExceptions()
    {
        $users = $this->generate('Users', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read', 'setFlash', 'write'),
                'Auth' => array('user')
                ),
            'models' => array(
                'User' => array('read', 'save')
                )
            ));
        
        $users->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $users->Session
        ->expects($this->at(0))
        ->method('read')
        ->with('Auth.User.username')
        ->will($this->returnValue('maxmass'));
        
        $users->Session
        ->expects($this->at(1))
        ->method('read')
        ->with('Auth.User.email')
        ->will($this->returnValue('vusion@ttc.com'));
        
        $CakeEmail = $this->getMock('CakeEmail', array('send'));
        
        $CakeEmail
        ->expects($this->any())
        ->method('send')
        ->will($this->throwException(new Exception('Email server is down')));
        
        $users->CakeEmail = $CakeEmail;
        
        $users->Session
        ->expects($this->any())
        ->method('setFlash')    
        ->with('"Email server is down". Please send report to vusion-issues@texttochange.com');
        
        $this->testAction('/users/reportIssue', array(  
            'method' => 'post',
            'data' => array('ReportIssue' => array(
                'subject' => 'testing email subject',
                'message' => 'testing email message',
                'screenshot'=> array(
                    'name' => 'hi.jpg',
                    'error'=>0,
                    'tmp_name'=> TESTS . 'files/reportIssue_test_image.png')
                ))
            ));
    }
    
    
    public function testEdit_grant_can_invite_users() 
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
        
        $mockedUser = array(
            'User' =>array(
                'id' => 1,
                'username' => 'jared'
                )
            );
        
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
        ->with($mockedUser)
        ->will($this->returnValue($mockedUser));
        
        $users->Acl
        ->expects($this->at(0))
        ->method('deny')
        ->with($mockedUser, 
            "controllers/UnmatchableReply")
        ->will($this->returnValue('true'));
        
        $users->Acl
        ->expects($this->at(1))
        ->method('allow')
        ->with($mockedUser, 
            "controllers/Users/index")
        ->will($this->returnValue('true'));

        $users->Acl
        ->expects($this->at(2))
        ->method('allow')
        ->with($mockedUser, 
            "controllers/Users/delete")
        ->will($this->returnValue('true'));
        
        $users->Acl
        ->expects($this->once())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $formUser = $mockedUser;
        $formUser['User']['unmatchable_reply_access'] = false;
        $formUser['User']['can_invite_users'] = true;
        
        $this->testAction("/users/edit/".$formUser['User']['id'],array(
            'method' => 'post',
            'data' => $formUser
            ));

        $this->assertContains('/users/index', $this->headers['Location']);
    }
    
    
    public function testEdit_deny_can_invite_users() 
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
        
        
        
        $mockedUser = array(
            'User' =>array(
                'id' => 1,
                'username' => 'jared'
                )
            );
        
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
        ->with($mockedUser)
        ->will($this->returnValue($mockedUser));
        
        $users->Acl
        ->expects($this->at(0))
        ->method('deny')
        ->with($mockedUser, 
            "controllers/UnmatchableReply")
        ->will($this->returnValue('true'));

        $users->Acl
        ->expects($this->at(1))
        ->method('deny')
        ->with($mockedUser, 
            "controllers/Users")
        ->will($this->returnValue('true'));
        
        $users->Acl
        ->expects($this->once())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $formUser = $mockedUser;
        $formUser['User']['unmatchable_reply_access'] = false;
        $formUser['User']['can_invite_users'] = false;
        
        $this->testAction("/users/edit/".$formUser['User']['id'],array(
            'method' => 'post',
            'data' => $formUser
            ));

        $this->assertContains('/users/index', $this->headers['Location']);
    }
}
