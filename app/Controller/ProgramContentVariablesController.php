<?php
App::uses('BaseProgramSpecificController', 'Controller');
App::uses('ContentVariable', 'Model');
App::uses('ContentVariableTable', 'Model');
App::uses('ProgramSpecificMongoModel', 'Model');


class ProgramContentVariablesController extends BaseProgramSpecificController
{
    var $uses = array(
        'ContentVariable', 
        'ContentVariableTable',
        'ProgramSetting');
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')),
        'ProgramAuth',
        'ArchivedProgram');
    var $helpers = array(
        'Js' => array('Jquery'), 
        'Time');

    
    function constructClasses()
    {
        parent::constructClasses();
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
            throw new NotFoundException(__('The keys/value cannot be found in the Content Variables.'));
        }
        
        if ($this->request->is('post')) {
            if ($newContentVariable = $this->ContentVariable->save($this->request->data)) {
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
                __('The keys/value has been deleted from the Content Variables.'),
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
        $this->Session->setFlash(__('The keys/calue was not deleted from the Content Variables.'), 
            'default',
            array('class' => "message failure")
            );
    }
    
    
    /** All function specific to ContentVariableTable **/
    /** by differenciating between table and keys/value, the ACL could be use to manage permissions **/
    public function indexTable()
    {
        $this->paginate = array('all', 'order' => array('modified' => 'desc'));
        $contentVariableTables = $this->paginate('ContentVariableTable');
        $this->set(compact('contentVariableTables', $contentVariableTables));
    }
    
    
    public function addTable()
    {   
        $programUrl = $this->params['program'];
        if ($this->request->is('post') && $this->_isAjax()) {
            $requestSuccess = false;
            $this->ContentVariableTable->create();
            if ($this->ContentVariableTable->save($this->request->data)) {
                $requestSuccess = true;
            }
            $this->set(compact('requestSuccess'));
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
                __('Table deleted from the Content Variables.'),
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
        $this->Session->setFlash(__('A Table was not deleted from Content Variable.'));
    }
    
    
    public function editTable()
    {
        $programUrl = $this->params['program'];
        $id         = $this->params['id'];
        
        $this->ContentVariableTable->id = $id;
        if (!$this->ContentVariableTable->exists()) {
            throw new NotFoundException(__('The table cannot be found in Content Variable.'));
        }
        $contentVariableTable = $this->ContentVariableTable->read();
        
        if ($this->request->is('post') && $this->_isAjax()) {
            $requestSuccess = false;
            if ($this->ContentVariableTable->save($this->request->data)) {
                $requestSuccess = true;
            } 
            $this->set(compact('requestSuccess'));
        } else {
            $this->request->data = $this->ContentVariableTable->read(null, $id);
        }
    }
    
    
    public function editTableValue()
    {
        $programUrl = $this->params['program'];
        
        if (!isset($this->request->data['ContentVariable']['keys'])) {
            throw new Exception('Missing keys');
        }
        
        $contentVariables = $this->ContentVariable->find('fromKeys',  array('conditions' => array('keys' => $this->request->data['ContentVariable']['keys'])));
        if (count($contentVariables) == 0) {
            throw new NotFoundException(__('This table value cannot be found in Content Variable.'));
        }
        
        if ($this->request->is("post")) {
            $requestSuccess = false;
            $contentVariable = $contentVariables[0];
            $this->ContentVariable->id = $contentVariable['ContentVariable']['_id'];
            $contentVariable['ContentVariable']['value'] = $this->request->data['ContentVariable']['value'];
            if ($this->ContentVariable->save($contentVariable)) {
                $this->ContentVariableTable->updateTableFromKeysValue($contentVariable);
                $requestSuccess = true;
            }
            $this->set(compact('requestSuccess'));
        }
    }
    
    
    public function export()
    { 
        $requestSuccess = false;
        $programUrl     = $this->params['program'];
        $id             = $this->params['id'];
        
        try {
            
            $filePath = WWW_ROOT . "files/programs/" . $programUrl; 
            
            if (!file_exists($filePath)) {
                mkdir($filePath);
                chmod($filePath, 0764);
            }
            
            $programNow  = $this->ProgramSetting->getProgramTimeNow();
            if ($programNow) {
                $timestamp = $programNow->format("Y-m-d_H-i-s");
            } else {
                $timestamp = '';
            }
            
            $contentVariableTable = $this->ContentVariableTable->find('first', array('conditions' => array('_id' => $id)));
           
            $tableName   = inflector::slug($contentVariableTable['ContentVariableTable']['name'], '_');
            $programName = $this->Session->read($programUrl.'_name');
            
            $programNameUnderscore = inflector::slug($programName, '_');
            
            $fileName     = $programNameUnderscore . "_" .$tableName. "_table_".$timestamp.".csv";            
            $fileFullPath = $filePath . "/" . $fileName;
            $handle       = fopen($fileFullPath, "w");
            
            $contentVariableTableColumns = $contentVariableTable['ContentVariableTable']['columns'];
            
            foreach ($contentVariableTableColumns as $contentVariableTableColumn) {
                $headers[] = $contentVariableTableColumn['header'];
                $columnValues[] = $contentVariableTableColumn['values'];
            }
            fputcsv($handle, $headers,',','"');
            
            # model deosnt allow for null values, so each data set has equal number of elements
            $countCols = count($columnValues[0]);
            
            for ($x =0; $x < $countCols; $x++) {
                $line= array();
                $columnIndex = 0;
                foreach ($columnValues as $columnValue) {
                    $line[] = $columnValues[$columnIndex][$x];
                    $columnIndex++;
                }
                
                fputcsv($handle, $line,',' , '"');
            }
            
            $requestSuccess = true;
            $this->set(compact('requestSuccess', 'fileName'));    
        } catch (Exception $e) {
            $this->Session->setFlash($e->getMessage());
            $this->set(compact('requestSuccess'));
        }  
        
        
    }
    
    
    public function download()
    {
        $programUrl   = $this->params['program'];
        $fileName     = $this->params['url']['file'];        
        $fileFullPath = WWW_ROOT . "files/programs/" . $programUrl . "/" . $fileName; 
        
        if (!file_exists($fileFullPath)) {
            throw new NotFoundException(__("This file does not exist: %s", $fileFullPath));
        }
        
        $this->response->header("X-Sendfile: $fileFullPath");
        $this->response->header("Content-type: application/octet-stream");
        $this->response->header('Content-Disposition: attachment; filename="' . basename($fileFullPath) . '"');
        $this->response->send();
    }
    
    
}
