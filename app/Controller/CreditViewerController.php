<?php
App::uses('AppController', 'Controller');
App::uses('DialogueHelper', 'Lib');
App::uses('Shortcode', 'Model');
App::uses('CreditLog', 'Model');
App::uses('Program', 'Model');


class CreditViewerController extends AppController
{
    var $helpers = array(
        'Js' => array('Jquery'), 
        'Time', 
        'PhoneNumber',
        'Number');
    var $components = array(
        'ProgramPaginator',
        'CreditManager',
        'RequestHandler',
        'LocalizeUtils',
        'PhoneNumber');
    
    var $uses = array(
        'Program', 
        'Group');
    
    
    public function beforeFilter()
    {    
        parent::beforeFilter();
    }
    
    
    public function constructClasses()
    {
        parent::constructClasses();
        
        if (!Configure::read("mongo_db")) {
            $options = array(
                'database' => 'vusion'
                );
        } else {
            $options = array(
                'database' => Configure::read("mongo_db")
                );
        }
        $this->ShortCode        = new ShortCode($options);
        $this->CreditLog        = new CreditLog($options);
    }
    
    
    public function index()
    {        
        $timeframeParameters = $this->_getTimeframeParameters();
        if (!is_array($timeframeParameters)) {
            $this->Session->setFlash($timeframeParameters);
        }

        $conditions = CreditLog::fromTimeframeParametersToQueryConditions($timeframeParameters);
        $countriesCredits   = $this->_getAllCredits($conditions);
        $this->set(compact('countriesCredits'));
    }    

    
    protected function _getAllCredits($conditions)
    {
        $countriesByPrefixes = $this->PhoneNumber->getCountriesByPrefixes();
        $countriesCredits = $this->CreditLog->calculateCreditPerCountry($conditions, $countriesByPrefixes);
        foreach ($countriesCredits as &$countryCredits) {
            foreach ($countryCredits['codes'] as &$codeCredits) {
                $codeCredits['code'] = DialogueHelper::fromPrefixedCodeToCode($codeCredits['code']);
                foreach ($codeCredits['programs'] as &$programCredits) {
                    if (!isset($programCredits['program-name'])) { 
                        $program = $this->Program->find('first', array('conditions' => array('database' => $programCredits['program-database'])));
                        $programCredits['program-name'] = $program['Program']['name'];
                    }
                }
            }
        }
        return $countriesCredits;
    }


