<?php
App::uses('AppHelper', 'View/Helper');
App::import('Component', 'Acl'); 
App::import('Component', 'Session'); 


class AclLinkHelper extends AppHelper
{
    
    public $helpers = array('Html', 'Form');
    
    public function __construct(View $View, $settings = array())
    {
        parent::__construct($View, $settings);
        $this->Acl = new AclComponent(new ComponentCollection());
        $this->Session = new SessionComponent(new ComponentCollection());
    }
    

    function _allow($aclUrl)
    {
        if ($this->Session->read('Auth.User.id') == null) {
            return false;
        }
        return $this->Acl->check(
            array('user' => array('id'=>$this->Session->read('Auth.User.id'))),
            $aclUrl);
    }


    function generateLink( $title, $url, $controller, $action = 'index', $id = null, $ext = null, $named = array())
    {
        $aclUrl = 'controllers/'.ucfirst($controller).($action ? '/'.$action : '');
        if ($this->_allow($aclUrl)) {
            $url = array(
                'program'=>$url,
                'controller'=>$controller,
                'action'=>$action.($ext ? $ext : ''),
                'id'=>$id);
            if ($named != array()) {
                $url = array_merge($named, $url);
            } 
            return $this->Html->link($title, $url);
        } else {
            return $this->Html->tag('span', $title, array('class'=>'ttc-disabled-link'));
        }
    }


    function generateButton($label, $url, $controller, $action, $options=null, $id=null, $ext=null)
    {
        $aclUrl = 'controllers/'.ucfirst($controller).($action ? '/'.$action : '');
        if ($this->_allow($aclUrl)) {
                $url = array(
                        'program'=>$url,
                        'controller'=>$controller,
                        'action'=>$action, 
                        '?' => $ext);
                if (isset($id)) {
                    $url['id'] = $id;
                };
                return $this->Html->link(
                    $label,
                    $url,
                    $options
                    );
            } 
            return;
    }
 

    function generatePostLink($label, $url, $controller, $action, $confirmation, $options=null, $id=null, $params=null, $ext = null)
    {
        $aclUrl = 'controllers/'.ucfirst($controller).($action ? '/'.$action : '');
        if ($this->_allow($aclUrl)) {
                $url = array(
                        'program'=>$url,
                        'controller'=>$controller,
                        'action'=>$action.($ext ? $ext : ''),
                        );
                if (isset($id)) {
                    $url['id'] = $id;
                };
                if (isset($params)) {
                    $url['?'] = $params;
                }
                return $this->Form->postLink(
                    $label,
                    $url,
                    $options,
                    $confirmation
                    );
            } 
            return;
    }


    
}
