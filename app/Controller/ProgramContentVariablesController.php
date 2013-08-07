<?php

App::uses('AppController', 'Controller');
App::uses('ContentVariable', 'Model');


class ProgramContentVariablesController extends AppController
{
    public $uses = array('ContentVariable');
    
    function constructClasses()
    {
        parent::constructClasses();
        
        $options = array(
            'database' => ($this->Session->read($this->params['program'].'_db'))
            );
        
        $this->ContentVariable = new ContentVariable($options);
        
    }
    
    
    public function index()
    {
        $contentVariables = $this->paginate();
        $this->set(compact('contentVariables', $contentVariables));
    }
    
    
    public function add()
    {
        $programUrl = $this->params['program'];
        
        if ($this->request->is('post')) {
            $this->ContentVariable->create();
            if ($this->ContentVariable->save($this->request->data)) {
                $this->Session->setFlash(__('The dynamic content has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array(
                    'program' => $programUrl, 
                    'controller' => 'programContentVariables',
                    'action' => 'index'
                    ));
            } else {
                print_r($this->validationErrors);
                $this->Session->setFlash(__('The dynamic content could not be saved.'), 
                'default',
                array('class' => "message failure")
                );
            }
        }
    }
    
    
    public function edit()
    {
        $programUrl = $this->params['program'];
        $id         = $this->params['id'];
        
        $this->ContentVariable->id = $id;
        if (!$this->ContentVariable->exists()) {
            throw new NotFoundException(__('Invalid dynamic content'));
        }
        $contentVariable = $this->ContentVariable->read();

        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->ContentVariable->save($this->request->data)) {
                $this->Session->setFlash(__('The dynamic content has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array(
                    'program' => $programUrl, 
                    'controller' => 'programContentVariables',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The dynamic content could not be saved. Please, try again.'), 
                'default',
                array('class' => "message failure")
                );
            }
        } else {
            $this->request->data = $this->ContentVariable->read(null, $id);
        }
    }
    
    
    public function delete()
    {
        $id         = $this->params['id'];
        $programUrl = $this->params['program'];
        
        $this->ContentVariable->id = $id;
        
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        
        if (!$this->ContentVariable->exists()) {
            throw new NotFoundException(__('Invalid Dynamic Content.'));
        }
        
        if ($this->ContentVariable->delete()) {
            $this->Session->setFlash(
                __('Dynamic content deleted'),
                'default',
                array('class'=>'message success')
                );
            $this->redirect(
                array(
                    'program' => $programUrl,
                    'controller' => 'programContentVariables',
                    'action' => 'index'
                    )
                );
        }
        $this->Session->setFlash(__('Dynamic content was not deleted.'), 
                'default',
                array('class' => "message failure")
                );
    }
}
