<?php
App::uses('AppController', 'Controller');
App::uses('ContentVariable', 'Model');
App::uses('ContentVariableTable', 'Model');


class ProgramContentVariablesController extends AppController
{
    public $uses = array('ContentVariable', 'ContentVariableTable');

    
    function constructClasses()
    {
        parent::constructClasses();
        
        $options = array(
            'database' => ($this->Session->read($this->params['program'].'_db'))
            );
        
        $this->ContentVariable = new ContentVariable($options);
        $this->ContentVariableTable = new ContentVariableTable($options);
    }
    
    
    public function index()
    {
        //Only load the one that don't have a link to a table
        $this->paginate = array(
            'all',
            'conditions' => array('$or' => array(
                array('table' => null),
                array('table' => array('$exists' => false))
                ))
            );
        $contentVariables = $this->paginate('ContentVariable');
        $this->set(compact('contentVariables', $contentVariables));
    }
    
    
    public function add()
    {
        $programUrl = $this->params['program'];
        
        if ($this->request->is('post')) {
            $this->ContentVariable->create();
            if ($this->ContentVariable->save($this->request->data)) {
                $this->Session->setFlash(__('The keys/values has been saved in Content Variables.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array(
                    'program' => $programUrl, 
                    'controller' => 'programContentVariables',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The keys/value could not be saved in Content Variables.'), 
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
            throw new NotFoundException(__('The keys/value cannot found in the Content Variables.'));
        }
        $oldContentVariable = $this->ContentVariable->read();

        if ($this->request->is('post')) {
            $allowed =  $this->ContentVariable->allowToEdit($oldContentVariable, $this->request->data);
            if (is_string($allowed)) {
                $this->Session->setFlash($allowed, 'default', array('class' => "message failure"));
            } else if ($newContentVariable = $this->ContentVariable->save($this->request->data)) {
                $this->ContentVariableTable->updateTablefromKeysValue($newContentVariable);
                $this->Session->setFlash(__('The keys/value has been saved in Content Variable.'),
                    'default',
                    array('class'=>'message success'));
                $this->redirect(array(
                    'program' => $programUrl, 
                    'controller' => 'programContentVariables',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The keys/value could not be saved in Content Variable. Please, try again.'), 
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
            throw new NotFoundException(__('The keys/value cannot be found in Content Variables.'));
        }
        
        if ($this->ContentVariable->delete()) {
            $this->Session->setFlash(
                __('The keys/value has been deleted from Content Variable.'),
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
        $this->Session->setFlash(__('The keys/calue was not deleted from Content Variable.'), 
                'default',
                array('class' => "message failure")
                );
    }


    /** All function specific to ContentVariableTable **/
    /** by differenciating between table and keys/value, the ACL could be use to manage permissions **/
    public function indexTable()
    {
        $this->paginate = array('all');
        $contentVariableTables = $this->paginate('ContentVariableTable');
        $this->set(compact('contentVariableTables', $contentVariableTables));
    }


    public function addTable()
    {   
        $programUrl = $this->params['program'];
        
        if ($this->request->is('post')) {
            $this->ContentVariableTable->create();
            if ($this->ContentVariableTable->save($this->request->data)) {
                $this->Session->setFlash(__('The table has been saved in Content Variable.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array(
                    'program' => $programUrl, 
                    'controller' => 'programContentVariables',
                    'action' => 'indexTable'
                    ));
            } else {
                $this->Session->setFlash(__('The table could not be saved in Content Variable.'), 
                'default',
                array('class' => "message failure")
                );
            }
        }
    }


    public function deleteTable()
    {
        $id         = $this->params['id'];
        $programUrl = $this->params['program'];
        
        $this->ContentVariableTable->id = $id;
        
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        
        if (!$this->ContentVariableTable->exists()) {
            throw new NotFoundException(__('The table cannot be found in Content Variable.'));
        }
        
        if ($this->ContentVariableTable->deleteTableAndValues($id)) {
            $this->Session->setFlash(
                __('Dynamic Content Table deleted'),
                'default',
                array('class'=>'message success')
                );
            $this->redirect(
                array(
                    'program' => $programUrl,
                    'controller' => 'programContentVariables',
                    'action' => 'indexTable'
                    )
                );
        }
        $this->Session->setFlash(__('A Table was not deleted from Content Variable.'), 
                'default',
                array('class' => "message failure")
                );
    }


    public function editTable()
    {
        $programUrl = $this->params['program'];
        $id         = $this->params['id'];

        $this->ContentVariable->id = $id;
        if (!$this->ContentVariable->exists()) {
            throw new NotFoundException(__('The table cannot be found in Content Variable.'));
        }
        $contentVariable = $this->ContentVariable->read();

        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->ContentVariable->save($this->request->data)) {
                $this->Session->setFlash(__('The Table has been saved as Content Variable.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array(
                    'program' => $programUrl, 
                    'controller' => 'programContentVariables',
                    'action' => 'indexTable'
                    ));
            } else {
                $this->Session->setFlash(__('The Table could not be saved in Content Variable. Please, try again.'), 
                'default',
                array('class' => "message failure")
                );
            }
        } else {
            $this->request->data = $this->ContentVariable->read(null, $id);
        }
    }


}
