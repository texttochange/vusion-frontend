<?php
App::uses('AppController', 'Controller');
App::uses('ShortCode', 'Model');
App::uses('Template', 'Model');


class ShortCodesController extends AppController
{

    var $helpers    = array('Js' => array('Jquery'));
    var $components = array('PhoneNumber'); 
    
    public function constructClasses()
    {
        parent::constructClasses();
    }


    public function beforeFilter()
    {
        parent::beforeFilter();
        if (!Configure::read("mongo_db")) {
            $options = array(
                'database' => 'vusion'
                );
        } else {
            $options = array(
                'database' => Configure::read("mongo_db")
                );
        }
        $this->ShortCode = new ShortCode($options);
        $this->Template  = new Template($options);
    }

    
    public function index()
    {
        $shortcodes = $this->paginate();
        $this->set(compact('shortcodes'));
    }
    
    
    public function add()
    {
        if ($this->request->is('post')) {
            $this->ShortCode->create();
            if ($this->ShortCode->save($this->request->data)) {
                $this->Session->setFlash(__('The shortcode has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array(
                    'controller' => 'shortCodes',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The shortcode could not be saved.'), 
                'default',
                array('class' => "message failure")
                );
            }
        }
        $this->setOptions();
    }
    
    
    public function edit()
    {
        $shortcode = $this->params['shortCode'];
        $id        = $this->params['id'];
        
        $this->ShortCode->id = $id;
        if (!$this->ShortCode->exists()) {
            throw new NotFoundException(__('Invalid shortcode.') . $id);
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->ShortCode->save($this->request->data)) {
                $shortcode = $this->request->data;
                $this->Session->setFlash(__('The shortcode has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array('controller' => 'shortCodes',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The shortcode could not be saved. Please, try again.'), 
                'default',
                array('class' => "message failure")
                );
            }
        } else {
            $this->request->data = $this->ShortCode->read(null, $id);
        }
        $this->setOptions();
    }
    
    protected function setOptions()
    {
        $countryOptions = $this->PhoneNumber->getCountries();
        $errorTemplateOptions   = $this->Template->getTemplateOptions('unmatching-keyword');
        $maxCharacterPerSmsOptions = array_combine(
            $this->ShortCode->maxCharacterPerSmsOptions, 
            $this->ShortCode->maxCharacterPerSmsOptions);
        $this->set(compact('errorTemplateOptions', 'countryOptions', 'maxCharacterPerSmsOptions'));
    }
    
    public function delete()
    {
        $id = $this->params['id'];
        
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $this->ShortCode->id = $id;
        if (!$this->ShortCode->exists()) {
            throw new NotFoundException(__('Invalid shortcode.') . $id);
        }
        if ($this->ShortCode->delete()) {
            $this->Session->setFlash(__('ShortCode deleted.'),
                'default',
                array('class'=>'message success')
            );
            $this->redirect(array('controller' => 'shortCodes',
                'action' => 'index'
                ));
        }
        $this->Session->setFlash(__('ShortCode was not deleted.'), 
                'default',
                array('class' => "message failure")
                );
        $this->redirect(array('controller' => 'shortCodes',
                'action' => 'index'
                ));
        
    }
    
    
}
