<?php

App::uses('AppController', 'Controller');
App::uses('UnmatchableReply', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('ShortCode', 'Model');

class UnmatchableReplyController extends AppController
{

    var $helpers = array('Js' => array('Jquery'), 'Time', 'PhoneNumber');
    var $components = array('RequestHandler', 'LocalizeUtils', 'PhoneNumber');

    
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
        $this->ShortCode  = new ShortCode($options);
        $this->UnmatchableReply = new UnmatchableReply($options);
        $this->DialogueHelper   = new DialogueHelper();
    }


    public function index()
    {
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }

         $this->paginate = array(
                'all',
                'conditions' => $this->_getConditions(),
                'order'=> $order,
            );
        $countriesIndexes = $this->PhoneNumber->getCountriesByPrefixes();
        $unmatchableReplies = $this->paginate();
        $this->set(compact('unmatchableReplies', 'countriesIndexes'));
    }


    protected function _getFilterFieldOptions()
    {   
        return $this->LocalizeUtils->localizeLabelInArray(
            $this->UnmatchableReply->filterFields);
    }
   

    protected function _getFilterParameterOptions()
    {
        $shortcodes = $countries = array();
        $codes = $this->ShortCode->find('all');
        if (!empty($codes)) {
            foreach ($codes as $code) {
                $shortcodes[] = $code['ShortCode']['shortcode'];
                $countries[] = $code['ShortCode']['country'];
            }
        }
        sort($countries);
        
        return array(
            'operator' => $this->UnmatchableReply->filterOperatorOptions,
            'country' => (count($countries)>0? array_combine($countries, $countries) : array())
            );
    }

    
    protected function _getConditions($conditions = null)
    {
       $filter = array_intersect_key($this->params['url'], array_flip(array('filter_param', 'filter_operator')));
       $countryPrefixes = $this->PhoneNumber->getPrefixesByCountries();
       
        if (!isset($filter['filter_param'])) 
            return null;

        if (!isset($filter['filter_operator']) || !in_array($filter['filter_operator'], $this->UnmatchableReply->filterOperatorOptions)) {
            throw new FilterException('Filter operator is missing or not allowed.');
        }     

        $this->set('urlParams', http_build_query($filter));

        return $this->UnmatchableReply->fromFilterToQueryConditions($filter, $countryPrefixes);
    }


}
