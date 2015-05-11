<?php
App::uses('AppHelper', 'View/Helper');

class DocumentationHelper extends AppHelper 
{
    
    var $helpers = array('Html');
    
    
    public function link()
    {
        $documentationUrl = 'http://vusion-doc.texttochange.org';
        echo $this->Html->link(
            __('Help'),
            array(),
            array(
                'class' => 'ttc-link-header', 
                'url' => $documentationUrl, 
                'onclick'=> 'popupNewBrowserTab(this)'));
    }
    
    
}