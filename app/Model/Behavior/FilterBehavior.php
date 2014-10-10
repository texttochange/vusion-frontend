<?php
App::uses('VusionConst', 'Lib');
App::uses('VusionException', 'Lib');


class FilterBehavior extends ModelBehavior {

   
	public function setup($model, $settings = array()) 
	{
		if (!isset($model->filterFields)) {
			throw new Exception("Model %s is missing filters definition" % $model->name);
		}
    }


    public function databaseSupportJoin(){
        return true;
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
                if (!$model->databaseSupportJoin() && $model->isJoin($filterParam)) {
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
            
            if ($filters['filter_operator'] == "all") {
                if (count($conditions) == 0) {
                    $conditions = $condition;
                } elseif (!isset($conditions['AND'])) {
                    $conditions = array('AND' => array($conditions, $condition));
                } else {
                    array_push($conditions['AND'], $condition);
                }
            }  elseif ($filters['filter_operator'] == "any") {
                if (count($conditions) == 0) {
                    $conditions = $condition;
                } elseif (!isset($conditions['OR'])) {
                    $conditions = array('OR' => array($conditions, $condition));
                } else {
                    array_push($conditions['OR'], $condition);
                }
            }
        }
        return $conditions;

    }

    public function findAllSafeJoin($model, $state, $query, $results=array()) 
    {
        throw new VusionException('SQL Database support Join');
    }

    public function countSafeJoin($model, $callback, $conditions = true, $limit = null, $timeout = 30000) 
    {
        throw new VusionException('SQL Database support Join');
    }


    public function mergeFilterConditions($model, $defaultConditions, $filterConditions)
    {
        if ($defaultConditions === array()) {
            return $filterConditions;
        } elseif ($filterConditions === array()) {
            return $defaultConditions;
        } else {
            return array('AND' => array($defaultConditions, $filterConditions));
        }
    }


}