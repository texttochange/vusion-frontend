<?php
App::uses('Component', 'Controller');
App::uses('ShortCode', 'Model');

class ProgramPaginatorComponent extends Component {
    
    var $settings = array(
		'page' => 1,
		'limit' => 20,
		'maxLimit' => 100,
		'paramType' => 'named'
	);
	
	
	public function __construct($collection, $settings = array())
	{
	    $settings = array_merge($this->settings, (array)$settings);
	    $this->Controller = $collection->getController();
        parent::__construct($collection, $settings);

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
	}
    
    public function paginate($paginateArray)
    {
        if (!is_array($paginateArray)) {
			throw new MissingModelException($paginateArray);
		}
		
		$object = $this->Controller->uses[0];
		$options = array();
		$params = $this->Controller->request->params;		
		$limit = (int)$this->settings['limit'];
		$page = $options['page'] = (isset($params['named']['page'])) ? (int)$params['named']['page'] : 1;		
		$order = null;
		$count =  count($paginateArray);
		$pageCount = intVal(ceil($count / $limit));
		$page_offset = ($page - 1) * $limit;
		
		$results = array_slice($paginateArray, $page_offset, $limit);

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


    public function getProgramDetails($programData)
    {
        $database           = $programData['Program']['database'];
        $tempProgramSetting = new ProgramSetting(array('database' => $database));
        $shortcode          = $tempProgramSetting->find('programSetting', array('key'=>'shortcode'));

        if (isset($shortcode[0]['ProgramSetting']['value'])) {
            $code = $this->ShortCode->find('prefixShortCode', array('prefixShortCode'=> $shortcode[0]['ProgramSetting']['value']));
            $programData['Program']['shortcode'] = ($code['ShortCode']['supported-internationally'] ? $code['ShortCode']['shortcode'] : $code['ShortCode']['country']."-".$code['ShortCode']['shortcode']);                
        }

        if ($this->params['ext']!='json') {
            $programData['Program']['stats'] = $this->_getProgramStats($database);
            
            $programDetails = array(
                'program' =>  $programData,
                'shortcode' => (isset($code)) ? $code : array()
                );
        }

        return $programDetails;
    }
    
    
    protected function _getProgramStats($database)
    {
        $programStats = array();
        
        $tempParticipant                   = new Participant(array('database' => $database));
        $programStats['participant-count'] = $tempParticipant->find('count'); 
        $tempHistory                       = new History(array('database' => $database));
        $programStats['history-count']     = $tempHistory->find('count');
        $tempSchedule                      = new Schedule(array('database' => $database));
        $programStats['schedule-count']    = $tempSchedule->find('count');
        
        return $programStats;
    }
    
    
    public function getNameSqlCondition($conditions)
    {
        if (empty($conditions))
            return array();
        
        $result = array();
        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->getNameSqlCondition($value));
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
    
    /*
    public function getProgramsList($conditions)
    {
        $nameCondition = $this->getNameSqlCondition($conditions);
        if (isset($conditions['$or']) and !isset($nameCondition['OR'])) {
            $programsList = $this->Program->find('all', array(
                'conditions' => $nameCondition,
                'order' => array(
                    'Program.created' => 'desc'
                    ))
                );
        } else {
            $programsList =  $this->Program->find('all');
        }
        return $programsList;
    }
    
    
    public function getPrograms($conditions)
    {
        $nameCondition = $this->getNameSqlCondition($conditions);
        if (isset($conditions['$or']) and !isset($nameCondition['OR'])) {
            $programs = $this->Program->find('all', array(
                'conditions' => $nameCondition,
                'order' => array(
                    'Program.created' => 'desc'
                    ))
                );
        } else {
            $programs =  $this->Program->find('all');
        }
        return $programs;
    }*/
    
}
