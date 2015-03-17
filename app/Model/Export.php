<?php
App::uses('MongoModel', 'Model');
App::uses('VusionConst', 'Lib');


class Export extends MongoModel
{
	var $useTable = 'exports';

    function getModelVersion()
    {
        return '1';
    }

    function getRequiredFields($objectType=null)
    {
        return array(
            'timestamp',
            'database',
            'collection',
            'filters',
            'conditions',
            'order',
            'filters',
            'status',
            'failure-reason',
            'size',
            'file-full-name'
            );
    }

    public $validate = array(
        'timestamp' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The timestamp has to be set.'
                ),
            'validFormat' => array(
                'rule' => array('custom', VusionConst::DATE_TIME_REGEX),
                'message' => VusionConst::DATE_TIME_FAIL_MESSAGE
                )
            ),
        'database' => array(
            'isString' => array(
                'rule' => array('custom','/^[a-z0-9_]{3,}$/'),
                'message' => 'Database has to be a string of at least 3 characters.',
                ),
            ),
        'collection' => array(
            'validateValue' => array(
                'rule' => array('inList', array('history', 'participants', 'unmatchable_reply')),
                'message' => 'Export of this collection is not supported.',
                ),
            ),
        /*'filters' => array(),
        'order' => array(),
        'conditions' => array(),*/
        'status' => array(
        	'validValue' => array(
        		'rule' => array('inList', array(
		    		'queued',
		           	'processing',
		            'success',
		            'failed',
		            'no-space')),
        		'message' => 'The status is not valid.',
       			),
        	),
        /*'failure-reason' => array(
        	'requiredConditional' => array (
                'rule' => array('requiredConditionalFieldValue', 'status', 'failed'),
                'message' => 'Status failed required this field.',
                )
        	),*/
        'size' => array(
        	'isLong' => array(
                'rule' => array('numeric'),
                'message' => 'Size field has to be a integer.',
                ),
         	),
        'file-full-name' => array(
        	'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The database has to be set.'
                ),
            'validValue' => array(
                'rule' => array('custom', '/^(\/[a-zA-Z_\-\s0-9\.]+)+\.csv$/'),
                'message' => 'Editing table is not allowed.',
                ),
            ),
        );


	public function beforeValidate()
    {
        parent::beforeValidate();
    	
    	$now = new DateTime('now');
    	$this->_setDefault('timestamp', $now->format("Y-m-d\TH:i:s"));
    	$this->_setDefault('filters', '');
    	$this->_setDefault('conditions', '');
        $this->data['Export']['conditions'] = $this->_addSlashRegex('conditions', $this->data['Export']['conditions']);
    	$this->_setDefault('order', '');
    	$this->_setDefault('size', 0);
    	$this->_setDefault('status', 'queued');
    	return true;
    }


    public function beforeDelete()
    {
        $export = $this->read();
        $file = new File($export['Export']['file-full-name']);
        $file->delete();
        return true;
    }


    ## the \ on mongo regex are disappearing when saving in mongo
    public function _addSlashRegex($key, $conditions) 
    {
        if (is_string($conditions) && $key === '$regex') {
            return addcslashes($conditions, '+');
        }
        if (is_array($conditions)) {
            foreach($conditions as $key => $value) {
                $conditions[$key] = $this->_addSlashRegex($key, $value);
            }
            return $conditions;
        }
        return $conditions;
    }


}
