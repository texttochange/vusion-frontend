<?php
App::uses('AppController', 'Controller');


class ProgramAjaxController extends AppController
{
    var $components = array(
        'RequestHandler',
        'Stats');
    
    function constructClasses()
    {
        parent::constructClasses();
    }
    
    
    public function getProgramStatsCached()
    { 
        print_r($this->RequestHandler);
        //print_r($this->params['program']);
       // $t = $this->response->not_modified($this->Stats->getProgramStats($programUrl[0]['Program']['database']));
       //print_r($t);
         /*$programUrl = $this->params['url']['programUrl'];
            if(count($programUrl) > 0){           
                $programStats = $this->Stats->getProgramStats($programUrl[0]['Program']['database'], true);
                $result = array('status' =>'ok', 'programUrl' => $programUrl[0]['Program']['url'], 'programStats' =>  programStats);
            }else{
                $result = array('status' =>'fail', 'programUrl' => $programParamsUrl, 'reason' => "This program url ". $programParamsUrl." doesn't exist", 'programStats' => null);
            }
            $this->set(compact('result'));
        } */
    }
}
