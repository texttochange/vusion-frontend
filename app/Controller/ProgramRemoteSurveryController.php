<?php
App::uses('AppController', 'Controller');
App::uses('ProgramSpecificMongoModel', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('UnmatchableReply', 'Model');
App::uses('Dialogue', 'Model');
App::uses('Request', 'Model');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('ShortCode', 'Model');
App::uses('CreditLog', 'Model');
App::uses('ProgramSpecificMongoModel', 'Model');


class ProgramRemoteSurveryController extends AppController
{
    var $uses = array(
        'Program', 
        'Group',
        'ShortCode',
        'CreditLog');
    
    
    
    public function addSurvery()
    {
        
    }
    
    
}
