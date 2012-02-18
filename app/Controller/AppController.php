<?php
App::uses('Controller', 'Controller');

class AppController extends Controller
{

    var $uses = array('Program', 'Group');

    public $components = array(
        'Session',
        'Auth' => array(
            'loginAction' => array(
                'controller' => 'users',
                'action' => 'login',
                
                ),
            'loginRedirect' => array(
                'controller' => 'programs',
                'action' => 'index'
                ),
            'logoutRedirect' => array(
                'controller' => 'users',
                'action' => 'login'
                ),
            //'authError' => 'Authentication Failed',
            'authenticate' => array(
                'Form' => array(
                    'field' => array('username' => 'username')
                    )
                ),
            'authorize' => array(
                'Actions' => array('actionPath' => 'controllers')
                )
            ),
        'Acl',
        'Cookie');

    public $helpers = array('Html', 'Form', 'Session');

    function beforeFilter()
    {    
        //set language into Session and Cookies
        $this->_setLanguage();
    }

    function constructClasses()
    {
        parent::constructClasses();

        //Verify the access of user to this program
        if (!empty($this->params['program'])) {
            $this->Program->recursive = -1;
            $data = $this->Program->find('authorized', array(
                'specific_program_access' => $this->Group->hasSpecificProgramAccess($this->Session->read('Auth.User.group_id')),
                'user_id' => $this->Session->read('Auth.User.id'),
                'program_url' => $this->params['program']
                ));
            if (count($data)==0) {
                //$this->Session->setFlash(__('This program does not exists'));
                //$this->redirect('/');
                throw new NotFoundException('Could not find this page.');
            } else {
                $this->Session->write( $this->params['program'] . '_name', $data[0]['Program']['name']);
                $this->Session->write( $this->params['program'] . '_db', $data[0]['Program']['database']);
                $this->set('programTimezone',$data[0]['Program']['timezone']);
            }
        }
    }

    function _setLanguage()
    {
        if ($this->Cookie->read('lang') && !$this->Session->check('Config.language')) {
            $this->Session->write('Config.language',$this->Cookie->read('lang'));
        } else if (isset($this->params['language']) && 
            ($this->params['language'] != $this->Session->read('Config.language'))){
            $this->Session->write('Config.language', $this->params['language']);
            $this->Cookie->write('lang', $this->params['language'], false, '20 days');
        }
    }


}
