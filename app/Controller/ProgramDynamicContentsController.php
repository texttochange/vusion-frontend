<?php

App::uses('AppController', 'Controller');
App::uses('DynamicContent', 'Model');


class ProgramDynamicContentsController extends AppController
{
    public $uses = array('DynamicContent');
    
    function constructClasses()
    {
        parent::constructClasses();
        
        $options = array(
            'database' => ($this->Session->read($this->params['program'].'_db'))
            );
        
        $this->DynamicContent = new DynamicContent($options);
        
    }
    
    
    public function index()
    {
        $dynamicContents = $this->paginate();
        $this->set(compact('dynamicContents', $dynamicContents));
    }
    
    
    public function add()
    {
        $programUrl = $this->params['program'];
        
        if ($this->request->is('post')) {
            $this->DynamicContent->create();
            if ($this->DynamicContent->save($this->request->data)) {
                $this->Session->setFlash(__('The dynamic content has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array(
                    'program' => $programUrl, 
                    'controller' => 'programDynamicContents',
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
        
        $this->DynamicContent->id = $id;
        if (!$this->DynamicContent->exists()) {
            throw new NotFoundException(__('Invalid dynamic content'));
        }
        $dynamicContent = $this->DynamicContent->read();

        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->DynamicContent->save($this->request->data)) {
                $this->Session->setFlash(__('The dynamic content has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array(
                    'program' => $programUrl, 
                    'controller' => 'programDynamicContents',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The dynamic content could not be saved. Please, try again.'), 
                'default',
                array('class' => "message failure")
                );
            }
        } else {
            $this->request->data = $this->DynamicContent->read(null, $id);
        }
    }
    
    
    public function delete()
    {
        $id         = $this->params['id'];
        $programUrl = $this->params['program'];
        
        $this->DynamicContent->id = $id;
        
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        
        if (!$this->DynamicContent->exists()) {
            throw new NotFoundException(__('Invalid Dynamic Content.'));
        }
        
        if ($this->DynamicContent->delete()) {
            $this->Session->setFlash(
                __('Dynamic content deleted'),
                'default',
                array('class'=>'message success')
                );
            $this->redirect(
                array(
                    'program' => $programUrl,
                    'controller' => 'programDynamicContents',
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
