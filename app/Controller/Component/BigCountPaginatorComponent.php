<?php
App::uses('PaginatorComponent', 'Controller/Component');


class BigCountPaginatorComponent extends PaginatorComponent
{

	public function paginate($object = null, $scope = array(), $whitelist = array()) {
		if (is_array($object)) {
			$whitelist = $scope;
			$scope = $object;
			$object = null;
		}

		$object = $this->_getObject($object);

		if (!is_object($object)) {
			throw new MissingModelException($object);
		}

		$options = $this->mergeOptions($object->alias);
		$options = $this->validateSort($object, $options, $whitelist);
		$options = $this->checkLimit($options);

		$conditions = $fields = $order = $limit = $page = $recursive = null;

		if (!isset($options['conditions'])) {
			$options['conditions'] = array();
		}

		$type = 'all';

		if (isset($options[0])) {
			$type = $options[0];
			unset($options[0]);
		}

		extract($options);

		if (is_array($scope) && !empty($scope)) {
			$conditions = array_merge($conditions, $scope);
		} elseif (is_string($scope)) {
			$conditions = array($conditions, $scope);
		}
		if ($recursive === null) {
			$recursive = $object->recursive;
		}

		$extra = array_diff_key($options, compact(
			'conditions', 'fields', 'order', 'limit', 'page', 'recursive'
		));

		if (!empty($extra['findType'])) {
			$type = $extra['findType'];
			unset($extra['findType']);
		}

		if ($type !== 'all') {
			$extra['type'] = $type;
		}

		if ((int)$page < 1) {
			$page = 1;
		}
		$page = $options['page'] = (int)$page;

		if ($object->hasMethod('paginate')) {
			$results = $object->paginate(
				$conditions, $fields, $order, $limit, $page, $recursive, $extra
			);
		} else {
			$parameters = compact('conditions', 'fields', 'order', 'limit', 'page');
			if ($recursive != $object->recursive) {
				$parameters['recursive'] = $recursive;
			}
			$results = $object->find($type, array_merge($parameters, $extra));
		}
		$defaults = $this->getDefaults($object->alias);
		unset($defaults[0]);

		if (!$results) {
			$count = 0;
		} elseif ($object->hasMethod('paginateCount')) {
			$count = $object->paginateCount($conditions, $recursive, $extra);
		} elseif ($page === 1 && count($results) < $limit) {
			$count = count($results);
		} else {
			$parameters = compact('conditions');
			if ($recursive != $object->recursive) {
				$parameters['recursive'] = $recursive;
			}
			$count = $object->find('count', array_merge($parameters, $extra));
		}
		$pageCount = (int)ceil($count / $limit);
		//$requestedPage = $page;
		//$page = max(min($page, $pageCount), 1);

		$paging = array(
			'page' => $page,
			'current' => count($results),
			'count' => $count,
			'prevPage' => ($page > 1),
			'nextPage' => ($count > ($page * $limit)),
			'pageCount' => $pageCount,
			'order' => $order,
			'limit' => $limit,
			'options' => Hash::diff($options, $defaults),
			'paramType' => $options['paramType']
		);

		if (!isset($this->Controller->request['paging'])) {
			$this->Controller->request['paging'] = array();
		}
		$this->Controller->request['paging'] = array_merge(
			(array)$this->Controller->request['paging'],
			array($object->alias => $paging)
		);

		//To avoid thowing the exception
		/*if ($requestedPage > $page) {
			throw new NotFoundException();
		}*/

		if (
			!in_array('Paginator', $this->Controller->helpers) &&
			!array_key_exists('Paginator', $this->Controller->helpers)
		) {
			$this->Controller->helpers[] = 'Paginator';
		}
		return $results;
	}

}

