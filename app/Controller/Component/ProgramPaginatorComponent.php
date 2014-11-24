<?php
App::uses('Component', 'Controller');
App::uses('PaginatorComponent', 'Controller/Component');
App::uses('ShortCode', 'Model');
App::uses('Program', 'Model');
App::uses('ProgramSpecificMongoModel', 'Model');

class ProgramPaginatorComponent extends PaginatorComponent 
{  
   
 
    public function __construct($collection, $settings = array())
    {
        $settings = array_merge($this->settings, (array)$settings);
        $this->Controller = $collection->getController();
        parent::__construct($collection, $settings);

        $this->Program = ClassRegistry::init('Program');
        $this->ShortCode = ClassRegistry::init('ShortCode');
        /*if (!Configure::read("mongo_db")) {
            $options = array(
                'database' => 'vusion'
                );
        } else {
            $options = array(
                'database' => Configure::read("mongo_db")
                );
        }
        $this->ShortCode  = new ShortCode($options);*/
    }
    
    public function paginate()
    {
        $type = 'all';
        $options = $this->Controller->paginate;

        if (!isset($options['conditions'])) {
            $options['conditions'] = array();
        }

        extract($options);

        $extra = array_diff_key($options, compact(
            'conditions', 'fields', 'order', 'limit', 'page', 'recursive'
        ));
        if ($type !== 'all') {
            $extra['type'] = $type;
        }

        $parameters = compact('conditions', 'fields', 'order', 'limit', 'page');
        ##TODO to be consitent with pagniator need to refactor Program Model
        $parameters += $extra;  
        if (!isset($conditions['$or'])) {
            $parameters['conditions'] = $this->getConditionProgramSql($conditions);
        } else {
            $parameters['conditions'] = array();
        }
        $programs =  $this->Program->find($type, $parameters);
        $programs = $this->filterPrograms($programs, $conditions);
        return $this->paginatePrograms($programs);
    }


    public function paginatePrograms($programs) 
    { 
        if (!is_array($programs)) {
            throw new Exception($programs);
        }

        $object = $this->Controller->uses[0];
        $options = array();
        $params = $this->Controller->request->params;
        $limit = (int)$this->settings['limit'];
        $page = $options['page'] = (isset($params['named']['page'])) ? (int)$params['named']['page'] : 1;
        $order = null;
        $count =  count($programs);
        $pageCount = intVal(ceil($count / $limit));
        $pageOffset = ($page - 1) * $limit;
        
        $results = array_slice($programs, $pageOffset, $limit);

        $paging = array(
            'page' => $page,
            'current' => count($results),
            'count' => $count,
            'prevPage' => ($page > 1),
            'nextPage' => ($count > ($page * $limit)),
            'pageCount' => $pageCount,
            'order' => $order,
            'limit' => $limit,
            'options' => $options,
            'paramType' => $this->settings['paramType']
        );

        # Use pagintor helper in the view to display our results
        if (!isset($this->Controller->request['paging'])) {
            $this->Controller->request['paging'] = array();
        }
        $this->Controller->request['paging'] = array_merge(
            (array)$this->Controller->request['paging'],
            array($object => $paging)
        );

        if (!in_array('Paginator', $this->Controller->helpers) &&
            !array_key_exists('Paginator', $this->Controller->helpers))
        {
            $this->Controller->helpers[] = 'Paginator';
        }
        return $results;
    }


    public function filterPrograms($programs, $conditions) 
    {
        if ($programs === array()) {
            return array();
        }
        return array_values(
                array_filter(
                    array_map(array($this, "filterProgram"), $programs, array_fill(0, count($programs), $conditions))));
    }


    public function filterProgram($program, $conditions) 
    {
        $programDetails = $this->getProgramDetails($program);
        if (!Program::matchProgramConditions($programDetails, $conditions)) {
            return false;
        }
        return array('Program' => $programDetails['Program']);
    } 

    ##TODO move to another component that will be ProgramDetails
    public function getProgramDetails($program)
    {
        $database           = $program['Program']['database'];
        $tempProgramSetting = ProgramSpecificMongoModel::init('ProgramSetting', $database, true);
        $shortcode          = $tempProgramSetting->getProgramSetting('shortcode');

        if (isset($shortcode)) {
            $code = $this->ShortCode->find('prefixShortCode', array('prefixShortCode'=> $shortcode));
            $program['Program']['shortcode'] = $code['ShortCode']['shortcode'];
            $program['Program']['country'] = $code['ShortCode']['country'];
            $program['Program']['prefixed-shortcode'] = $shortcode;
        }

        if ($this->params['ext']!='json') {
            $programDetails = array(
                'Program' =>  $program['Program'],
                'ShortCode' => (isset($code['ShortCode'])) ? $code['ShortCode'] : array(),
                'settings' => $tempProgramSetting->getProgramSettings()
                );
        } else {
            $programDetails = $program;
        }
        unset($tempProgramSetting);
        return $programDetails;
    }
    
    
    #TODO move to Program Model (contain SQL)
    public function getConditionProgramSql($conditions)
    {
        if (empty($conditions))
            return array();
        
        $result = array();
        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->getConditionProgramSql($value));
            } else {
                if ($key == 'name LIKE' or $key == 'name') {
                    array_push($result, $conditions);
                }
            }
        }
        if (count($result) > 1) {
            $newResult['OR'] = $result;
            $result = $newResult;
        }
        return $result;
    }
    
}
