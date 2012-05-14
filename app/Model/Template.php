<?php
App::uses('MongoModel', 'Model');
/**
 * Template Model
 *
 */
class Template extends MongoModel
{

    var $specific    = true;
    var $name        = 'Template';
    var $useDbConfig = 'mongo';
    var $useTable    = 'templates';
    
}    
