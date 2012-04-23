<?php

App::uses('AppController', 'Controller');

class DocumentationController extends AppController
{

     var $components = array('RequestHandler');
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
             	     $documentation = "Sorry, no help available for '". $topic."'. Please leave us a comment [help@texttochange.com].";
             } else {
             	 $documentation = $this->readfile_chunked($file);
             }
         }
         $this->set(compact('topic','documentation'));
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
