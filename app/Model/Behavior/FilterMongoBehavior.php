<?php
App::uses('FilterBehavior', 'Model/Behavior');


class FilterMongoBehavior extends FilterBehavior 
{

    var $joinCursor = null;
    var $originalJoinQuery = null;
    
    public function setup(Model $model, $config = array()) 
    {
        parent::setup($model, $config);
        $model->MAX_JOIN_PHONES = VusionConst::MAX_JOIN_PHONES;
        $model->joinCursor = null;
        $model->originalJoinQuery = null;
    }

    public function databaseSupportJoin()
    {
        return false;
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


    public function findAllSafeJoin($model, $state, $query, $results=array()) 
    {    
        if ($state === 'before') {
            if (!isset($query['limit'])) {
                throw new VusionException('FindAllSafe has to be used with limit');
            }
            if (FilterMongoBehavior::hasJoin($query) || $model->joinCursor != null) {
                if ($cursor = FilterMongoBehavior::hasJoin($query)) {
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
                $query = FilterMongoBehavior::replaceJoin($model->originalJoinQuery, $join);
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


    //Works with 1 join only
    public static function hasJoin($conditions) 
    {
        if (!is_array($conditions)) {
            return false;
        }
        foreach ($conditions as $key => $value) {
            if ($key === '$join') {
                return $conditions[$key];
            }
            if (is_array($value)) {
                if ($join = FilterMongoBehavior::hasJoin($value)) {
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
                $conditions[$key] = FilterMongoBehavior::replaceJoin($value, $replaceWith);
           }
        }
        return $conditions;
    }


    public function countSafeJoin($model, $callback, $conditions = true, $limit = null, $timeout = 30000) 
    {
        if (!FilterMongoBehavior::hasJoin($conditions)) {
            return $model->{$callback}($conditions, $limit, $timeout);
        } 
        $result = 0;
        $joinCursor = FilterMongoBehavior::hasJoin($conditions);
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
            $conditions = FilterMongoBehavior::replaceJoin($originalJoinQuery, $join);
            $result = $result + $model->{$callback}($conditions, $limit, $timeout);
            $joinCursor->next();
        }
        return $result;
    }


    public function mergeFilterConditions($model, $defaultConditions, $filterConditions)
    {
        if ($defaultConditions === array()) {
            return $filterConditions;
        } elseif ($filterConditions === array()) {
            return $defaultConditions;
        } else {
            return array('$and' => array($defaultConditions, $filterConditions));
        }
    }


}