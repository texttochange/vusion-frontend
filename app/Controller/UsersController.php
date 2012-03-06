<?php

App::uses('AppController', 'Controller');

/**
 * Users Controller
 *
 * @property User $User
 */
class UsersController extends AppController
{

    
    public function beforeFilter()
    {
        parent::beforeFilter();
        //For initial creation of the admin users uncomment the line below
        $this->Auth->allow('login', 'logout');
        //$this->Auth->allow('*');
    }


    /**
    * index method
    *
    * @return void
    */
    public function index()
    {
        $this->User->recursive = 0;
        $this->set('users', $this->paginate());
    }


    /**
    * view method
    *
    * @param string $id
    * @return void
    */
    public function view($id = null)
    {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        $this->set('user', $this->User->read(null, $id));
    }


    /**
    * add method
    *
    * @return void
    */
    public function add()
    {
        if ($this->request->is('post')) {
            $this->User->create();
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash(__('The user has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }
        }
        $groups = $this->User->Group->find('list');
        $programs = $this->User->Program->find('list');
        $this->set(compact('groups', 'programs'));
    }


    /**
    * edit method
    *
    * @param string $id
    * @return void
    */
    public function edit($id = null)
    {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash(__('The user has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $this->User->read(null, $id);
        }
        $groups = $this->User->Group->find('list');
        $programs = $this->User->Program->find('list');
        $this->set(compact('groups', 'programs'));
    }


    /**
    * delete method
    *
    * @param string $id
    * @return void
    */
    public function delete($id = null)
    {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->User->delete()) {
            $this->Session->setFlash(__('User deleted'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('User was not deleted'));
        $this->redirect(array('action' => 'index'));
    }


    public function login()
    {
        if ($this->Auth->login()) {
            $this->Session->setFlash(__('Login successful.'));
            $this->redirect($this->Auth->redirect());
        } else {
            if($this->request->is('post')) {
                $this->Session->setFlash(__('Invalid username or password, try again'));
            }
        }    
    }


    public function logout()
    {
        $this->Session->setFlash(__('Good-Bye'));
        $this->redirect($this->Auth->logout());
    }


    public function initDB()
    {
        $group =& $this->User->Group;
        
        //allow admins to everything
        $group->id = 1;
        $this->Acl->allow($group, 'controllers');
        
        //allow manager to users and programs
        $group->id = 2;
        $this->Acl->deny($group, 'controllers');
        $this->Acl->allow($group, 'controllers/Users');
        $this->Acl->allow($group, 'controllers/Programs');
        $this->Acl->allow($group, 'controllers/ProgramsUsers');
        $this->Acl->allow($group, 'controllers/Home');
        $this->Acl->allow($group, 'controllers/Participants');
        $this->Acl->allow($group, 'controllers/Scripts');
        $this->Acl->allow($group, 'controllers/Status');
        $this->Acl->allow($group, 'controllers/ProgramSettings');
        $this->Acl->allow($group, 'controllers/ShortCodes');
        
        //allow program manager to programs
        $group->id = 3;
        $this->Acl->deny($group, 'controllers');
        $this->Acl->allow($group, 'controllers/Programs');        
        //$this->Acl->allow($group, 'controllers/Users/login');
        //$this->Acl->allow($group, 'controllers/Users/logout');
        $this->Acl->allow($group, 'controllers/Home');
        $this->Acl->allow($group, 'controllers/Participants');
        $this->Acl->allow($group, 'controllers/Scripts');
        $this->Acl->allow($group, 'controllers/Status');
        $this->Acl->allow($group, 'controllers/ProgramSettings');
        $this->Acl->allow($group, 'controllers/ShortCodes');
        
        //allow customer to 
        $group->id = 4;
        $this->Acl->deny($group, 'controllers');
        $this->Acl->allow($group, 'controllers/Programs/index');
        $this->Acl->allow($group, 'controllers/Programs/view');
        //$this->Acl->allow($group, 'controllers/Users/login');
        //$this->Acl->allow($group, 'controllers/Users/logout');
        $this->Acl->allow($group, 'controllers/Home');
        //$this->Acl->deny($group, 'controllers/Participants');
        $this->Acl->deny($group, 'controllers/Participants/edit');
        $this->Acl->deny($group, 'controllers/Participants/add');
        $this->Acl->allow($group, 'controllers/Participants/index');
        $this->Acl->allow($group, 'controllers/Participants/view');
        $this->Acl->allow($group, 'controllers/Status');

        echo 'AllDone';
        exit;
    }


}
