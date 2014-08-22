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
        $this->ShortCode = new ShortCode($options);
        $this->CreditLog = new CreditLog($options);
    }
    
    
    public function index()
    {        
        $timeframeParameters = $this->_getTimeframeParameters();
        if (!is_array($timeframeParameters)) {
            $this->Session->setFlash($timeframeParameters);
        }
        
        $conditions       = CreditLog::fromTimeframeParametersToQueryConditions($timeframeParameters);
        $countriesCredits = $this->_getAllCredits($conditions);
        $this->set(compact('countriesCredits'));
    }    
    
    
    protected function _getAllCredits($conditions)
    {
        $countriesByPrefixes = $this->PhoneNumber->getCountriesByPrefixes();
        $countriesCredits    = $this->CreditLog->calculateCreditPerCountry($conditions, $countriesByPrefixes);
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
            
            $headersDates = array(
                'Date From',
                'Date To',
                '');
            
            foreach ($headersDates as $headerDate) {
                $line1 = array();
                if ($headerDate == 'Date From') {
                    $value = array($headerDate, $timeframeParameters['date-from']);
                    $line1 = $value;
                } else if ($headerDate == 'Date To') {
                    $value = array($headerDate, $timeframeParameters['date-to']);
                    $line1 = $value;
                } else {
                    $line1[] = '';
                }
                fputcsv($handle, $line1,',' , '"' );
            }
            
            
            $headers = array(
                'country',
                'shortcode',
                'program-name',
                'incoming',
                'outgoing',
                'outgoing-pending',
                'outgoing-acked',
                'outgoing-nacked',
                'outgoing-delivered',
                'outgoing-failed');
            
            // We write the headers
            fputcsv($handle, $headers,',' , '"' );
            
            //We extract the data and copy them in the file 
            $creditLogs = $this->_getAllCredits($conditions);
            
            foreach ($creditLogs as $creditLog) {
                foreach ($creditLog['codes'] as $code) {
                    $programsCount = $this->_getProgramCount($code);
                    $programIndex = 0;
                    for ($count =1; $count <= $programsCount; $count++) {
                        $line = array();
                        foreach ($headers as $header) {
                            if ($header == 'country') {
                                $line[] = $creditLog['country'];
                            } else if ($header == 'shortcode') {
                                $line[] = $code['code'];
                            } else {
                                $line[] = $this->_getProgramCredits($code, $header, $programIndex, $programsCount);                                
                            }
                        }
                        $programIndex++;
                        fputcsv($handle, $line,',' , '"');
                    }
                    $unmatchableReply = $this->_getUnmatcahableReplyCount($creditLog, $code, $headers);
                    fputcsv($handle, $unmatchableReply,',' , '"');
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
    
    
    protected function _getProgramCredits($code, $header, $programIndex, $programsCount)
    {
        $programCredits = '';
        
        if ($programIndex < $programsCount) {
            if (in_array($header, $code['programs'][$programIndex]))
                $programCredits = $code['programs'][$programIndex][$header];
        }
        
        return $programCredits;
    }
    
    
    protected function _getUnmatcahableReplyCount($creditLog, $code, $headers,  $line = array())
    {
        foreach ($headers as $header) {
            if ($header == 'country') {
                $line[] = $creditLog['country'];
            } else if ($header == 'shortcode') {
                $line[] = $code['code'];
            } else if ($header == 'program-name' ){
                $line[] = 'Unmatchable Reply';                                
            } else if ($header == 'incoming') {
                $line[] = $code['incoming'];                    
            } else if ($header == 'outgoing') {
                $line[] = $code['outgoing'];
            } else {
                $line[] = 0;
            }
        }
        return $line;
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
