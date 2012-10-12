<?php
App::uses('MongoModel', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('Schedule', 'Model');
App::uses('MissingField', 'Lib');

class Dialogue extends MongoModel
{

    var $specific = true;
    var $name     = 'Dialogue';

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
        parent::beforeValidate();

        if (!isset($this->data['Dialogue']['activated'])) {
            $this->data['Dialogue']['activated'] = 0;
        } else {
            $this->data['Dialogue']['activated'] = intval( $this->data['Dialogue']['activated']);
        }

        if (!isset($this->data['Dialogue']['dialogue-id'])) {
            $this->data['Dialogue']['dialogue-id'] = uniqid();
        }   

        $interactionModel = new Interaction();

        if (isset($this->data['Dialogue']['interactions'])) {
            foreach ($this->data['Dialogue']['interactions'] as &$interaction) {
                $interaction = $interactionModel->beforeValidate($interaction);
                /*
                if (!isset($interaction['interaction-id']) || $interaction['interaction-id']=="") {
                    $interaction['interaction-id'] = uniqid();  
                }   
                if (!isset($interaction['activated']) or $interaction['activated']==null) {
                    $interaction['activated'] = 0;
                }
                if (!isset($interaction['type-interaction'])) {
                    $interaction['type-interaction'] = null;
                }
                if (!isset($interaction['type-schedule'])) {
                    $interaction['type-schedule'] = null;
                }
                # do something in here.
                if ((isset($interaction['type-schedule']) and $interaction['type-schedule'] == 'offset-days') 
                    and (!isset($interaction['days']) or $interaction['days'] == ""))
                    $interaction['days'] = '0';
                */
            }
        } else {
            $this->data['Dialogue']['interactions'] = array();
        }

        $this->data['Dialogue'] = $this->DialogueHelper->objectToArray($this->data['Dialogue']);

        return $this->DialogueHelper->recurseScriptDateConverter($this->data['Dialogue']);
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
        return $this->save($dialogue);
    }


    public function makeDraftActive($dialogueId)
    {
        $draft = $this->find('draft', array('dialogue-id'=>$dialogueId));
        if ($draft) {
            return $this->makeActive($draft[0]['Dialogue']['_id'].'');
        }
        return false;
    }


    public function getActiveDialogues($options=null)
    {
        $dialogueQuery = array(
            'key' => array(
                'dialogue-id' => true,
                ),
            'initial' => array('Dialogue' => 0),
            'reduce' => 'function(obj, prev){
                if (obj.activated && (prev.Dialogue==0 || prev.Dialogue.modified <= obj.modified)) 
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
                if (obj.activated && (!prev.Active || prev.Active.modified <= obj.modified)) 
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

class Interaction
{
    var $modelName = 'interaction';
    var $modelVersion = '1'; 

    var $payload = array();

    var $fields = array(
        'interaction-id',
        'type-schedule',
        'type-interaction',
        'activated');

    public function beforeValidate($interaction)
    {
        
        $SCHEDULE_TYPE = array(
            'fixed-time' => array('date-time' => function($v) {return true;}),
            'offset-days'=> array(
                'days' => function($v) { return ($v!=null);},
                'at-time'=> function($v) { return ($v!=null);}),
            'offset-time'=> array('minutes'=> function($v) { return ($v!=null);}),
            'offset-condition'=> array( 
                'offset-condition-interaction-id' => function($v) { return ($v!=null);}));
        
        $INTERACTION_TYPE = array(
            'announcement' => array('content' => function($v) {return ($v!=null);}),
            'question-answer'=> array(
                'content'=> function($v) {return ($v!=null);},
                'keyword'=> function($v) {return ($v!=null);},
                'set-use-template'=> function($v) {return true;},
                'type-question'=> function($v) {return ($v!=null);},
                'type-unmatching-feedback'=> function($v) {return ($v!=null);},
                'set-reminder'=> function($v) {return true;}),
            'question-answer-keyword'=> array(
                'content'=> function($v) {return ($v!=null);},
                'label-for-participant-profiling'=> function($v) {return ($v!=null);},
                'answer-keywords'=> function($v) {return ($v!=null);},
                'type-unmatching-feedback'=> function($v) {return ($v!=null);},
                'set-reminder'=> function($v) {return true;}));


       
        $interaction['model-name'] = $this->modelName;        
        $interaction['model-version'] = $this->modelVersion;

        foreach($this->fields as $field) {
            if (!isset($interaction[$field])) {
                if ($field=='interaction-id') {
                    $interaction['interaction-id'] = uniqid();  
                } elseif ($field=='activated') {
                    $interaction['activated'] = 0;
                }else {
                    throw new MissingField("$field is missing");
                }
            }
            foreach($SCHEDULE_TYPE[$interaction['type-schedule']] as $field => $check) {
                if (!call_user_func($check, $interaction[$field])){
                    throw new MissingField("$field is missing");
                }
            }
            foreach($SCHEDULE_TYPE[$interaction['type-schedule']] as $field => $check) {
                if (!call_user_func($check, $interaction[$field])){
                    throw new MissingField("$field is missing");
                }
            }
        }

        return $interaction;

    }

}
