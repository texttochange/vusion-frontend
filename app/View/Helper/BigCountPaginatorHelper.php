<?php
App::uses('PaginatorHelper', 'View/Helper');


class BigCountPaginatorHelper extends PaginatorHelper {


	protected function _hasPage($model, $page) 
	{
	    $params = $this->params($model);
	    if ($page == 'next' && $params['count'] === 'many') {
	        return true;
	    }
		if (!empty($params)) {
			if ($params["{$page}Page"] == true) {
				return true;
			}
		}
		return false;
	}


	public function counter($options = array()) 
	{
	   if (is_string($options)) {
			$options = array('format' => $options);
		}

		$options = array_merge(
			array(
				'model' => $this->defaultModel(),
				'format' => 'pages',
				'separator' => __d('cake', ' of ')
			),
		$options);
		$paging = $this->params($options['model']);
		if ($paging['pageCount'] == 0) {
			$paging['pageCount'] = 1;
		}
		$start = 0;
		if ($paging['count'] === 'many' || $paging['count'] >= 1) {
			$start = (($paging['page'] - 1) * $paging['limit']) + 1;
		}
		$end = $start + $paging['limit'] - 1;	
		if ($paging['count'] !== 'many' && $paging['count'] < $end) {
			$end = $paging['count'];
		}

		switch ($options['format']) {
			case 'range':
				if (!is_array($options['separator'])) {
					$options['separator'] = array(' - ', $options['separator']);
				}
				$out = $start . $options['separator'][0] . $end . $options['separator'][1];
				$out .= $paging['count'];
			break;
			case 'pages':
				$out = $paging['page'] . $options['separator'] . $paging['pageCount'];
			break;
			default:
				$map = array(
					'%page%' => $paging['page'],
					'%pages%' => $paging['pageCount'],
					'%current%' => $paging['current'],
					'%count%' => $paging['count'],
					'%start%' => $start,
					'%end%' => $end,
					'%model%' => strtolower(Inflector::humanize(Inflector::tableize($options['model'])))
				);
				$out = str_replace(array_keys($map), array_values($map), $options['format']);

				$newKeys = array(
					'{:page}', '{:pages}', '{:current}', '{:count}', '{:start}', '{:end}', '{:model}'
				);
				$out = str_replace($newKeys, array_values($map), $out);
		
			break;
		}
		return $out;
	}

}