    protected function _getTimeframeParameters()
    {
        $parameters = array_intersect_key($this->params['url'], array_flip(array('date-from', 'date-to', 'predefined-timeframe')));

        //Default parameters
        if ($parameters == array()) {
            $parameters = array(
                'predefined-timeframe' => 'today',
                'date-from' => '',
                'date-to' => '');
        }
        //Create missing key
        $parameters = array_merge(
            array(
                'predefined-timeframe' => '',
                'date-from' => '',
                'date-to' => ''),
            $parameters);

        if ($parameters['predefined-timeframe'] != '' && ($parameters['date-to']!= '' || $parameters['date-from']!= '')) {
                return __("Please select between date or predefine timefame");
            }
       
        
        $this->set('timeframeParams', $parameters);        

        return $parameters;
    }
    
    
    public function export()
    {
         $url                 = $this->params['controller'];
         $requestSucces       = false;
         $timeframeParameters = $this->_getTimeframeParameters();
         $conditions          = CreditLog::fromTimeframeParametersToQueryConditions($timeframeParameters);
         
         $paginate = array(
            'all',
            'limit' => 500,
            'maxLimit' => 500);

      try{
            $filePath = WWW_ROOT . "files/programs/" . $url;
            
            if (!file_exists($filePath)) {
                mkdir($filePath);
                chmod($filePath, 0764);
            }
            
            $now           = new DateTime('now');
            $fileName      = $url .'_' . $now->format('Y-m-d_H-i-s') . '.csv';            
            $fileFullPath  = $filePath . "/" . $fileName;
            $handle        = fopen($fileFullPath, "w");            
            $headers       = array(
                'country',
                'code',
                'program-name',
                'incoming',
                'outgoing',
                'outgoing-pending',
                'outgoing-acked',
                'outgoing-nacked',
                'outgoing-delivered',
                'outgoing-failed');
            
            //Second we write the headers
            fputcsv($handle, $headers,',' , '"' );
            
            //Third we extract the data and copy them in the file            
            $creditLogCount = $this->CreditLog->find('count');
            $pageCount      = intval(ceil($creditLogCount / $paginate['limit']));
            
            /*for ($count = 1; $count <= $pageCount; $count++) {
                $paginate['page'] = $count;
                $this->paginate   = $paginate;
                $creditLogs       = $this->_getAllCredits($conditions);
                
                foreach ($creditLogs as $creditLog) {
                    print_r($creditLog);
                    echo "*************************************";
                    
                    $line = array();
                    $codesCount = count($creditLog['codes']);
                    foreach ($headers as $header) {
                        foreach ($creditLog['codes'] as $code) {
                            $programsCount = count($code['programs']);
                            for ($count =1; $count <= $programsCount; $count++) {
                                if ($header == 'country') {
                                    //if ($creditLog['country'] == $header)
                                    //$line[] = $creditLog['country'];
                                    print_r($header);
                                    echo "     ";
                                    print_r($creditLog['country']);
                                    echo "\n";
                                }
                                /*if ($header == 'code') {
                                    //$line[] = $code['code'];
                                   print_r($header);
                                    echo "     ";
                                    print_r($code['code']);
                                    echo "\n";
                                }
                            }
                            foreach ($code['programs'] as $key => $value) {
                                    if (isset($code['programs'][$key][$header]))  {
                                        //$line[] = $code['programs'][0][$header];
                                        print_r($header);
                                        echo "     ";
                                        print_r($code['programs'][$key][$header]);
                                        echo "\n";
                                    } 
                                }
                        }
                        
                        
                        /*
                        /*if (isset($creditLog[$header])) {
                        $line[] = $creditLog[$header];
                        } else if ($header == 'code') {
                        $line[] = $creditLog['codes'][0][$header];         
                        } else if ($header == 'program-name') {
                        // $programNames = $creditLog['codes'][0]['programs'][0]['program-name'];
                        // $value  = $this->_searchLabel($programNames, $header);
                        $line[] = $creditLog['codes'][0]['programs'][0][$header];
                        } else {
                        $line[] = "";
                        }
                    }
                    fputcsv($handle, $line,',' , '"');
                }
            }*/
            
            $creditLogs       = $this->_getAllCredits($conditions);
            
            foreach ($creditLogs as $creditLog) {
                //print_r($creditLog);
                //echo "*************************************";
                foreach ($creditLog['codes'] as $code) {
                    $programsCount = $this->_getProgramCount($code);
                    for ($count =1; $count <= $programsCount; $count++) {
                        $line = array();
                        foreach ($headers as $header) {
                            if ($header == 'country') {
                                $line[] = $creditLog['country'];
                            } else {
                                $line[] = $this->_getProgramCredits($code, $header);
                            }
                        }
                        //print_r($line);
                        fputcsv($handle, $line,',' , '"');
                    }
                }
            }
            
            $requestSuccess = true;
            $this->set(compact('requestSuccess', 'fileName'));
        } catch (Exception $e) {
            $this->Session->setFlash($e->getMessage());
            $this->set(compact('requestSuccess'));
        }  
    }
    
    protected function _getProgramCount($code)
    {
        $programsCount = 0;
        
        $programsCount += count($code['programs']);        
        return $programsCount;
    }
    
    
    protected function _getProgramCredits($code, $header)
    {
        $programCredits = '';
        //print_r($code);
        //echo "*************************************";
        foreach ($code['programs'] as $key => $value) {
            //print_r($code['programs'][$key][$header]);
            //echo "*************************************";
            if (isset($code['programs'][$key][$header])) {
                //$programCredits = $code['programs'][$key][$header];
                // if (in_array($header, $value)) {
                print_r($value[$header]);
                echo "*************************************";
                 echo "\n";
                $programCredits = $value[$header];
                
            }
            
            
            
        }
        /*if ($programIndex < $programsCount) {//echo "here";
        $programCredits = $code['programs'][$programIndex][$header];
        }*/
        /*for ($index = 0; $index < $programCount:  as $program) {
        if (in_array($header, $program)) {
        $programCredits = $program[$header];
        }
        }*/
        return $programCredits;
    }
    
    
    public function download()
    {
        $url          = $this->params['controller'];
        $fileName     = $this->params['url']['file'];        
        $fileFullPath = WWW_ROOT . "files/programs/" . $url . "/" . $fileName; 
        
        if (!file_exists($fileFullPath)) {
            throw new NotFoundException();
        }
        
        $this->response->header("X-Sendfile: $fileFullPath");
        $this->response->header("Content-type: application/octet-stream");
        $this->response->header('Content-Disposition: attachment; filename="' . basename($fileFullPath) . '"');
        $this->response->send();
    }
    
    
}
