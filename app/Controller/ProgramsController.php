<?php

App::uses('AppController', 'Controller');
App::uses('ProgramSetting', 'Model');

/**
 * Programs Controller
 *
 * @property Program $Program
 */
class ProgramsController extends AppController
{

    var $components = array('RequestHandler');
    public $helpers = array('Js' => array('Jquery'));    
    var $uses = array('Program', 'Group');
    var $paginate = array(
        'limit' => 10,
        'order' => array(
            'Program.created' => 'desc'
            )
        );


    /**
    * index method
    *
    * @return void
    */
    public function index() 
    {
        $this->Program->recursive = -1;
        if ($this->Group->hasSpecificProgramAccess($this->Session->read('Auth.User.group_id'))) {
            $this->paginate = array(
                'authorized',
                'specific_program_access' => 'true',
                'user_id' => $this->Session->read('Auth.User.id'),
                );
        }
        $programs      =  $this->paginate();
        $isProgramEdit = $this->Acl->check(array(
                'User' => array(
                    'id' => $this->Session->read('Auth.User.id')
                ),
            ), 'controllers/Programs/edit');
        foreach($programs as &$program) {
            //print_r($program);
            $tempProgramSetting = new ProgramSetting(array('database' => $program['Program']['database']));
            $country = $tempProgramSetting->find('programSetting', array('key'=>'country'));
            if (isset($country[0]['ProgramSetting']['value'])) {
                $program['Program']['country'] = $country[0]['ProgramSetting']['value'];
            } else {
                $program['Program']['country'] = "Not defined";
            }
        }
        $this->set(compact('programs', 'isProgramEdit'));
    }


    /**
    * view method
    *
    * @param string $id
    * @return void
    */
    public function view($id = null)
    {
        $this->Program->id = $id;
        if (!$this->Program->exists()) {
            throw new NotFoundException(__('Invalid program'));
        }
        $this->set('program', $this->Program->read(null, $id));
    }


    /**
    * add method
    *
    * @return void
    */
    public function add()
    {
        if ($this->request->is('post')) {
            $this->Program->create();
            if ($this->Program->save($this->request->data)) {
                $this->Session->setFlash(__('The program has been saved'),
                    'default',
                    array('class'=>'good-message')
                );
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The program could not be saved. Please, try again.'));
            }
        }
    }


    /**
    * edit method
    *
    * @param string $id
    * @return void
    */
    public function edit($id = null)
    {
        $this->Program->id = $id;
        if (!$this->Program->exists()) {
            throw new NotFoundException(__('Invalid program'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Program->save($this->request->data)) {
                $this->Session->setFlash(__('The program has been saved'),
                    'default',
                    array('class'=>'good-message')
                );
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The program could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $this->Program->read(null, $id);
        }
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
        $this->Program->id = $id;
        if (!$this->Program->exists()) {
            throw new NotFoundException(__('Invalid program'));
        }
        if ($this->Program->delete()) {
            $this->Session->setFlash(__('Program deleted'),
                'default',
                array('class'=>'good-message')
            );
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('Program was not deleted'));
        $this->redirect(array('action' => 'index'));
    }


}
