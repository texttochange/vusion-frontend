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
    
    function getModelVersion()
    {
        return '1';
    }
   
    function getRequiredFields($objectType=null)
    {
        return array(
            'name',
            'type-template',
            'template'
            );
     }

    public $typeTemplates = array(
        'open-question' => 'Open question',
        'closed-question' => 'Closed question',
        'unmatching-answer' => 'Unmatching Answer',
        'unmatching-keyword' => 'Unmatching Keyword'
        );

    public $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please give a name to the template.'
                ),
            'isReallyUnique' => array(
                'rule' => 'isReallyUnique',
                'message' => 'This name is already used by another template, choose another one.',
                'require' => true
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
                ),
            'hasAllTemplateKeyword' => array(
                'rule' => 'hasAllTemplateKeyword'
                ),
            ),
        );

    public function isReallyUnique($check) {
         if ($this->id) {
            $conditions = array('id'=>array('$ne'=> $this->id),'name' => '/^'.$check['name'].'$/i');
        } else {
            $conditions = array('name' => new MongoRegex('/^'.$check['name'].'$/i'));
        }
        $result = $this->find('count', array(
            'conditions' => $conditions
            ));
        return $result < 1;       
    }

    public function beforeValidate()
    {
        parent::beforeValidate();
        return true;
    }

    public function getTemplateOptions($type)
    {
        $templates = $this->find('all', array('conditions' => array('Template.type-template' => $type)));
        $options = array();
        foreach ($templates as $template) {
            $options[$template['Template']['_id']] = $template['Template']['name'];
        }
        return $options;
    }

    public function hasAllTemplateKeyword($check)
    {

        if ($this->data['Template']['type-template'] == 'open-question'
            or $this->data['Template']['type-template'] == 'closed-question') {
            if (!preg_match('/QUESTION/', $check['template'])) 
                return __("Please use 'QUESTION' in the template.");
            
            if (!preg_match('/SHORTCODE/', $check['template'])) 
                return __("Please use 'SHORTCODE' in the template.");
            
            if (!preg_match('/KEYWORD/', $check['template'])) 
                return __("Please use 'KEYWORD' in the template.");
        
            if ($this->data['Template']['type-template'] == 'open-question') {
                if (!preg_match('/ANSWER/', $check['template']))
                    return __("Please use 'ANSWER' in the template.");
            } elseif ($this->data['Template']['type-template'] == 'closed-question') {
                if (!preg_match('/ANSWERS/', $check['template']))
                    return __("Please use 'ANSWERS' in the template.");
            }
        } else {
            /*
            if ($this->data['Template']['type-template'] == 'unmatching-answer') {
                if (!preg_match('/ANSWER/', $check['template'])) 
                    return __("Please use 'ANSWER' in the template.");
            }
            if ($this->data['Template']['type-template'] == 'unmatching-keyword') {
                if (!preg_match('/KEYWORD/', $check['template'])) 
                    return __("Please use 'KEYWORD' in the template.");
            }*/
        }

        return true;
    }


}    
