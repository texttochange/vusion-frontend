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
    
    public $typeTemplates = array(
        'open-question' => 'Open question',
        'closed-question' => 'Closed question'
        );

    public $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please give a name to the template.'
                )
            ),
        'type-template' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please select a template type.'
                )
            ),
        'template' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please write a template.'
                )
            ),
        );

    public function getTemplateOptions($type)
    {
        $templates = $this->find('all', array('conditions' => array('Template.type-template' => $type)));
        $option = array();
        foreach ($templates as $template) {
            $options[$template['Template']['_id']] = $template['Template']['name'];
        }
        return $options;
    }


}    
