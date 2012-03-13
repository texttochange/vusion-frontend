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
    public function redirect($url, $status = null, $exit = true) {
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
    public $fixtures = array('app.user', 'app.group', 'app.program', 'app.programs_user');

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
        
        $this->instanciateUserModel();
        $this->dropData();
    }
    
    
    protected function instanciateUserModel()
    {
        //$options = array('database');
        $this->Users->User = new User();
    }
    
    
    protected function dropData()
    {
        $this->Users->User->deleteAll(true,false);
    }


/**
 * tearDown method
 *
 * @return void
 */
    public function tearDown()
    {
        $this->dropData();
        
        unset($this->Users);

        parent::tearDown();
    }
    
    
    public function mockUserAccess()
    {
        $user = $this->generate('Users', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
            ),
            'models' => array(
                'User' => array()
                )
            ));
    
        $user->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));

        $user->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->returnValue('User'));	    
 
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
        
       
        //print_r($user);
        
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


}
