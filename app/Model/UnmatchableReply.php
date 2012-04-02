<?php
App::uses('MongoModel', 'Model');
/**
 * UnmatchableReply Model
 *
 */
class UnmatchableReply extends MongoModel
{

    var $specific    = true;
    var $name        = 'UnmatchableReply';
    var $useDbConfig = 'mongo';
    var $useTable    = 'unmatchable_reply';
    
}
