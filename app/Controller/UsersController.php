<?php

App::uses('AppController', 'Controller');
App::uses('User', 'Model');
App::uses('BasicAuthenticate', 'Controller/Component/Auth/');

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
            throw new NotFoundException(__('Invalid user.'));
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
                $this->Session->setFlash(__('The user has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'), 
                'default',
                array('class' => "message failure")
                );
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
        
        if ($this->Auth->user('group_id') != 1 && $id != $this->Auth->user('id')) {
            $this->Session->setFlash(__('Stop trying to ACCESS this user, you have been redirected to your page'),
                'default',
                array('class' => "message failure"));
            $this->redirect(array('action' => 'edit', $this->Auth->user('id')));
        }            
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user.'));
        } 
        
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash(__('The user has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                if ($this->Acl->check(array(
                    'User' => array(
                        'id' => $this->Session->read('Auth.User.id')
                        )
                    ), 'Controllers/Users/index')){                
                $this->redirect(array('action' => 'index'));
                    } else {                  
                        $this->redirect(array('action' => 'view', $this->Session->read('Auth.User.id')));
                    }
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'), 
                'default',
                array('class' => "message failure")
                );
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
            throw new NotFoundException(__('Invalid user.'));
        }
        if ($this->User->delete()) {
            $this->Session->setFlash(__('User deleted.'),
                'default',
                array('class'=>'message success')
            );
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('User was not deleted.'), 
                'default',
                array('class' => "message failure")
                );
        $this->redirect(array('action' => 'index'));
    }


    public function login()
    {
        if ($this->request->is('post')) {
            if ($this->Auth->login()) {
                $this->Session->setFlash(__('Login successful.'),
                    'default',
                    array('class'=>'message success')
                    );
                if ($this->Session->read('Auth.User.group_id') == 1) {
                    $this->redirect(array('controller' => 'admin'));
                }
                $this->redirect($this->Auth->redirect());
            } else {
                if($this->request->is('post')) {
                    $this->Session->setFlash(__('Invalid username or password, try again.'));
                }
                return $this->redirect(array('controller' => 'users', 'action' => 'login'));
             }
        }
    }


    public function logout()
    {
        $this->Session->setFlash(__('Good Bye'),
            'default',
            array('class'=>'message success')
        );
        $this->redirect($this->Auth->logout());
    }
    
    
    private $hash = 'DYhG93b001JfIxfs2guVoUubWwvniR2G0FgaC9mi';
    public function changePassword($id = null)
    {
        $this->User->id = $id;
        
        if ($this->Auth->user('group_id') != 1 && $id != $this->Auth->user('id')) {
            $this->Session->setFlash(__('Stop trying to ACCESS this user, you have been redirected to your page'),
                'default',
                array('class' => "message failure"));
            $this->redirect(array('action' => 'changePassword', $this->Auth->user('id')));
        }            
        
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user.'));
        }
        $user   = $this->User->read(null, $id);
        $userId = $id;
        $this->set(compact('userId'));
        
        if ($this->request->is('post') || $this->request->is('put')) {
            if (Security::hash($this->hash.$this->request->data['oldPassword']) != $user['User']['password']) {
                $this->Session->setFlash(__('old password is incorrect. Please try again.'), 
                'default',
                array('class' => "message failure")
                );
            } else if ($this->request->data['newPassword'] != $this->request->data['confirmNewPassword']) {
                $this->Session->setFlash(__('new passwords do not match. Please try again.'), 
                'default',
                array('class' => "message failure")
                );
            } else {
                $user['User']['password'] = $this->request->data['newPassword'];
                if ($this->User->save($user)) {
                    $this->Session->setFlash(__('Password changed successfully.'),
                        'default',
                        array('class'=>'message success')
                    );
                    $this->redirect(array('action' => 'view', $id));
                } else {
                    $this->Session->setFlash(__('Password saving failed.'), 
                        'default',
                        array('class' => "message failure")
                        );
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
        $this->Acl->allow($group, 'controllers/ProgramDialogues');
        $this->Acl->allow($group, 'controllers/ProgramHistory');
        $this->Acl->allow($group, 'controllers/ProgramSettings');
        $this->Acl->allow($group, 'controllers/ProgramSimulator');
        $this->Acl->allow($group, 'controllers/ProgramRequests');
        $this->Acl->allow($group, 'controllers/ShortCodes');
        $this->Acl->allow($group, 'controllers/UnmatchableReply');
        $this->Acl->allow($group, 'controllers/ProgramUnattachedMessages');
        $this->Acl->allow($group, 'controllers/ProgramLogs');
        $this->Acl->allow($group, 'controllers/Templates');
        $this->Acl->allow($group, 'controllers/Users/view');
        $this->Acl->allow($group, 'controllers/Users/changePassword');
        $this->Acl->allow($group, 'controllers/Users/edit');
        
        //allow program manager to programs
        $group->id = 3;
        $this->Acl->deny($group, 'controllers');
        $this->Acl->deny($group, 'controllers/Programs');
        $this->Acl->allow($group, 'controllers/Programs/index');        
        //$this->Acl->allow($group, 'controllers/Users/login');
        //$this->Acl->allow($group, 'controllers/Users/logout');
        $this->Acl->allow($group, 'controllers/ProgramHome');
        $this->Acl->allow($group, 'controllers/ProgramParticipants');
        $this->Acl->allow($group, 'controllers/ProgramDialogues');
        $this->Acl->allow($group, 'controllers/ProgramHistory');
        $this->Acl->allow($group, 'controllers/ProgramSettings');
        $this->Acl->allow($group, 'controllers/ProgramSettings/view');
        $this->Acl->allow($group, 'controllers/ProgramSettings/edit');
        //$this->Acl->allow($group, 'controllers/ProgramSettings/index');
        //$this->Acl->allow($group, 'controllers/ProgramSettings/view');
        $this->Acl->allow($group, 'controllers/ProgramSimulator');        
        $this->Acl->allow($group, 'controllers/ProgramRequests');
        $this->Acl->allow($group, 'controllers/ShortCodes');
        $this->Acl->deny($group, 'controllers/UnmatchableReply');
        $this->Acl->allow($group, 'controllers/ProgramUnattachedMessages');
        $this->Acl->allow($group, 'controllers/ProgramLogs');
        $this->Acl->allow($group, 'controllers/Users/view');
        $this->Acl->allow($group, 'controllers/Users/changePassword');
        $this->Acl->allow($group, 'controllers/Users/edit');
        
        //allow partner to 
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
        $this->Acl->allow($group, 'controllers/ProgramParticipants/export');
        $this->Acl->allow($group, 'controllers/ProgramParticipants/download');
        $this->Acl->deny($group, 'controllers/ProgramParticipants/reset');
        $this->Acl->deny($group, 'controllers/ProgramParticipants/optin');
        $this->Acl->deny($group, 'controllers/ProgramParticipants/optout');
        $this->Acl->allow($group, 'controllers/ProgramHistory/index');
        $this->Acl->allow($group, 'controllers/ProgramHistory/export');
        $this->Acl->deny($group, 'controllers/ProgramHistory/delete');
        $this->Acl->allow($group, 'controllers/Users/view');
        $this->Acl->allow($group, 'controllers/Users/changePassword');
        $this->Acl->allow($group, 'controllers/Users/edit');
        
        //allow program messager to 
        $group->id = 5;
        $this->Acl->deny($group, 'controllers');
        $this->Acl->allow($group, 'controllers/Programs/index');
        $this->Acl->allow($group, 'controllers/Programs/view');
        $this->Acl->allow($group, 'controllers/ProgramHome');
        $this->Acl->allow($group, 'controllers/ProgramParticipants');
        $this->Acl->allow($group, 'controllers/ProgramHistory/index');
        $this->Acl->allow($group, 'controllers/ProgramHistory/export');
        $this->Acl->deny($group, 'controllers/ProgramHistory/delete');
        $this->Acl->allow($group, 'controllers/ProgramUnattachedMessages');
        $this->Acl->allow($group, 'controllers/Users/view');
        $this->Acl->allow($group, 'controllers/Users/changePassword');
        $this->Acl->allow($group, 'controllers/Users/edit');
        
        echo 'AllDone';
        exit;
    }
    

}
