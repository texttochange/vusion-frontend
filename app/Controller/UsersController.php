<?php

App::uses('AppController', 'Controller');
App::uses('User', 'Model');

    /**
    *  Users Controller
    *
    *  @property User $User
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
                $this->Session->setFlash(__('The user has been saved'),
                    'default',
                    array('class'=>'success-message')
                );
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }
        }
        $groups   = $this->User->Group->find('list');
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
                $this->Session->setFlash(__('The user has been saved'),
                    'default',
                    array('class'=>'success-message')
                );
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $this->User->read(null, $id);
        }
        $groups   = $this->User->Group->find('list');
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
            $this->Session->setFlash(__('User deleted'),
                'default',
                array('class'=>'success-message')
            );
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('User was not deleted'));
        $this->redirect(array('action' => 'index'));
    }


    public function login()
    {
        if ($this->Auth->login()) {
            $this->Session->setFlash(__('Login successful.'),
                'default',
                array('class'=>'success-message')
            );
            if ($this->Session->read('Auth.User.group_id') == 1) {
                $this->redirect(array('controller' => 'admin'));
            }
            $this->redirect($this->Auth->redirect());
        } else {
            if($this->request->is('post')) {
                $this->Session->setFlash(__('Invalid username or password, try again'));
            }
        }    
    }


    public function logout()
    {
        $this->Session->setFlash(__('Good-Bye'),
            'default',
            array('class'=>'success-message')
        );
        $this->redirect($this->Auth->logout());
    }
    
    
    private $hash = 'DYhG93b001JfIxfs2guVoUubWwvniR2G0FgaC9mi';
    public function changePassword($id = null)
    {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        $user   = $this->User->read(null, $id);
        $userId = $id;
        $this->set(compact('userId'));
        
        if ($this->request->is('post') || $this->request->is('put')) {
            if (Security::hash($this->hash.$this->request->data['oldPassword']) != $user['User']['password']) {
                $this->Session->setFlash(__('old password is incorrect. Please try again.'));
            } else if ($this->request->data['newPassword'] != $this->request->data['confirmNewPassword']) {
                $this->Session->setFlash(__('new passwords do not match. Please try again.'));
            } else {
                $user['User']['password'] = $this->request->data['newPassword'];
                if ($this->User->save($user)) {
                    $this->Session->setFlash(__('Password changed successfully.'),
                        'default',
                        array('class'=>'success-message')
                    );
                    $this->redirect(array('action' => 'view', $id));
                } else {
                    $this->Session->setFlash(__('Password saving failed.'));
                }	
            }
        }
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
        $this->Acl->allow($group, 'controllers/ProgramHome');
        $this->Acl->allow($group, 'controllers/ProgramParticipants');
        $this->Acl->allow($group, 'controllers/ProgramScripts');
        $this->Acl->allow($group, 'controllers/ProgramHistory');
        $this->Acl->allow($group, 'controllers/ProgramSettings');
        $this->Acl->allow($group, 'controllers/ShortCodes');
        $this->Acl->allow($group, 'controllers/UnmatchableReply');
        $this->Acl->allow($group, 'controllers/ProgramUnattachedMessages');
        
        //allow program manager to programs
        $group->id = 3;
        $this->Acl->deny($group, 'controllers');
        $this->Acl->deny($group, 'controllers/Programs');
        $this->Acl->allow($group, 'controllers/Programs/index');        
        //$this->Acl->allow($group, 'controllers/Users/login');
        //$this->Acl->allow($group, 'controllers/Users/logout');
        $this->Acl->allow($group, 'controllers/ProgramHome');
        $this->Acl->allow($group, 'controllers/ProgramParticipants');
        $this->Acl->allow($group, 'controllers/ProgramScripts');
        $this->Acl->allow($group, 'controllers/ProgramHistory');
        $this->Acl->deny($group, 'controllers/ProgramSettings');
        $this->Acl->allow($group, 'controllers/ProgramSettings/index');
        $this->Acl->allow($group, 'controllers/ProgramSettings/view');
        $this->Acl->allow($group, 'controllers/ShortCodes');
        $this->Acl->allow($group, 'controllers/UnmatchableReply');
        $this->Acl->allow($group, 'controllers/ProgramUnattachedMessages');
        
        //allow customer to 
        $group->id = 4;
        $this->Acl->deny($group, 'controllers');
        $this->Acl->allow($group, 'controllers/Programs/index');
        $this->Acl->allow($group, 'controllers/Programs/view');
        //$this->Acl->allow($group, 'controllers/Users/login');
        //$this->Acl->allow($group, 'controllers/Users/logout');
        $this->Acl->allow($group, 'controllers/ProgramHome');
        //$this->Acl->deny($group, 'controllers/ProgramParticipants');
        $this->Acl->deny($group, 'controllers/ProgramParticipants/edit');
        $this->Acl->deny($group, 'controllers/ProgramParticipants/add');
        $this->Acl->allow($group, 'controllers/ProgramParticipants/index');
        $this->Acl->allow($group, 'controllers/ProgramParticipants/view');
        $this->Acl->allow($group, 'controllers/ProgramHistory');

        echo 'AllDone';
        exit;
    }
    

}
