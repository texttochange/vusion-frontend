<?php
App::uses('MongoModel', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('Schedule', 'Model');
App::uses('MissingField', 'Lib');
App::uses('FieldValueIncorrect', 'Lib');
App::uses('VusionException', 'Lib');
App::uses('Interaction', 'Model');

class Dialogue extends MongoModel
{
    
    var $specific     = true;
    var $name         = 'Dialogue';
    var $usedKeywords = array();
    
    function getModelVersion()
    {
        return '2';
    }
    
    
    function getRequiredFields($objectType=null)
    {
        return array(
            'name',
            'dialogue-id',
            'auto-enrollment',
            'interactions',
            'activated',
            'set-prioritized'
            );
    }
    
    
    public $validate = array(
        'dialogue-id' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'A dialogue-id field cannot be empty.'
                ),
            ),
        'auto-enrollment' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'A auto-enrollment field cannot be empty.'
                ),
            'validValue' => array(
                'rule' => array('inList', array('none', 'all')),
                'message' => 'The auto-enrollment value is not valid.'
                ),
            ),
        'interactions' => array(
            'validInteractions' => array(
                'rule' => 'validateInteractions',
                'message' => 'noMessage'
                )
            ),
        'activated' => array(
            'validValue' => array(
                'rule' => array('inlist', array(0, 1)),
                'message' => 'The activated field value can only be 0 or 1.'
                ),
            ),
        'set-prioritized' => array(
            'validValue' => array(
                'rule' => array('inList', array(null, 'prioritized')),
                'message' => 'The prioritized field value can only be null or prioritized.'
                ), 
            ),
        'name' => array(
        'uniqueDialogueName' => array(
            'rule' => 'uniqueDialogueName',
            'message' => 'This Dialogue Name already exists. Please choose another.'
            ),
        ),
        );
    
    
    
    public function validateInteractions($check) 
    {
        $index = 0;
        $this->Interaction->setUsedKeywords($this->usedKeywords);
        foreach ($check['interactions'] as $interaction) {
            $this->Interaction->set($interaction);
            if (!$this->Interaction->validates()) {
                if (!isset($this->validationErrors['interactions'])) {
                    $this->validationErrors['interactions'] = array();
                }
                if (!isset($this->validationErrors['interactions'][$index])) {
                    $this->validationErrors['interactions'][$index] = array();
                }
                $this->validationErrors['interactions'][$index] = $this->Interaction->validationErrors;
            }
            $index++;
        }
        if (isset($this->validationErrors['interactions'])) {
            return false;
        }
        return true;
    }
    
    
    var $findMethods = array(
        'draft' => true,
        'first' => true,
        'count' => true,
        ); 
    
    
    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        
        $this->Schedule    = new Schedule($id, $table, $ds);
        $this->Interaction = new Interaction($id['database']);
    }
    
    
    protected function _findDraft($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions']['Dialogue.activated']   = 0;
            $query['conditions']['Dialogue.dialogue-id'] = $query['dialogue-id'];
            return $query;
        }
        return $results;
    }
    
    
    public function beforeValidate()
    {
        parent::beforeValidate();
        
       //Need to convert all dates
        $this->data['Dialogue'] = DialogueHelper::objectToArray($this->data['Dialogue']);
        DialogueHelper::recurseScriptDateConverter($this->data['Dialogue']);
        
        //Set default value if key not present
        $this->_setDefault('activated', 0);
        $this->_setDefault('dialogue-id', uniqid());
        $this->_setDefault('interactions', array());
        $this->_setDefault('set-prioritized', null);
        
       //Need to make sure the value is an int
        $this->data['Dialogue']['activated'] = intval($this->data['Dialogue']['activated']);
        
        //Run before validate for all interactions
        $this->_beforeValidateInteractions();
        
        return true;        
    }
    
    
    public function _beforeValidateInteractions()
    {
        foreach ($this->data['Dialogue']['interactions'] as &$interaction) {
            if (isset($this->data['Dialogue']['set-prioritized']) && $this->data['Dialogue']['set-prioritized']) {
                $interaction['prioritized'] = $this->data['Dialogue']['set-prioritized'];
            }
            $this->Interaction->set($interaction);
            $this->Interaction->beforeValidate();
            $interaction = $this->Interaction->getCurrent();
        }
        return true;
    }
    
    
    public function makeActive($objectId)
    {      
        $this->id = $objectId;
        if (!$this->exists())
            return false;
        $dialogue = $this->read(null, $objectId);
        $dialogue['Dialogue']['activated'] = 1;
        if (isset($dialogue['Dialogue']['interactions'])) {
            $interactionIds = array();
            foreach ($dialogue['Dialogue']['interactions'] as &$interaction) {
                $interactionIds[]         = $interaction['interaction-id'];
                $interaction['activated'] = 1;
            }
            //Delete all interaction that have been deleted on the UI
            $this->Schedule->deleteAll(
                array(
                    'Schedule.dialogue-id'=>$dialogue['Dialogue']['dialogue-id'],
                    'Schedule.interaction-id'=>array('$nin'=>$interactionIds)),
                false);
        }
        // we make sure no other version is activated
        $result = $this->save($dialogue);
        $this->updateAll(
            array('activated' => 2),
            array('activated' => 1, 
                'dialogue-id' => $result['Dialogue']['dialogue-id'],
                '_id' => array('$ne' => new MongoId($result['Dialogue']['_id']))));
        return $result;
    }
    
    
    public function makeDraftActive($dialogueId)
    {
        $draft = $this->find('draft', array('dialogue-id'=>$dialogueId));
        if ($draft) {
            return $this->makeActive($draft[0]['Dialogue']['_id'].'');
        }
        return false;
    }
    
    public function getActiveDialogue($dialogueId) {
        return $this->find('first', array(
            'conditions'=>array(
                'activated' => 1,
                'dialogue-id' => $dialogueId)));
    }
    
    public function getActiveDialogues($moreConditions=null)
    {
        $conditions = array('conditions' => array('activated' => 1));
        if (isset($moreConditions)) {
            $conditions['conditions'] = array_merge($conditions['conditions'], $moreConditions);
        }
        return $this->find('all', $conditions); 
    }
    
    
    public function getActiveInteractions()
    {
        $activeInteractions = array();
        $activeDialogues    = $this->getActiveDialogues();
        foreach ($activeDialogues as $activeDialogue) {
            if (isset($activeDialogue['Dialogue']['interactions'])) {
                foreach ($activeDialogue['Dialogue']['interactions'] as $interaction) {
                    $activeInteractions[$interaction['interaction-id']] = $interaction;
                }
            }
        }
        return $activeInteractions;
    }
    
    
    public function getDialoguesInteractionsContent()
    {
        $content   = array();
        $dialogues = $this->getActiveDialogues();
        foreach ($dialogues as $dialogue) {
            $interactionContent = array();
            foreach ($dialogue['Dialogue']['interactions'] as $interaction) {
                $interactionContent[$interaction['interaction-id']] = $interaction['content'];
            }
            $content[$dialogue['Dialogue']['dialogue-id']] = array(
                'name'=>$dialogue['Dialogue']['name'],
                'interactions'=> $interactionContent);
        }
        return $content;
    }
    
    
    public function getActiveAndDraft()
    {
        $dialogueQuery = array(
            'key' => array(
                'dialogue-id' => true,
                ),
            'initial' => array('Active' => 0, 'Draft' => 0),
            'reduce' => 'function(obj, prev){
            if (obj.activated==1) { 
            prev.Active = obj;
            } else if (obj.activated==0) {
            prev.Draft = obj;
            }
            }',
            'options' => array(
                'condition' => array('activated'=> array('$in' => array(0,1))))
            );
        $dialogues = $this->getDataSource()->group($this, $dialogueQuery);
        uasort($dialogues['retval'], array($this, '_compareDialogue'));
        return $dialogues['retval'];
    }
    
    
    private function _compareDialogue($a, $b)
    {
        if ($a['Active'] && $b['Active'])
            return ($a['Active']['modified']<$b['Active']['modified']) ? 1 : -1;
        if ($a['Active'] && !$b['Active'])
            return -1;
        if (!$a['Active'] && $b['Active'])
            return 1;
    }
    
    
    protected function _filterDraft($dialogue)
    {
        return ($dialogue['Dialogue']!=0);
    }
    
    
    public function saveDialogue($dialogue, $usedKeywords = array())
    {
        $this->usedKeywords = $usedKeywords;
        
        if (!isset($dialogue['Dialogue']['dialogue-id'])) { 
            $this->create();
            return $this->save($dialogue);
        }
        
        $draft = $this->find('draft', array('dialogue-id'=>$dialogue['Dialogue']['dialogue-id']) );
        $this->create();
        if ($draft) { 
            $this->id                          = $draft[0]['Dialogue']['_id'];
            $dialogue['Dialogue']['_id']       = $draft[0]['Dialogue']['_id'];
            $dialogue['Dialogue']['activated'] = 0;
        } else { 
            unset($dialogue['Dialogue']['_id']);
            $dialogue['Dialogue']['activated'] = 0;
        }
        return $this->save($dialogue);
    }
    
    
    static public function hasDialogueKeywords($dialogue, $keywords)
    {
        if (isset($dialogue['Dialogue'])) {
            $dialogue = $dialogue['Dialogue'];
        }
        
        if (!isset($dialogue['interactions'])) {
            return array();
        }
        
        $foundKeywords = array();
        foreach ($dialogue['interactions'] as $interaction) {
            $foundKeywords = array_merge($foundKeywords, Interaction::hasInteractionKeywords($interaction, $keywords));
        }
        return $foundKeywords;
    }
    
    
    static public function getDialogueKeywords($dialogue)
    {
        if (isset($dialogue['Dialogue'])) {
            $dialogue = $dialogue['Dialogue'];
        }
        
        if (!isset($dialogue['interactions'])) {
            return array();
        }
        
        $foundKeywords = array();
        foreach ($dialogue['interactions'] as $interaction) {
            $foundKeywords = array_merge($foundKeywords, Interaction::getInteractionKeywords($interaction));
        }
        return $foundKeywords;
    }
    
    
    static public function getDialogueId($dialogue)
    {
        if (isset($dialogue['Dialogue'])) {
            $dialogue = $dialogue['Dialogue'];
        }
        
        if (!isset($dialogue['dialogue-id'])) {
            return null;
        }
        
        return $dialogue['dialogue-id'];
    }
    
    
    public function useKeyword($keywords, $excludeDialogue=null)
    {
        $params = array();
        if ($excludeDialogue!=null) {
            $params = array('dialogue-id' => array('$ne' => $excludeDialogue));
        }
        $keywords = DialogueHelper::cleanKeywords($keywords);
        $usedKeywords = array();
        foreach ($this->getActiveDialogues($params) as $activeDialogue) {
            $foundKeywords = Dialogue::hasDialogueKeywords($activeDialogue, $keywords);
            $foundKeywords = array_flip($foundKeywords);
            foreach ($foundKeywords as $key => $value) {
                $foundKeywords[$key] = array(
                    'dialogue-id' => $activeDialogue['Dialogue']['dialogue-id'],
                    'dialogue-name' => $activeDialogue['Dialogue']['name']);
            }
            $usedKeywords = array_merge($usedKeywords, $foundKeywords);
        }
        if ($usedKeywords === array()) {
            return false;
        }
        return $usedKeywords;
    }
    
    
    public function getKeywords()
    {
        $keywords = array();
        foreach ($this->getActiveDialogues() as $activeDialogue) {
            $keywords = array_merge($keywords, Dialogue::getDialogueKeywords($activeDialogue));
        }
        return $keywords;
    }
    
    
    public function deleteDialogue($dialogueId)
    {
        $this->Schedule->deleteAll(array('Schedule.dialogue-id'=>$dialogueId), false);
        return $this->deleteAll(array('Dialogue.dialogue-id'=>$dialogueId), false);
    }
    
    
    public function uniqueDialogueName($check)
    {   $dialogueId = $this->data['Dialogue']['dialogue-id'];
        return $this->isValidDialogueName($check['name'], $dialogueId);    
    }
    
    
    public function isValidDialogueName($name, $dialogueId = null)
    {
        if (isset($dialogueId)) {
            $conditions = array('name'=> $name, 'dialogue-id' => array('$ne'=> $dialogueId));
            $result     = $this->find('count', array('conditions' => $conditions));
            return $result == 0;    
        }
        
        $conditions = array('name' => $name);
        $result     = $this->find('count', array('conditions' => $conditions));
        return $result == 0;        
    }
    
    
}



