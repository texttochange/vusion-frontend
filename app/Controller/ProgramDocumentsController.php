<?php

App::uses('AppController', 'Controller');


/**
 * ProgramDocuments Controller
 *
 * @property ProgramDocument $ProgramDocument
 */
class ProgramDocumentsController extends AppController {

    
    /**
    * index method
    *
    * @return void
    */
    public function index()
    {
        $this->ProgramDocument->recursive = 0;
        $this->set('ProgramDocuments', $this->paginate());
    }


    /**
    * view method
    *
    * @param string $id
    * @return void
    */
    public function view($id = null)
    {
        $this->ProgramDocument->id = $id;
        if (!$this->ProgramDocument->exists()) {
            throw new NotFoundException(__('Invalid ProgramDocument'));
        }
        $this->set('ProgramDocument', $this->ProgramDocument->read(null, $id));
    }


    /**
    * add method
    *
    * @return void
    */
    public function add()
    {
        if ($this->request->is('post')) {
            $this->ProgramDocument->create();
            if ($this->ProgramDocument->save($this->request->data)) {
                $this->Session->setFlash(__('The ProgramDocument has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The ProgramDocument could not be saved. Please, try again.'));
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
        $this->ProgramDocument->id = $id;
        if (!$this->ProgramDocument->exists()) {
            throw new NotFoundException(__('Invalid ProgramDocument'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->ProgramDocument->save($this->request->data)) {
                $this->Session->setFlash(__('The ProgramDocument has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The ProgramDocument could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $this->ProgramDocument->read(null, $id);
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
        $this->ProgramDocument->id = $id;
        if (!$this->ProgramDocument->exists()) {
            throw new NotFoundException(__('Invalid ProgramDocument'));
        }
        if ($this->ProgramDocument->delete()) {
            $this->Session->setFlash(__('ProgramDocument deleted'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('ProgramDocument was not deleted'));
        $this->redirect(array('action' => 'index'));
    }


}
