<?php
App::uses('Component', 'Controller');

class EmulatePaginatorComponent extends Component {
    
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
	}
    
    public function paginate($array)
    {
        if (!is_array($array)) {
			throw new MissingModelException($array);
		}
		
		$object = $this->Controller->uses[0];
		
		$results = $array;
		
		$limit = $this->settings['limit'];
		$page = $this->settings['page'];
		$order = null;
		$count =  count($results);
		$pageCount = intVal(ceil($count / $limit));

		$paging = array(
			'page' => $page,
			'current' => count($results),
			'count' => $count,
			'prevPage' => ($page > 1),
			'nextPage' => ($count > ($page * $limit)),
			'pageCount' => $pageCount,
			'order' => $order,
			'limit' => $limit,
			'paramType' => $this->settings['paramType']
		);
		if (!isset($this->Controller->request['paging'])) {
			$this->Controller->request['paging'] = array();
		}
		$this->Controller->request['paging'] = array_merge(
			(array)$this->Controller->request['paging'],
			array($object => $paging)
		);
			
        return $results;
    }
    
    public function tester($arrayWithData) {
        print_r($this->Paginator->Controller->request->params['paging']);
        /*$count = $this->Paginator->counter('{:count}');
        $count = count($arrayWithData);*/
        return count($arrayWithData);        
    }    
    
}
