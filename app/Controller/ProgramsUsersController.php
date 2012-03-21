<?php

App::uses('AppController', 'Controller');

/**
 * ProgramsUsers Controller
 *
 * @property ProgramsUser $ProgramsUser
 */
class ProgramsUsersController extends AppController
{


    /**
    * index method
    *
    * @return void
    */
    public function index()
    {
        $this->ProgramsUser->recursive = 0;
        $this->set('programsUsers', $this->paginate());
    }

    /**
    * view method
    *
    * @param string $id
    * @return void
    */
    public function view($id = null)
    {
        $this->ProgramsUser->id = $id;
        if (!$this->ProgramsUser->exists()) {
            throw new NotFoundException(__('Invalid programs user'));
        }
        $this->set('programsUser', $this->ProgramsUser->read(null, $id));
    }


    /**
    * add method
    *
    * @return void
    */
    public function add()
    {
        if ($this->request->is('post')) {
            $this->ProgramsUser->create();
            if ($this->ProgramsUser->save($this->request->data)) {
                $this->Session->setFlash(__('The programs user has been saved'),
                        'default',
                        array('class'=>'good-message')
                        );
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The programs user could not be saved. Please, try again.'));
            }
        }
        $this->set('programs', $this->ProgramsUser->Program->find('list'));
        $this->set('users', $this->ProgramsUser->User->find('list'));
    }


    /**
    * edit method
    *
    * @param string $id
    * @return void
    */
    public function edit($id = null)
    {
        $this->ProgramsUser->id = $id;
        if (!$this->ProgramsUser->exists()) {
            throw new NotFoundException(__('Invalid programs user'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->ProgramsUser->save($this->request->data)) {
                $this->Session->setFlash(__('The programs user has been saved'),
                        'default',
                        array('class'=>'good-message')
                        );
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The programs user could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $this->ProgramsUser->read(null, $id);
        }
        $this->set('programs', $this->ProgramsUser->Program->find('list'));
        $this->set('users', $this->ProgramsUser->User->find('list'));
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
        $this->ProgramsUser->id = $id;
        if (!$this->ProgramsUser->exists()) {
            throw new NotFoundException(__('Invalid programs user'));
        }
        if ($this->ProgramsUser->delete()) {
            $this->Session->setFlash(__('Programs user deleted'),
                        'default',
                        array('class'=>'good-message')
                        );
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('Programs user was not deleted'));
        $this->redirect(array('action' => 'index'));
    }


}
