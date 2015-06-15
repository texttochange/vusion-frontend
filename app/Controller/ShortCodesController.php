<?php
App::uses('AppController', 'Controller');
App::uses('ShortCode', 'Model');
App::uses('Template', 'Model');


class ShortCodesController extends AppController
{
    var $uses = array(
        'ShortCode',
        'Template');
    var $components = array(
        'PhoneNumber'); 
    var $helpers = array(
        'Js' => array('Jquery'));
    
    
    public function constructClasses()
    {
        parent::constructClasses();
    }
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
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
        if ($this->request->is('post')) {
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
        $countries                  = $this->PhoneNumber->getCountries();
        $prefixesByCountriesOptions = $this->PhoneNumber->getPrefixesByCountries();
        $errorTemplateOptions       = $this->Template->getTemplateOptions('unmatching-keyword');
        
        $maxCharacterPerSmsOptions = array_combine(
            $this->ShortCode->maxCharacterPerSmsOptions, 
            $this->ShortCode->maxCharacterPerSmsOptions);
        $this->set(compact('errorTemplateOptions', 'prefixesByCountriesOptions', 'maxCharacterPerSmsOptions', 'countries'));
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
         $this->Session->setFlash(__('ShortCode deleted.'),
                'default',
                array('class'=>'message success')
                );
        $this->redirect(array('controller' => 'shortCodes',
            'action' => 'index'
            ));
        
    }
    
    
    public function archive()
    {
        $id = $this->params['id'];
        
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        
        $this->ShortCode->id = $id;
        if (!$this->ShortCode->exists()) {
            throw new NotFoundException(__('Invalid shortcode.') . $id);
        }
        
        if ($this->ShortCode->archive($id)) {
            $this->Session->setFlash(__('This ShortCode has been archived. All programs using it have been archived or deleted'),
                'default',
                array('class'=>'message success')
                );
            $this->redirect(array('controller' => 'shortCodes',
                'action' => 'index'
                ));
        } else {    
            $this->Session->setFlash(__('ShortCode couldn\'t be archived. Please filter with Shortcode to archive any Running program(s)'));
            $this->redirect(array('controller' => 'shortCodes',
                'action' => 'index'
                ));
        }
    }
    
    
}
