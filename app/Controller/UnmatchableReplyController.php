<?php

App::uses('AppController', 'Controller');
App::uses('UnmatchableReply', 'Model');
App::uses('DialogueHelper', 'Lib');

class UnmatchableReplyController extends AppController
{

    var $helpers = array('Js' => array('Jquery'), 'Time');


    public function beforeFilter()
    {
        parent::beforeFilter();
    }


    public function constructClasses()
    {
        parent::constructClasses();
        
        $options = array(
            'database' => 'vusion'
            );
        
        $this->UnmatchableReply = new UnmatchableReply($options);
        $this->DialogueHelper   = new DialogueHelper();
    }


    public function index()
    {
        $this->set('filterFieldOptions', $this->UnmatchableReply->fieldFilters);
        
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

        $unmatchableReplies = $this->paginate();
        $this->set(compact('unmatchableReplies'));
    }
    
    
    protected function _getConditions()
    {
        $conditions = null;
        
        $onlyFilterParams = array_intersect_key($this->params['url'], array_flip(array('filter_param')));

        if (!isset($onlyFilterParams['filter_param'])) 
            return $conditions;
       
        $urlParams = http_build_query($onlyFilterParams);
        $this->set('urlParams', $urlParams);
        
        foreach($onlyFilterParams['filter_param'] as $onlyFilterParam) {
            if ($onlyFilterParam[1]=='date-from' && isset($onlyFilterParam[2])) {
                $conditions['timestamp']['$gt'] = $this->DialogueHelper->ConvertDateFormat($onlyFilterParam[2]);
            } elseif ($onlyFilterParam[1]=='date-to' && isset($onlyFilterParam[2])) {
                $conditions['timestamp']['$lt'] = $this->DialogueHelper->ConvertDateFormat($onlyFilterParam[2]);
            } elseif ($onlyFilterParam[1] == 'participant-phone' && isset($onlyFilterParam[2])) {
                $phoneNumbers = explode(",", str_replace(" ", "", $onlyFilterParam[2]));
                if ($phoneNumbers) {
                    if (count($phoneNumbers) > 1) {
                        $or = array();
                        foreach ($phoneNumbers as $phoneNumber) {
                            $regex = new MongoRegex("/^\\".$phoneNumber."/");
                            $or[] = array('participant-phone' => $regex);
                        }
                        $conditions['$or'] = $or;
                    } else {
                        $conditions['participant-phone'] = new MongoRegex("/^\\".$phoneNumbers[0]."/");
                    }
                }
            } else {
                $this->Session->setFlash(__('The parameter(s) for "%s" filtering are missing.',$onlyFilterParam[1]), 
                'default',
                array('class' => "message failure")
                );
            }
        }
        
        return $conditions;
    }

    
}
