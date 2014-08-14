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
      try{
            //First a tmp file is created
            $filePath = WWW_ROOT . "files/programs/"; 
            
            //TODO: the folder creation should be managed at program creation
            if (!file_exists($filePath)) {
                //echo 'create folder: ' . WWW_ROOT . "files/";
                mkdir($filePath);
                chmod($filePath, 0764);
            }
            
            //$programNow    = $this->ProgramSetting->getProgramTimeNow();
           // $programName   = $this->Session->read($programUrl.'_name');
            $fileName      = "CreditViewerExport.csv";            
            $fileFullPath  = $filePath . "/" . $fileName;
            $handle        = fopen($fileFullPath, "w");
            
            $headers       = $this->Participant->getExportHeaders($conditions);
            
            //Second we write the headers
            fputcsv($handle, $headers,',' , '"' );
            
            //Third we extract the data and copy them in the file            
            $participantCount = $this->Participant->find('count', array('conditions'=> $conditions));
            $pageCount        = intval(ceil($participantCount / $paginate['limit']));
            
            for ($count = 1; $count <= $pageCount; $count++) {
                $paginate['page'] = $count;
                $this->paginate   = $paginate;
                $participants     = $this->paginate();
                foreach ($participants as $participant) {
                    $line = array();
                    foreach ($headers as $header) {
                        if (in_array($header, array('phone', 'last-optin-date', 'last-optout-date'))) {
                            $line[] = $participant['Participant'][$header];
                        } else if ($header == 'tags') {
                            $line[] = implode(', ', $participant['Participant'][$header]);         
                        } else {
                            $value  = $this->_searchProfile($participant['Participant']['profile'], $header);
                            $line[] = $value;
                        }
                    }
                    fputcsv($handle, $line,',' , '"');
                }
            }
            $requestSuccess = true;
            $this->set(compact('requestSuccess', 'fileName'));
        } catch (Exception $e) {
            $this->Session->setFlash($e->getMessage());
            $this->set(compact('requestSuccess'));
        }  
    }

    
}
