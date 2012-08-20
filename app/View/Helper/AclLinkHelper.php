<?php

App::uses('AppHelper', 'View/Helper');

class AclLinkHelper extends AppHelper{
    
    public $helpers = array('Html');
    
    public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
        App::import('Component', 'Acl'); 
        App::import('Component', 'Session'); 
        $this->Acl=new AclComponent(new ComponentCollection()); 
        $this->Session=new SessionComponent(new ComponentCollection()); 
    }
    
    function generateLink( $title, $url, $controller, $action = 'index', $id = null, $ext = null){
        $aclUrl = 'controllers/'.ucfirst($controller).($action ? '/'.$action : '');
        if ($this->Acl->check(
            array('user'=>array('id'=>$this->Session->read('Auth.User.id'))),
            $aclUrl)) {
            return $this->Html->link(__($title),
                array(
                    'program'=>$url,
                    'controller'=>$controller,
                    'action'=>$action.($ext ? $ext : ''),
                    'id'=>$id
                    )
                );
        } else {
            return $this->Html->tag('span',__($title), array('class'=>'ttc-disabled-link'));
        }
    }
   
    
}
