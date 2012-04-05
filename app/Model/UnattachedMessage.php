<?php
App::uses('MongoModel', 'Model');
/**
 * UnattachedMessage Model
 *
 */
class UnattachedMessage extends MongoModel
{

    var $specific    = true;
    var $name        = 'UnattachedMessage';
    var $useDbConfig = 'mongo';
    var $useTable    = 'unattached_messages';
    
}
