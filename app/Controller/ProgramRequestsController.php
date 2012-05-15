<?php
App::uses('AppController', 'Controller');
App::uses('Request', 'Model');

class ProgramRequestsController extends AppController
{
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('*');
    }


    function constructClasses()
    {
        parent::constructClasses();
        $options       = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->Request = new Request($options);
    }


    public function index()
    {
        $this->set('requests', $this->Request->find('all'));
    }


    public function add()
    {
        $programUrl = $this->params['program'];

        if ($this->request->is('post')) {
            $this->Request->create();
            if ($this->Request->save($this->request->data)) {
                $this->Session->setFlash(
                    __('The request has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array(
                    'program' => $programUrl,
                    'action' => 'index')
                    );
            }
        }
    }


    public function edit()
    {
        $programUrl = $this->params['program'];
        $id         = $this->params['id'];
        
        $this->Request->id = $id;
        if (!$this->Request->exists()) {
            throw new NotFoundException(__('Invalide Request') . $id);
        }

        if ($this->request->is('post')) {
            if ($this->Request->save($this->request->data)) {
                $request = $this->request->data;
                $this->Session->setFlash(
                    __('The request has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(
                    array(
                        'program' => $programUrl,
                        'action' => 'index'
                        )
                    ); 
            } else {
                $this->Session->setFlash(__('The request could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $this->Request->read(null, $id);
        }
    }


    public function delete()
    {
        $programUrl = $this->params['program']; 
        $id         = $this->params['id']; 
        
         $this->Request->id = $id;
        if (!$this->Request->exists()) {
            throw new NotFoundException(__('Invalid request') . $id);
        }
        if ($this->Request->delete()) {
            $this->Session->setFlash(
                __('The request has been deleted.'),
                'default',
                array('class'=>'message success')
            );
        } else {
            $this->Session->setFlash(__('The request has not been deleted.'));
        }
        $this->redirect(
            array(
                'program' => $programUrl,
                'action' => 'index'
                )
            );
    }


}
