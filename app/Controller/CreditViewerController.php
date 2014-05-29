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
        'PhoneNumber');
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

    
}
