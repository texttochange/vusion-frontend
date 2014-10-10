<?php
App::uses('VusionConst', 'Lib');


class FilterBehavior extends ModelBehavior {

    var $joinCursor = null;
    var $originalJoinQuery = null;
   
	public function setup($model, $settings = array()) 
	{
		if (!isset($model->filterFields)) {
			throw new Exception("Model %s is missing filters definition" % $model->name);
		}
        $model->MAX_JOIN_PHONES = VusionConst::MAX_JOIN_PHONES;
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
                    if ($resultFilter['joins'] != array()) {
                        $resultFilter['errors'][] = __('only one join is allowed');
                        continue;
                    }
                    $resultFilter['joins'][] = $model->getJoin($filterParam);
                }
                $resultFilter['filter']['filter_param'][] = $filterParam;
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


	public function fromFiltersToQueryCondition($model, $filters, $conditions=array()) 
    {
        foreach ($filters['filter_param'] as $filterParam) {

            $condition = $model->fromFilterToQueryCondition($filterParam);
            if ($condition == array()) {
                continue;
            }
            if ($filters['filter_operator'] === "all") {
                if (count($conditions) == 0) {
                   $conditions = (isset($filterParam[3])) ? array_filter($condition) : $condition;
                } elseif (!isset($conditions['$and'])) {
                    $conditions = array('$and' => array($conditions, $condition));
                } else {
                    array_push($conditions['$and'], $condition);
                }
            } elseif ($filters['filter_operator'] === "any") {
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
    public function findAllSafeJoin($model, $state, $query, $results=array()) 
    {    
        if ($state === 'before') {
            if (!isset($query['limit'])) {
                throw new VusionException('FindAllSafe has to be used with limit');
            }
            if (FilterBehavior::hasJoin($query) || $model->joinCursor != null) {
                if ($cursor = FilterBehavior::hasJoin($query)) {
                    $model->joinCursor = $cursor;
                    $model->joinCursor->rewind();
                    $model->originalJoinQuery = $query;
                }
                $join = array('$in' => array());
                $i = 1;
                while ($model->joinCursor->valid()) {
                    $phone = $model->joinCursor->current();
                    $join['$in'][] = $phone['_id'];
                    if ($i > $model->MAX_JOIN_PHONES) {
                        break;
                    }
                    $model->joinCursor->next();
                    $i++;
                }
                $query = FilterBehavior::replaceJoin($model->originalJoinQuery, $join);
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


    //Only works with 1 join only
    public static function hasJoin($conditions) 
    {
        foreach ($conditions as $key => $value) {
            if ($key === '$join') {
                return $conditions[$key];
            }
            if (is_array($value)) {
                if ($join = FilterBehavior::hasJoin($value)) {
                    return $join;
                }
            }
        }
        return false;
    }


    public static function replaceJoin($conditions, $replaceWith) 
    {
        foreach ($conditions as $key => $value) {
            if ($key === '$join') {
                unset($conditions['$join']);
                $conditions = array_merge($conditions, $replaceWith);
            }
            if (is_array($value)) {
                $conditions[$key] = FilterBehavior::replaceJoin($value, $replaceWith);
           }
        }
        return $conditions;
    }


    public function countSafeJoin($model, $callback, $conditions = true, $limit = null, $timeout = 30000) 
    {
        $result = 0;
        $joinCursor = FilterBehavior::hasJoin($conditions);
        $originalJoinQuery = $conditions;
        $joinCursor->rewind();    //initialize the cursor
        while ($joinCursor->valid()) {
            $join = array('$in' => array());
            $i = 1;
            while ($joinCursor->valid()) {
                $phone = $joinCursor->current();
                $join['$in'][] = $phone['_id'];
                if ($i > $model->MAX_JOIN_PHONES) {
                    break;
                }
                $joinCursor->next();
                $i++;
            }
            $conditions = FilterBehavior::replaceJoin($originalJoinQuery, $join);
            $result = $result + $model->{$callback}($conditions, $limit, $timeout);
            $joinCursor->next();
        }
        return $result;
    }


}