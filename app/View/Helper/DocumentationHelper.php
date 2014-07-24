<?php
App::uses('AppHelper', 'View/Helper');

class DocumentationHelper extends AppHelper 
{
    
    var $helpers = array('Html');
    
    var $convertLanguageNotation = array(
        'eng' => 'en',
        'spa' => 'es',
        'fre' => 'fr');
    
    public function link()
    {
        $userLanguage = Configure::read('Config.language');
        $lang         = 'en';
        if (isset($this->convertLanguageNotation[$userLanguage])) {
            $lang = $this->convertLanguageNotation[$userLanguage];
        }
        
        echo $this->Html->link(
            __('Help'),
            'http://vusion-doc.texttochange.org/' . $lang,
            array('class' => 'ttc-link-header'));
    }
    
    
}