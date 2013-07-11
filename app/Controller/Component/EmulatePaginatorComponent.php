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
    
}
