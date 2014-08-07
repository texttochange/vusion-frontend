<?php

App::uses('AppController', 'Controller');
App::uses('User', 'Model');
App::uses('Group', 'Model');
App::uses('BasicAuthenticate', 'Controller/Component/Auth/');
App::uses('CakeEmail', 'Network/Email');

class UsersController extends AppController
{
    var $components = array('LocalizeUtils', 'ResetPasswordTicket', 'Captcha', 'Email');
    var $uses       = array('User', 'Group');
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        //For initial creation of the admin users uncomment the line below
        $this->Auth->allow('login', 'logout', 'requestPasswordReset', 'captcha', 'useTicket', 'newPassword');
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
        
        $this->paginate        = $paginate;
        $this->User->recursive = 0;
        $this->set('users', $this->paginate("User"));
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
            $umatchableReplyAccess = $this->request->data['User']['unmatchable_reply_access'];
            unset($this->request->data['User']['unmatchable_reply_access']);
            if ($user = $this->User->save($this->request->data)) {
                #checkbox is checked => we store it in the ACL
                if ($umatchableReplyAccess == true) {
                    $this->Acl->allow($user, 'controllers/UnmatchableReply');
                } else {
                    $this->Acl->deny($user, 'controllers/UnmatchableReply');
                }
                $this->Session->setFlash(__('The user has been saved.'),
                    'default',
                    array('class'=>'message success')
                    );
                if ($this->Acl->check(array(
                    'User' => array(
                        'id' => $this->Session->read('Auth.User.id')
                        )
                    ), 'Controllers/Users/index')) {                
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
            #As the information is stored in the ACL we need to retrieve it form the ACL component
            $this->request->data['User']['unmatchable_reply_access'] = $this->Acl->check($this->User, 'controllers/UnmatchableReply');
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
        if ($this->Auth->user()) {
            $this->Session->setFlash(
                __('Already logged in...'),
                'default', 
                array('class'=>'message success'));
            $this->redirect($this->Auth->redirect());
        }
        
        if ($this->request->is('ajax')) {
            if ($this->Auth->login()) {
                return;
            } else {
                throw new UnauthorizedException();
            }
        } else if ($this->request->is('post')) {
            if ($this->Auth->login()) {
                $group     = $this->Group->findById($this->Session->read('Auth.User.group_id'));
                $groupName = $group['Group']['name'];
                $this->Session->write('groupName', $groupName);
                $this->Session->setFlash(__('Login successful.'),
                    'default',
                    array('class'=>'message success'));
                if ($this->Session->read('Auth.User.group_id') == 1) {
                    $this->redirect(array('controller' => 'admin'));
                }
                
                $id          = $this->Auth->user('id');
                $allPrograms = $this->Program->find('authorized', array(
                    'specific_program_access' => 'true',
                    'user_id' => $id));
                if ($this->Session->read('Auth.User.group_id') == 4) {
                    if (count($allPrograms) == 1) {
                        $programUrl = $allPrograms[0]['Program']['url'];
                        $this->redirect(array('program' => $programUrl,
                            'controller' => 'programHome',
                            'action' => 'index'));
                    }
                }
                
                $this->redirect($this->Auth->redirect());
            } else {
                if ($this->request->is('post')) {
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
        $this->Session->destroy();
        $this->redirect($this->Auth->logout());
    }
    
    
    public function changePassword($id = null)
    {
        $hash           = Configure::read('Security.salt');
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
            
            if (Security::hash($hash.$this->request->data['oldPassword']) != $user['User']['password']) {
                $this->Session->setFlash(__('old password is incorrect. Please try again.'),
                    'default',
                    array('class' => "message failure")
                    );
            } else if ($this->request->data['newPassword'] != $this->request->data['confirmNewPassword']) {
                $this->Session->setFlash(__('New passwords doesn\'t match. Please try again.'), 
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
    
    
    public function reportIssue()
    {
        $this->layout = 'popup';
        
        if (!$this->request->is('post')) {
            return;
        }
        
        $userName                 = $this->Session->read('Auth.User.username');        
        $userEmail                = $this->Session->read('Auth.User.email');
        $reportIssueToEmail       = Configure::read('vusion.reportIssue.email');
        $reportIssueSubjectPrefix = Configure::read('vusion.reportIssue.subjectPrefix');
        $reportIssueSubject       = $this->request->data['ReportIssue']['reportIssueSubject'];
        $reportIssueMessage       = $this->request->data['ReportIssue']['reportIssueMessage'];       
        $attachment               = $this->request->data['ReportIssue']['Screenshort'];
        $filePath                 = WWW_ROOT . 'img';
        
        if (!$reportIssueSubject) {
            $this->validationErrors['ReportIssue']['reportIssueSubject'] = __('Describe the issue is missing. Please write the expect vs current behavior.');
            //$this->Session->setFlash(__('Describe the issue is missing. 
            //Please write the expect vs current behavior.'));
            return;
        }
        
        if (!$reportIssueMessage) {
            $this->validationErrors['ReportIssue']['reportIssueMessage'] = __('How to reproduce is missing. Please explain to us how to reproduce the issue on our computers.');
            //$this->Session->setFlash(__('How to reproduce is missing. 
            //Please explain to us how to reproduce the issue on our computers.'));
            return;
        }
        
        if ($attachment['error'] != 0) {
            if ($attachment['error'] == 4) { 
                $message = __("Screenshot is missing. Please take one screenshot and upload the image.");
            } else { 
                $message = __('Error while uploading the file: %s.', $attachment['error']);            
            }
            $this->Session->setFlash($message, 
                'default', array('class' => 'message failure')
                );
            return;
        }
        
        $fileExtension = end(explode('.', $attachment['name']));
        if (!($fileExtension == 'jpg') and !($fileExtension == 'png')) {
            $this->Session->setFlash( __('The file format ".%s" is not supported. Please upload an image .jpg or .png.', $fileExtension)); 
            return ;
        }
       
        copy($attachment['tmp_name'], $filePath . DS . $attachment['name']);
        
        $email = new CakeEmail();
        $email->config('default');
        $email->from($userEmail);
        $email->to($reportIssueToEmail);
        $email->subject($reportIssueSubjectPrefix . " " . $reportIssueSubject);
        $email->template('reportissue_template');
        $email->emailFormat('html');
        $email->viewVars(array(
            'reportIssueSubject' => $reportIssueSubject,
            'reportIssueMessage' => $reportIssueMessage,
            'userName' => $userName));
        $email->attachments($filePath . DS .$attachment['name']);
        $email->send();
        
        $this->Session->setFlash(
            __('The tech team will contact you in the next 2 days by Email. Thank you.'),
            'default', array('class'=>'message success'));
        
        unlink($filePath . DS . $attachment['name']);
        
        return $this->redirect(array('controller' => 'users', 'action' => 'reportIssue'));
    }
    
    
    public function captcha()
    {
        $this->autoRender = false;  
        $this->layout     = 'ajax';
        if (!isset($this->Captcha)) { 
            $this->Captcha = $this->Components->load(
                'Captcha', array(
                    'width' => 150,
                    'height' => 50,
                    'theme' => 'default', 
                    )
                ); 
        }
        $this->Captcha->create();
    }
    
    
    public function requestPasswordReset()
    {
        if (!$this->request->is('post')) {
            return;
        }
        
        $email = $this->request->data['emailEnter'];
        if (!$email) {
            $this->Session->setFlash(__('Please Enter Email address'));
            return;
        }
        
        $account = $this->User->find(
            'all', array('conditions' => array('email' => $email)
                )
            );
        if (!$account) {
            $this->Session->setFlash(__('Invalid Email : '.$email));
            return;
        }
        
        if ($this->request->data['captchaField'] != $this->Captcha->getCaptchaCode()) {
            $this->Session->setFlash(
                __('Please enter correct captcha code and try again.'),
                'default',
                array('class' => "message failure")
                );
            return;
        }        
        $userName = $account[0]['User']['username'];
        $userId   = $account[0]['User']['id'];
        $this->Session->write('user_id',$userId);
        
        $token = md5 (date('mdy').rand(4000000, 4999999));
        $this->ResetPasswordTicket->saveToken($token);
        
        $this->ResetPasswordTicket->sendEmail($email, $userName, $token);
        $this->Session->setFlash(
            __('An Email has been sent to your email account.'),
            'default',
            array('class'=>'message success')
            );
        $this->redirect('/');
    }
    
    
    public function useTicket($ticketHash)
    {
        $results = $this->ResetPasswordTicket->checkTicket($ticketHash);
        if (isset($results)) {
            $this->Session->setFlash(
                __('Enter your new password below'),
                'default',
                array('class'=>'message success')
                );
            $this->render('new_password');
            return;
        }
        $this->Session->setFlash(__('Your ticket is lost or expired.'));
        $this->redirect('/');
    }
    
    
    public function newPassword()
    { 
        $userId = $this->Session->read('user_id');
        $user   = $this->User->read(null, $userId);
        
        if (!$userId) {
            $this->redirect('/');
        } 
        
        if (!$this->request->is('post')) {
            return;
        }
        
        if ($this->request->data['newPassword'] != $this->request->data['confirmPassword']) {
            $this->Session->setFlash(
                __('New passwords doesn\'t match. Please try again.'), 
                'default',
                array('class' => "message failure")
                );
            $this->render('new_password');
            return;
        }
        
        $user['User']['password'] = $this->request->data['newPassword'];
        if ($this->User->save($user)) {
            $this->Session->delete('user_id');
            $this->Session->setFlash(
                __('Password changed successfully.'),
                'default',
                array('class'=>'message success')
                );
            $this->redirect('/');
        } else {        
            $this->Session->setFlash(__('Password saving failed.'), 
                'default',
                array('class' => "message failure")
                );
            $this->render('new_password');          
        }
    }
    
    
    public function initDB()
    {
        echo "Acl Start</br>";
        $Group =& $this->User->Group;
        
        //allow admins to everything
        $group     = $Group->find('first', array('conditions' => array('name' => 'administrator')));
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
            $this->Acl->deny($Group, 'controllers/UnmatchableReply');
            $this->Acl->allow($Group, 'controllers/ProgramUnattachedMessages');
            $this->Acl->allow($Group, 'controllers/ProgramPredefinedMessages');
            $this->Acl->allow($Group, 'controllers/ProgramLogs');
            $this->Acl->allow($Group, 'controllers/Templates');
            $this->Acl->allow($Group, 'controllers/CreditViewer');
            $this->Acl->allow($Group, 'controllers/Users/view');
            $this->Acl->allow($Group, 'controllers/Users/changePassword');
            $this->Acl->allow($Group, 'controllers/Users/edit');
            $this->Acl->allow($Group, 'controllers/Users/requestPasswordReset');
            $this->Acl->allow($Group, 'controllers/ProgramAjax');
            $this->Acl->allow($Group, 'controllers/Users/reportIssue');
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
            $this->Acl->allow($Group, 'controllers/ProgramAjax');
            //$this->Acl->allow($Group, 'controllers/Users/login');
            //$this->Acl->allow($Group, 'controllers/Users/logout');
            $this->Acl->allow($Group, 'controllers/ProgramHome');
            $this->Acl->allow($Group, 'controllers/ProgramParticipants');
            $this->Acl->allow($Group, 'controllers/ProgramDialogues');
            $this->Acl->allow($Group, 'controllers/ProgramHistory');
            $this->Acl->allow($Group, 'controllers/ProgramSettings');
            $this->Acl->allow($Group, 'controllers/ProgramSettings/view');
            $this->Acl->deny($Group, 'controllers/ProgramSettings/edit');            
            //$this->Acl->allow($Group, 'controllers/ProgramSettings/index');
            //$this->Acl->allow($Group, 'controllers/ProgramSettings/view');
            $this->Acl->allow($Group, 'controllers/ProgramSimulator');        
            $this->Acl->allow($Group, 'controllers/ProgramRequests');
            $this->Acl->allow($Group, 'controllers/ProgramContentVariables');
            //$this->Acl->allow($Group, 'controllers/ShortCodes');
            $this->Acl->deny($Group, 'controllers/UnmatchableReply');
            $this->Acl->allow($Group, 'controllers/ProgramUnattachedMessages');
            $this->Acl->allow($Group, 'controllers/ProgramPredefinedMessages');
            $this->Acl->allow($Group, 'controllers/ProgramLogs');
            $this->Acl->allow($Group, 'controllers/Users/view');
            $this->Acl->allow($Group, 'controllers/Users/changePassword');
            $this->Acl->allow($Group, 'controllers/Users/edit');
            $this->Acl->allow($Group, 'controllers/Users/requestPasswordReset');
            $this->Acl->allow($Group, 'controllers/Users/reportIssue');
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
            $this->Acl->allow($Group, 'controllers/ProgramAjax');
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
            $this->Acl->allow($Group, 'controllers/Users/requestPasswordReset');
            $this->Acl->deny($Group, 'controllers/UnmatchableReply');
            $this->Acl->allow($Group, 'controllers/Users/reportIssue');
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
            $this->Acl->allow($Group, 'controllers/ProgramAjax');
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
            $this->Acl->allow($Group, 'controllers/Users/requestPasswordReset');
            $this->Acl->deny($Group, 'controllers/UnmatchableReply');
            $this->Acl->allow($Group, 'controllers/Users/reportIssue');
            echo "Acl Done: ". $group['Group']['name']."</br>";
        }
        
        echo 'AllDone';
        exit;
    }
    
    
}
