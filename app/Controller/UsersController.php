<?php

App::uses('AppController', 'Controller');
App::uses('User', 'Model');
App::uses('BasicAuthenticate', 'Controller/Component/Auth/');

class UsersController extends AppController
{
    var $components = array('LocalizeUtils');
    
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
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        $paginate = array('all');
        
        if (isset($this->params['named']['sort'])) {
            $paginate['order'] = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        
        $conditions = $this->_getConditions();
        if ($conditions != null) {
            $paginate['conditions'] = $conditions;
        }
        
        $this->paginate = $paginate;
        
        $this->User->recursive = 0;
        $this->set('users', $this->paginate());
    }


    protected function _getFilterFieldOptions()
    {   
        return $this->LocalizeUtils->localizeLabelInArray(
            $this->User->filterFields);
    }


    protected function _getFilterParameterOptions()
    {
        $groups = $this->User->Group->find('list');
        
        return array(
            'operator' => $this->LocalizeUtils->localizeValueInArray($this->User->filterOperatorOptions),
            'group' => $groups           
            );
       
    }
    
    
    protected function _getConditions()
    {
       $filter = array_intersect_key($this->params['url'], array_flip(array('filter_param', 'filter_operator')));

        if (!isset($filter['filter_param'])) 
            return null;

        if (!isset($filter['filter_operator']) || !in_array($filter['filter_operator'], $this->User->filterOperatorOptions)) {
            throw new FilterException('Filter operator is missing or not allowed.');
        }     

        $this->set('urlParams', http_build_query($filter));

        return $this->User->fromFilterToQueryConditions($filter);
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
        echo "Acl Start</br>";
        $Group =& $this->User->Group;
        
        //allow admins to everything
        $group = $Group->find('first', array('conditions' => array('name' => 'administrator')));
        $Group->id = $group['Group']['id'];
        $this->Acl->allow($Group, 'controllers');
        echo "Acl Done: ". $group['Group']['name']."</br>";
        
        //allow manager to users and programs
        $group = $Group->find('first', array('conditions' => array('name' => 'manager')));
        if ($group == null) {
            echo "Acl ERROR: cannot find the group manager</br>";
        } else {
            $Group->id = $group['Group']['id'];
            $this->Acl->deny($Group, 'controllers');
            $this->Acl->allow($Group, 'controllers/Users');
            $this->Acl->allow($Group, 'controllers/Programs');
            $this->Acl->allow($Group, 'controllers/ProgramsUsers');
            $this->Acl->allow($Group, 'controllers/ProgramHome');
            $this->Acl->allow($Group, 'controllers/ProgramParticipants');
            $this->Acl->allow($Group, 'controllers/ProgramDialogues');
            $this->Acl->allow($Group, 'controllers/ProgramHistory');
            $this->Acl->allow($Group, 'controllers/ProgramSettings');
            $this->Acl->allow($Group, 'controllers/ProgramSimulator');
            $this->Acl->allow($Group, 'controllers/ProgramRequests');
            $this->Acl->allow($Group, 'controllers/ProgramContentVariables');
            $this->Acl->allow($Group, 'controllers/ShortCodes');
            $this->Acl->allow($Group, 'controllers/UnmatchableReply');
            $this->Acl->allow($Group, 'controllers/ProgramUnattachedMessages');
            $this->Acl->allow($Group, 'controllers/ProgramPredefinedMessages');
            $this->Acl->allow($Group, 'controllers/ProgramLogs');
            $this->Acl->allow($Group, 'controllers/Templates');
            $this->Acl->allow($Group, 'controllers/Users/view');
            $this->Acl->allow($Group, 'controllers/Users/changePassword');
            $this->Acl->allow($Group, 'controllers/Users/edit');
            echo "Acl Done: ". $group['Group']['name']."</br>";
        }
        
        //allow program manager to programs
        $group = $Group->find('first', array('conditions' => array('name' => 'program manager')));
        if ($group == null) {
            echo "Acl ERROR: cannot find the group program manager</br>";
        } else {
            $Group->id = $group['Group']['id']."</br";
            $this->Acl->deny($Group, 'controllers');
            $this->Acl->deny($Group, 'controllers/Programs');
            $this->Acl->allow($Group, 'controllers/Programs/index');        
            //$this->Acl->allow($Group, 'controllers/Users/login');
            //$this->Acl->allow($Group, 'controllers/Users/logout');
            $this->Acl->allow($Group, 'controllers/ProgramHome');
            $this->Acl->allow($Group, 'controllers/ProgramParticipants');
            $this->Acl->allow($Group, 'controllers/ProgramDialogues');
            $this->Acl->allow($Group, 'controllers/ProgramHistory');
            $this->Acl->allow($Group, 'controllers/ProgramSettings');
            $this->Acl->allow($Group, 'controllers/ProgramSettings/view');
            $this->Acl->allow($Group, 'controllers/ProgramSettings/edit');
            //$this->Acl->allow($Group, 'controllers/ProgramSettings/index');
            //$this->Acl->allow($Group, 'controllers/ProgramSettings/view');
            $this->Acl->allow($Group, 'controllers/ProgramSimulator');        
            $this->Acl->allow($Group, 'controllers/ProgramRequests');
            $this->Acl->allow($Group, 'controllers/ProgramContentVariables');
            $this->Acl->allow($Group, 'controllers/ShortCodes');
            $this->Acl->deny($Group, 'controllers/UnmatchableReply');
            $this->Acl->allow($Group, 'controllers/ProgramUnattachedMessages');
            $this->Acl->allow($Group, 'controllers/ProgramPredefinedMessages');
            $this->Acl->allow($Group, 'controllers/ProgramLogs');
            $this->Acl->allow($Group, 'controllers/Users/view');
            $this->Acl->allow($Group, 'controllers/Users/changePassword');
            $this->Acl->allow($Group, 'controllers/Users/edit');
            echo "Acl Done: ". $group['Group']['name']."</br>";
        }
        
        //allow partner to 
        $group = $Group->find('first', array('conditions' => array('name' => 'partner')));
                if ($group == null) {
            echo "Acl ERROR: cannot find the partner</br>";
        } else {
            $Group->id = $group['Group']['id']."</br";
            $this->Acl->deny($Group, 'controllers');
            $this->Acl->allow($Group, 'controllers/Programs/index');
            $this->Acl->allow($Group, 'controllers/Programs/view');
            //$this->Acl->allow($Group, 'controllers/Users/login');
            //$this->Acl->allow($Group, 'controllers/Users/logout');
            $this->Acl->allow($Group, 'controllers/ProgramHome');
            //$this->Acl->deny($Group, 'controllers/ProgramParticipants');
            $this->Acl->deny($Group, 'controllers/ProgramParticipants/edit');
            $this->Acl->deny($Group, 'controllers/ProgramParticipants/add');
            $this->Acl->allow($Group, 'controllers/ProgramParticipants/index');
            $this->Acl->allow($Group, 'controllers/ProgramParticipants/view');
            $this->Acl->allow($Group, 'controllers/ProgramParticipants/export');
            $this->Acl->allow($Group, 'controllers/ProgramParticipants/download');
            $this->Acl->allow($Group, 'controllers/ProgramParticipants/getFilterParameterOptions');
            $this->Acl->deny($Group, 'controllers/ProgramParticipants/reset');
            $this->Acl->deny($Group, 'controllers/ProgramParticipants/optin');
            $this->Acl->deny($Group, 'controllers/ProgramParticipants/optout');
            $this->Acl->allow($Group, 'controllers/ProgramHistory/index');
            $this->Acl->allow($Group, 'controllers/ProgramHistory/export');
            $this->Acl->allow($Group, 'controllers/ProgramHistory/download');
            $this->Acl->deny($Group, 'controllers/ProgramHistory/delete');
            $this->Acl->allow($Group, 'controllers/Users/view');
            $this->Acl->allow($Group, 'controllers/Users/changePassword');
            $this->Acl->allow($Group, 'controllers/Users/edit');
            echo "Acl Done: ". $group['Group']['name']."</br>";
        }

        //allow partner messager to
        $group = $Group->find('first', array('conditions' => array('name' => 'partner manager')));
        if ($group == null) {
            echo "Acl ERROR: cannot find the group partner manager</br>";
        } else {
    
            $Group->id = $group['Group']['id'];
            $this->Acl->deny($Group, 'controllers');
            $this->Acl->allow($Group, 'controllers/Programs/index');
            $this->Acl->allow($Group, 'controllers/Programs/view');
            $this->Acl->allow($Group, 'controllers/ProgramHome');
            $this->Acl->allow($Group, 'controllers/ProgramParticipants');
            $this->Acl->allow($Group, 'controllers/ProgramHistory/index');
            $this->Acl->allow($Group, 'controllers/ProgramHistory/export');
            $this->Acl->allow($Group, 'controllers/ProgramHistory/download');
            $this->Acl->deny($Group, 'controllers/ProgramHistory/delete');
            $this->Acl->allow($Group, 'controllers/ProgramUnattachedMessages');
            $this->Acl->allow($Group, 'controllers/ProgramPredefinedMessages');
            $this->Acl->allow($Group, 'controllers/Users/view');
            $this->Acl->allow($Group, 'controllers/Users/changePassword');
            $this->Acl->allow($Group, 'controllers/Users/edit');
            echo "Acl Done: ". $group['Group']['name']."</br>";
        }
                
        echo 'AllDone';
        exit;
    }
    

}
