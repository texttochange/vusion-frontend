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
    // public $fixtures = array('app.user', 'app.group', 'app.program', 'app.programs_user');

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


   /**
    * testIndex method
    *
    * @return void
    */
    public function testIndex() 
    {

    }


   /**
    * testView method
    *
    * @return void
    */
    public function testView()
    {

    }


   /**
    * testAdd method
    *
    * @return void
    */
    public function testAdd() 
    {

    }


   /**
    * testEdit method
    *
    * @return void
    */
    public function testEdit() 
    {

    }


   /**
    * testDelete method
    *
    * @return void
    */
    public function testDelete() 
    {

    }
    
    
    private $hash = 'DYhG93b001JfIxfs2guVoUubWwvniR2G0FgaC9mi';
    
    
    public function testChangePassword()
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
                        'password' => Security::hash($this->hash.'gerald')
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
            ),
            'models' => array(
                'User' => array(/*'exists',*/ 'find', '_findCount', 'read', 'save'),
                'Group' => array()
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
            
        /*$users->User
            ->expects($this->once())
            ->method('exists')
            ->will($this->returnValue('true'));
         */   
         $user = array(
                    'User'=> array(
                        'id' => 1,
                        'username' => 'mark',
                        'password' => Security::hash($this->hash.'gerald'),
                        'group_id' => 1
                        )
                    );
                
        $users->User
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue($user));
        
        $users->User
            ->expects($this->any())
            ->method('_finCount')
            ->will($this->returnValue(1));
        
        $this->testAction("/users/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=username&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=m");
    }


}
