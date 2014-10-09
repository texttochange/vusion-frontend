<?php
App::uses('VusionConst', 'Lib');


class FilterBehavior extends ModelBehavior {

    var $MAX_JOIN_PHONES = VusionConst::MAX_JOIN_PHONES;
    var $joinCursor = null;


	public function setup($model, $settings = array()) 
	{
		if (!isset($model->filterFields)) {
			throw new Exception("Model %s is missing filters definition" % $model->name);
		}
	}


	public function getFilters($model, $subset=null) 
    {
		if (!isset($subset)) {
            return $model->filterFields;
        }
        $subsetFilterFields = array();
        foreach ($model->filterFields as $field => $filterField) {
            $subsetOperator = array();
            foreach ($filterField['operators'] as $operator => $details) {
                if (isset($details[$subset])) {
                    $subsetOperator[$operator] = array('parameter-type' => $details['parameter-type']);
                }
            }
            if ($subsetOperator != array()) {
                $subsetFilterField              = $filterField;
                $subsetFilterField['operators'] = $subsetOperator;
                $subsetFilterFields[$field]     = $subsetFilterField;
            }
        }
        return $subsetFilterFields;
    }

    public function getFilterOperators($model)
    {
        if (isset($model->filterOperatorOptions)) {
            return $model->filterOperatorOptions;
        }
        return array();
    }


    public function isJoin($model, $filterParam) 
    {
        $filters = $model->getFilters();
        if (!isset($filters[$filterParam[1]]['operators'][$filterParam[2]])) {
            return false;
        }
        if (isset($filters[$filterParam[1]]['operators'][$filterParam[2]]['join'])) {
            return true;
        }
        return false;
    }


    public function getJoin($model, $filterParam) {
        if (!$model->isJoin($filterParam)) {
            return null;
        }
        $filters = $model->getFilters();
        return $filters[$filterParam[1]]['operators'][$filterParam[2]]['join'];
    }


    public function validateFilter($model, $filter, $subset=null) 
    {
        $resultFilter = array(
            'filter' => array(
                'filter_operator' => null,
                'filter_param' => array()),
            'joins' => array(),
            'errors' => array());

        if (!isset($filter['filter_param'])) 
            return $resultFilter;
        
        if (!isset($filter['filter_operator'])) {
            throw new FilterException(__('Filter operator is missing.'));
        } 
        if (!in_array($filter['filter_operator'], $model->getFilterOperators())) {
            throw new FilterException(__('Filter operator is not allowed.'));
        }
        $resultFilter['filter']['filter_operator'] = $filter['filter_operator'];

        foreach($filter['filter_param'] as $filterParam) {
            $valid = $model->validateFilterParam($filterParam);
            if (is_bool($valid) && $valid) {
                if ($model->isJoin($filterParam)) {
                    $resultFilter['joins'][] = $model->getJoin($filterParam);
                } else {
                    $resultFilter['filter']['filter_param'][] = $filterParam;
                }
                continue;
            } 
            $resultFilter['errors'][] = $valid;
        }
        return $resultFilter;
    }


    public function validateFilterParam($model, $filterParam, $subset=null) {
    	if (!isset($filterParam[1])) {
            return "condition is missing";
        }

        $filterFields = $model->getFilters($subset);
        
        if (!isset($filterFields[$filterParam[1]])) {
            return array($filterParam[1], __('not supported'));
        }
        if (!isset($filterParam[2])) {
            return array($filterParam[1], __('operator not defined'));
        }
        
        if (!isset($filterFields[$filterParam[1]]['operators'][$filterParam[2]])) {
            return array($filterParam[1], $filterParam[2], __('not supported'));
        }
                
        if ($filterFields[$filterParam[1]]['operators'][$filterParam[2]]['parameter-type'] != 'none' && (!isset($filterParam[3]) || $filterParam[3]=='')) {
            return array($filterParam[1], $filterParam[2], __('missing parameter'));
        }
        return true;
    }


	public function fromFiltersToQueryCondition($model, $filters, $conditions=array()) {
        
        foreach ($filters['filter_param'] as $filterParam) {
            $condition = $model->fromFilterToQueryCondition($filterParam);
            if ($filters['filter_operator'] == "all") {
                if (count($conditions) == 0) {
                   $conditions = (isset($filterParam[3])) ? array_filter($condition) : $condition;
                } elseif (!isset($conditions['$and'])) {
                    $condition = array_filter($condition);
                    if (!empty($condition))
                        $conditions = array('$and' => array($conditions, $condition));
                } else {
                    $condition = array_filter($condition);
                    if (!empty($condition))
                        array_push($conditions['$and'], $condition);
                }
            } elseif ($filters['filter_operator'] == "any") {
                if (count($conditions) == 0) {
                    $conditions = $condition;
                } elseif (!isset($conditions['$or'])) {
                    $conditions = array('$or' => array($conditions, $condition));
                } else {
                    array_push($conditions['$or'], $condition);
                }
            }
        }
        return $conditions;
    }

    //Require to allow allSafeJoin as find function in Model
    //All make the protected _findAllSafeJoin call this function
    public function findAllSafeJoin($model, $state, $query, $results=array()) {
        if ($state === 'before') {
            if (!isset($query['limit'])) {
                throw new VusionException('FindAllSafe has to be used with limit');
            }
            //TODO: the $join operator can be in different part of the conditions
            if (isset($query['conditions']['phone']['$join']) || $model->joinCursor != null) {
                if (isset($query['conditions']['phone']['$join'])) {
                    $model->joinCursor = $query['conditions']['phone']['$join'];
                    $model->joinCursor->rewind();    //initialize the cursor
                    unset($query['conditions']['phone']['$join']);
                }
                $query['conditions']['phone']['$in'] = array();
                $i = 1;
                while ($model->joinCursor->valid()) {
                    $phone = $model->joinCursor->current();
                    $query['conditions']['phone']['$in'][] = $phone['_id'];
                    if ($i > $model->MAX_JOIN_PHONES) {
                        break;
                    }
                    $model->joinCursor->next();
                    $i++;
                }
                $model->joinCursor->next();
                if (!$model->joinCursor->valid()) {
                    $model->joinCursor = null;
                }
            } 
            return $query;
        } 
        if (($state === 'after') && ($model->joinCursor != null)) {
            if (count($results) < $query['limit']) {
                $laterResults = $model->find('allSafeJoin', $query);
                $results = array_merge($results, $laterResults);
            }
        }
        return $results;
    }

    public static function hasJoin($conditions) {
        return isset($conditions['phone']['$join']);
    }


    public function countSafeJoin($model, $callback, $conditions = true, $limit = null, $timeout = 30000) {
        $result = 0;
        $joinCursor = $conditions['phone']['$join'];
        $joinCursor->rewind();    //initialize the cursor
        unset($conditions['phone']['$join']);
        while ($joinCursor->valid()) {
            $conditions['phone']['$in'] = array();
            $i = 1;
            while ($joinCursor->valid()) {
                $phone = $joinCursor->current();
                $conditions['phone']['$in'][] = $phone['_id'];
                if ($i > $model->MAX_JOIN_PHONES) {
                    break;
                }
                $joinCursor->next();
                $i++;
            }
            $joinCursor->next();
            $result = $result + $model->{$callback}($conditions, $limit, $timeout);
        }
        return $result;
    }

}