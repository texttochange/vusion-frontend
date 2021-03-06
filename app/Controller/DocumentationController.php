<?php

App::uses('AppController', 'Controller');

class DocumentationController extends AppController
{
    
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')));
    var $helpers    = array(
        'Js' => array('Jquery')
        );
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('*');
    }
    
    
    public function view()
    {
        $requestSuccess = false;

        if (isset($this->params['url']['topic'])) {
            $lang = Configure::read('Config.language');
            if ($lang and !is_numeric($lang)) {
                $lang = $lang . "/";
            } else {
                $lang = 'eng/';
            }
            $topic = $this->params['url']['topic'];
            $file = WWW_ROOT . "files/documentation/".$lang.$topic.".txt";
            if (!file_exists($file)) {
                $email = Configure::read('vusion.reportIssue.email');
                $this->Session->setFlash(
                    __("Sorry, no help available for %s Please leave us a comment %s.", $topic, $email));
            } else {
                $documentation = $this->readfile_chunked($file);
                $requestSuccess = true;
            }
        }
        $this->set(compact('topic', 'documentation', 'requestSuccess'));
    }
    
    
    protected function readfile_chunked($filename, $type='string') 
    {
        $chunk_array=array();
        $chunksize = 1*(1024*1024); // how many bytes per chunk
        $buffer = '';
        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }
        while (!feof($handle)) {
            switch($type)
            {
            case'array':
                // Returns Lines Array like file()
                $lines[] = fgets($handle, $chunksize);
                break;
            case'string':
                // Returns Lines String like file_get_contents()
                $lines = fread($handle, $chunksize);
                break;
            }
        }
        fclose($handle);
        return $lines;
    }
    
}
