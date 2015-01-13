<?php

App::uses('AppController', 'Controller');
App::uses('User', 'Model');
App::uses('Group', 'Model');
App::uses('BasicAuthenticate', 'Controller/Component/Auth/');
App::uses('CakeEmail', 'Network/Email');
App::uses('VusionConst', 'Lib');

class UsersController extends AppController
{
    public $CakeEmail = null;
    
    var $uses = array(
        'User',
        'Group');
    var $components = array(
        'LocalizeUtils', 
        'Captcha',
        'Email',
        'Filter',
        'Ticket');
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        //For initial creation of the admin users uncomment the line below
        $this->Auth->allow(
            'login',
            'logout',
            'requestPasswordReset',
            'captcha',
            'useTicket',
            'newPassword',
            'addInvitee');
    }
    
    
    public function index()
    {
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        $paginate = array('all');
        $defaultConditions = array();        
        
        if ($this->Auth->user('group_id') != 1) {
            $defaultConditions = array('invited_by' => $this->Auth->user('id'));
        }

        if (isset($this->params['named']['sort'])) {
            $paginate['order'] = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        
        $conditions = $this->Filter->getConditions($this->User, $defaultConditions);
        if ($conditions != null) {
            $paginate['conditions'] = $conditions;
        }
        
        $this->paginate        = $paginate;
        $this->User->recursive = 0;
        $users = $this->paginate("User");
        # to display a username in invited_by field
        foreach($users as &$user) {
            $username = $this->User->find('first', array(
                'fields' => array('User.username'),
                'conditions' =>array('User.id' => $user['User']['invited_by'])
                ));
            $user['User']['invited_by'] = ($username ? $username['User']['username']: __("admin"));
        }
        $this->set(compact('users'));
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
    
    
    public function view($id = null)
    {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user.'));
        }
        $this->set('user', $this->User->read(null, $id));
    }
    
    
    public function add()
    {
        if ($this->request->is('post')) {
            $this->User->create();
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash(
                    __('The user has been saved.'),
                    'default',
                    array('class'=>'message success')
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
    
    
    public function edit($id = null)
    {
        $this->User->id = $id;
        
        if ($this->Auth->user('group_id') != 1 && $id != $this->Auth->user('id')) {
            $this->Session->setFlash(__('You are not allowed to edit another user\'s details, your tentative has been reported to Vusion adminstrator.'));
            $this->redirect(array('action' => 'edit', $this->Auth->user('id')));
        }            
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user.'));
        } 
        
        if ($this->request->is('post')) {
            $userId = $this->request->data['User']['id'];
            if ($this->Auth->user('group_id') != 1 && $userId != $this->Auth->user('id')) {
                $this->Session->setFlash(__('You are not allowed to edit another user\'s details, your tentative has been reported to Vusion adminstrator.'));
                $this->redirect(array('action' => 'edit', $this->Auth->user('id')));
            }  
            
            $umatchableReplyAccess = $this->request->data['User']['unmatchable_reply_access'];
            unset($this->request->data['User']['unmatchable_reply_access']);
            $canInviteUsers = $this->request->data['User']['can_invite_users'];
            unset($this->request->data['User']['can_invite_users']);
            if ($user = $this->User->save($this->request->data)) {
                ##### To Refactor ###############
                #checkbox is checked => we store it in the ACL
                if ($umatchableReplyAccess == true) {
                    $this->Acl->allow($user, 'controllers/UnmatchableReply');
                } else {
                    $this->Acl->deny($user, 'controllers/UnmatchableReply');
                }

                if ($canInviteUsers == true) {
                    $this->Acl->allow($user, 'controllers/Users/inviteUser');
                    $this->Acl->allow($user, 'controllers/Users/index');
                    $this->Acl->allow($user, 'controllers/Users/delete');
                } else {
                    $this->Acl->deny($user, 'controllers/Users/index');
                    $this->Acl->deny($user, 'controllers/Users/delete');
                    $this->Acl->deny($user, 'controllers/Users/inviteUser');
                }
                ########################################################

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
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $this->User->read(null, $id);
            ##As the information is stored in the ACL we need to retrieve it form the ACL component
            $this->request->data['User']['unmatchable_reply_access'] = $this->Acl->check($this->User, 'controllers/UnmatchableReply');
            $this->request->data['User']['can_invite_users'] = $this->Acl->check($this->User, 'controllers/Users/inviteUser');
        }
        $groups   = $this->User->Group->find('list');
        $programs = $this->User->Program->find('list');
        $this->set(compact('groups', 'programs'));
    }
    
    
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
        $this->Session->setFlash(__('User was not deleted.'));
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
        
        if ($this->_isAjax()) {
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
            $this->Session->setFlash(__('You are not allowed to edit another user\'s details, your tentative has been reported to Vusion adminstrator.'));
            $this->redirect(array('action' => 'changePassword', $this->Auth->user('id')));
        }            
        
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user.'));
        }
        $user   = $this->User->read(null, $id);
        $userId = $id;
        $this->set(compact('userId'));
        
        if ($this->request->is('post')) {
            
            if (Security::hash($hash.$this->request->data['oldPassword']) != $user['User']['password']) {
                $this->Session->setFlash(__('Old password is incorrect. Please try again.'));
            } else if ($this->request->data['newPassword'] != $this->request->data['confirmNewPassword']) {
                $this->Session->setFlash(__('New passwords doesn\'t match. Please try again.'));
            } else {
                $user['User']['password'] = $this->request->data['newPassword'];
                if ($this->User->save($user)) {
                    $this->Session->setFlash(__('Password changed successfully.'),
                        'default',
                        array('class'=>'message success')
                        );
                    $this->redirect(array('action' => 'view', $id));
                } else {
                    $this->Session->setFlash(__('Password saving failed.'));
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
        $filePath                 = WWW_ROOT . 'files/report-issues';        
        $validationErrors         = array();
        
        if (!isset($this->request->data['ReportIssue']['subject']) || ($this->request->data['ReportIssue']['subject'] == "")) {
            $validationErrors['subject'] = array(__('Please describe the expect vs current behavior.'));
        } else {
            $subject = $this->request->data['ReportIssue']['subject'];
        } 
        if (!isset($this->request->data['ReportIssue']['message']) || ($this->request->data['ReportIssue']['message'] == "")) {
            $validationErrors['message'] = array(__('Please explain us how to reproduce the issue on our computers.'));
        } else {
            $message = $this->request->data['ReportIssue']['message'];
        }
        $attachment               = $this->request->data['ReportIssue']['screenshot'];
        if ($attachment['error'] != 0) {
            if ($attachment['error'] == 4) { 
                $validationErrors['screenshot'] = array(__("Please take one screenshot and upload it."));
            } else { 
                $validationErrors['screenshot'] = array(__('Error while uploading the file: %s.', $attachment['error']));
            }
        } else {
            $fileExtension = end(explode('.', $attachment['name']));
            if (!($fileExtension == 'jpg') and !($fileExtension == 'png')) {
                $validationErrors['screenshot'] = array(__('The file format ".%s" is not supported. Please upload an image .jpg or .png.', $fileExtension)); 
            }
        }
        if ($validationErrors != array()) {
            $this->Session->setFlash(__("Reporting failed due to incorrect report."));
            $this->set(compact('validationErrors'));
            return;
        }
        
        copy($attachment['tmp_name'], $filePath . DS . $attachment['name']);
        
        if (!$this->CakeEmail) {
            $this->CakeEmail = new CakeEmail();
        }
        $this->CakeEmail->config('default');
        $this->CakeEmail->from($userEmail);
        $this->CakeEmail->to($reportIssueToEmail);
        $this->CakeEmail->subject($reportIssueSubjectPrefix . " " . $subject);
        $this->CakeEmail->template('reportissue_template');
        $this->CakeEmail->emailFormat('html');
        $this->CakeEmail->viewVars(array(
            'subject' => $subject,
            'message' => $message,
            'userName' => $userName));
        $this->CakeEmail->attachments($filePath . DS .$attachment['name']);
        
        try {
            $this->CakeEmail->send();
        } catch (SocketException $e) {
            $this->Session->setFlash(
                __('Email server connection is down. Please send report to vusion-issues@texttochange.com'));
            unlink($filePath . DS . $attachment['name']);
            return;  
        } catch (Exception $e) {
            $exceptionMessage = $e->getMessage();
            $this->Session->setFlash(
                __('"%s". Please send report to vusion-issues@texttochange.com', $exceptionMessage));
            unlink($filePath . DS . $attachment['name']);
            return;
        }
        
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
                __('Please enter correct captcha code and try again.'));
            return;
        }        
        $userName = $account[0]['User']['username'];
        $userId   = $account[0]['User']['id'];
        $this->Session->write('user_id',$userId);

        $token = md5 (date('mdy').rand(4000000, 4999999));
        $this->Ticket->saveTicket($token);
        
        $subject = 'Password Reset';
        $template = 'reset_password_template';
        $this->Ticket->sendEmail($email, $userName, $subject, $template, $token);
        $this->Session->setFlash(
            __('An Email has been sent to your email account.'),
            'default', array('class'=>'message success'));
        $this->redirect('/');
    }
    
    
    public function useTicket($ticketHash)
    {
        $results = $this->Ticket->checkTicket($ticketHash);
        if (isset($results)) {
            if (is_array($results)) {
                $this->Session->setFlash(
                    __('Enter your username and password below'),
                    'default', array('class'=>'message success'));
                $this->Session->write('invite',$results);
                $this->render('add_invitee');
            } else {
                $this->Session->setFlash(
                    __('Enter your new password below'),
                    'default', array('class'=>'message success'));
                $this->render('new_password');
            }
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
                __('New passwords doesn\'t match. Please try again.'));
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
            $this->Session->setFlash(__('Password saving failed.'));
            $this->render('new_password');          
        }
    }


    public function inviteUser()
    {
        $user = $this->Auth->user();
        $andCondition = ($user['group_id'] != 5) ? array(array('id !=' => 1), array('id !=' => 2)) : array(array('id !=' => 1), array('id !=' => 2), array('id !=' => 3));

        $groups   = $this->User->Group->find('list',
            array('conditions' => array('AND' => $andCondition)
            ));
        
        if ($this->Group->hasSpecificProgramAccess($user['group_id'])) {
           $rawPrograms = $this->User->Program->find(
                'authorized',
                array(
                'specific_program_access' => 'true',
                'user_id' => $user['id'],
                ));
           foreach ($rawPrograms as $program) {
             $programs[$program['Program']['id']] = $program['Program']['name'];
           }
        } else {
            $programs = $this->User->Program->find('list');
        }

        $this->set(compact('groups', 'programs'));

        if (!$this->request->is('post')) {
            return;
        }
        # to help in form validation
        $validationErrors = array('User' => array(), 'Program' => array());

        if (!isset($this->request->data['User']['email']) or
            $this->request->data['User']['email'] == "" or
            !preg_match(VusionConst::EMAIL_REGEX, $this->request->data['User']['email'])) {
            $validationErrors['User']['email'] = "Invalid email.";
        } else {
            $email = $this->request->data['User']['email'];
        }

        if ($this->request->data['Program']['Program'] == "") {
            $validationErrors['Program']['Program'] = __("Please select at least one program.");
        } else {
            $programs = $this->request->data['Program'];
        }
        
        $group_id = $this->request->data['User']['group_id'];
        $disclaimer = $this->request->data['User']['invite_disclaimer'];
        
        #to help in form validation
        if ($validationErrors['User'] != array() or $validationErrors['Program'] != array()) {
            $this->Session->setFlash(__("Inite user failed."));
            $this->set(compact('validationErrors'));
            return;
        }

        if (!$disclaimer) {
            $this->Session->setFlash(__('Please tick the disclaimer'));
            return;
        }

        $invite = array(
            'programs'=> $programs,
            'group_id' => $group_id,
            'invited_by' => $user['id']
            );
        
        $token = md5 (date('mdy').rand(4000000, 4999999));
        $this->Ticket->saveTicket($token, $email, $invite);

        $userName = $this->Session->read('Auth.User.username');
        
        $subject = 'Invitation';
        $template = 'invite_user_template';
        $this->Ticket->sendEmail($email, $userName, $subject, $template, $token);
        $this->Session->setFlash(
            __('An Email has been sent to the invited email account.'),
            'default', array('class'=>'message success'));
        $this->redirect('/');
    }

    public function addInvitee()
    {
        $invite = $this->Session->read('invite');

        $usernameExists = $this->User->find('first', 
            array('conditions' => array('username' => $this->request->data['User']['username'])));
        $emailExists    = $this->User->find('first', 
            array('conditions' => array('email' => $this->request->data['User']['email'])));

        if ($usernameExists) {
            $this->Session->setFlash(__('This username already exists. Please choose another'));
            return;
        }

        if ($emailExists) {
            $this->Session->setFlash(__('This email already exists. Please choose another'));
            return;
        }

        if ($this->request->is('post')) {
            $this->User->create();
            $invite = $this->Session->read('invite');
            $this->request->data['User']['group_id'] = $invite['group_id'];
            $this->request->data['User']['invited_by'] = $invite['invited_by'];
            $this->request->data['Program'] = $invite['programs'];
            if ($this->User->save($this->request->data)) {
                $this->Session->delete('invite');
                $this->Session->setFlash(__('Your account has been created.'),
                    'default',
                    array('class'=>'message success')
                    );
                $this->redirect('/');
            } else {
                $this->Session->setFlash(__('Account creation failed.'), 
                    'default',
                    array('class' => "message failure")
                    );
            }
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
            $this->Acl->deny($Group, 'controllers/Users/inviteUser');
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
            $this->Acl->deny($Group, 'controllers/Users/inviteUser');
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
            $this->Acl->deny($Group, 'controllers/Users/inviteUser');
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
            $this->Acl->deny($Group, 'controllers/Users/inviteUser');
            echo "Acl Done: ". $group['Group']['name']."</br>";
        }
        
        echo 'AllDone';
        exit;
    }
    
    
}
