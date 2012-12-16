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

    var $specific = true;
    var $name     = 'Dialogue';
    
    var $AUTOENROLLMENT_VALUES = array('none', 'all');

    function getModelVersion()
    {
        return '1';
    }


    function getRequiredFields($objectType=null)
    {
        return array(
            'name',
            'dialogue-id',
            'auto-enrollment',
            'interactions',
            'activated',
            );
    }
    

    var $findMethods = array(
        'draft' => true,
        'first' => true,
        'count' => true,
        );


    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        
        $this->DialogueHelper = new DialogueHelper();
        $this->Schedule = new Schedule($id, $table, $ds);
    }


    protected function _findDraft($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions']['Dialogue.activated'] = 0;
            $query['conditions']['Dialogue.dialogue-id'] = $query['dialogue-id'];
            return $query;
        }
        return $results;
    }    


    public function beforeValidate()
    {
        try {
            parent::beforeValidate();
    
            if (!isset($this->data['Dialogue']['activated'])) {
                $this->data['Dialogue']['activated'] = 0;
            } else {
                $this->data['Dialogue']['activated'] = intval( $this->data['Dialogue']['activated']);
            }
    
            if (!isset($this->data['Dialogue']['dialogue-id'])) {
                $this->data['Dialogue']['dialogue-id'] = uniqid();
            }   
    
            if (!in_array($this->data['Dialogue']['auto-enrollment'], $this->AUTOENROLLMENT_VALUES)) {
                $errorValue = $this->data['Dialogue']['auto-enrollment'];
                throw new FieldValueIncorrect("Auto Enrollment cannot be $errorValue");
            }
    
            $interactionModel = new Interaction();
    
            if (isset($this->data['Dialogue']['interactions'])) {
                foreach ($this->data['Dialogue']['interactions'] as &$interaction) {
                    $interaction = $interactionModel->beforeValidate($interaction);
                }
            } else {
                $this->data['Dialogue']['interactions'] = array();
            }
    
            $this->data['Dialogue'] = $this->DialogueHelper->objectToArray($this->data['Dialogue']);
    
            return $this->DialogueHelper->recurseScriptDateConverter($this->data['Dialogue']);
        } catch (Exception $e) {
            $this->validationErrors['dialogue'] = $e->getMessage();
            return false;
        }
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
                 $interactionIds[] = $interaction['interaction-id'];
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

    public function getActiveDialogues($options=null)
    {
        $dialogueQuery = array(
            'key' => array(
                'dialogue-id' => true,
                ),
            'initial' => array('Dialogue' => 0),
            'reduce' => 'function(obj, prev){
                if (obj.activated==1 && (prev.Dialogue==0 || prev.Dialogue.modified <= obj.modified)) 
                    prev.Dialogue = obj;
                }',
            'options'=> $options
            );
        $dialogues = $this->getDataSource()->group($this, $dialogueQuery);
        return array_filter(
            $dialogues['retval'],
            array($this, "_filterDraft")
            );
    }


    public function getActiveInteractions()
    {
        $activeInteractions = array();
        $activeDialogues    = $this->getActiveDialogues();
        foreach ($activeDialogues as $activeDialogue) {
            if (isset($activeDialogue['Dialogue']['interactions']))
                $activeInteractions = array_merge($activeInteractions, $activeDialogue['Dialogue']['interactions']);
        }
        return $activeInteractions;
    }


    public function getDialoguesInteractionsContent()
    {
        $content = array();
        $dialogues = $this->getActiveDialogues();
        foreach($dialogues as $dialogue) {
            $interactionContent = array();
            foreach($dialogue['Dialogue']['interactions'] as $interaction) {
                $interactionContent[$interaction['interaction-id']] = $interaction['content'];
            }
            $content[$dialogue['dialogue-id']] = array(
                'name'=>$dialogue['Dialogue']['name'],
                'interactions'=> $interactionContent);
        }
        return $content;
    }


    public function getDialogues()
    {
        $dialogueQuery = array(
            'key' => array(
                'dialogue-id' => true,
                ),
            'initial' => array('Dialogue' => 0),
            'reduce' => 'function(obj, prev){
                if (prev.Dialogue==0 || prev.Dialogue.modified <= obj.modified) 
                    prev.Dialogue = obj;
                }',
            );
        $dialogues = $this->getDataSource()->group($this, $dialogueQuery);
        return $dialogues['retval'];
    }


    public function getActiveAndDraft()
    {
        $dialogueQuery = array(
            'key' => array(
                'dialogue-id' => true,
                ),
            'initial' => array('Active' => 0, 'Draft' => 0),
            'reduce' => 'function(obj, prev){
                if (obj.activated==1 && (!prev.Active || prev.Active.modified <= obj.modified)) 
                    prev.Active = obj;
                else if (!obj.activated)
                    prev.Draft = obj;
                }',
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


    public function saveDialogue($dialogue)
    {
        $dialogue = $this->DialogueHelper->objectToArray($dialogue);

         if (!isset($dialogue['Dialogue']['dialogue-id'])) { 
            $this->create();
            return $this->save($dialogue);
        }

        $draft = $this->find('draft', array('dialogue-id'=>$dialogue['Dialogue']['dialogue-id']) );
        $this->create();
        if ($draft) { 
            $this->id                    = $draft[0]['Dialogue']['_id'];
            $dialogue['Dialogue']['_id'] = $draft[0]['Dialogue']['_id'];
        } else { 
            unset($dialogue['Dialogue']['_id']);
            $dialogue['Dialogue']['activated'] = 0;
        }
        return $this->save($dialogue);
    }


    public function useKeyword($keyword)
    {
        foreach ($this->getActiveDialogues() as $activeDialogue) {
            $foundKeyword = $this->DialogueHelper->hasKeyword($activeDialogue, $keyword);
            if ($foundKeyword) {
                return $foundKeyword;
            }
        }
        return array();
    }


    public function getKeywords()
    {
        $keywords = array();
        foreach ($this->getActiveDialogues() as $activeDialogue) {
            $keywords = array_merge($keywords, $this->DialogueHelper->getKeywords($activeDialogue));
        }
        return $keywords;
    }


    public function getActiveDialogueUseKeyword($keyword)
    {
        foreach ($this->getActiveDialogues() as $activeDialogue) {
            $foundKeyword = $this->DialogueHelper->hasKeyword($activeDialogue, $keyword);
            if ($foundKeyword) {
                return $activeDialogue;
            }
        }
        return array();
    }


    public function deleteDialogue($dialogueId)
    {
        $this->Schedule->deleteAll(array('Schedule.dialogue-id'=>$dialogueId), false);
        return $this->deleteAll(array('Dialogue.dialogue-id'=>$dialogueId), false);
    }

}



