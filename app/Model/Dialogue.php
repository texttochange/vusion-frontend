<?php
App::uses('MongoModel', 'Model');
App::uses('ScriptHelper', 'Lib');

class Dialogue extends MongoModel
{

    var $specific = true;
    var $name     = 'Dialogue';

    var $findMethods = array(
        'draft' => true,
        'first' => true,
        'count' => true,
        );

    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->scriptHelper = new ScriptHelper();
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
        if (!isset($this->data['Dialogue']['dialogue'])) {
            return false;
        }

        if (!isset($this->data['Dialogue']['activated'])) {
            $this->data['Dialogue']['activated'] = 0;
        }

        if (!isset($this->data['Dialogue']['dialogue-id'])) {
            $this->data['Dialogue']['dialogue-id'] = uniqid();
        }   

        $this->data['Dialogue']['dialogue'] = $this->scriptHelper->objectToArray($this->data['Dialogue']['dialogue']);

        return $this->scriptHelper->recurseScriptDateConverter($this->data['Dialogue']['dialogue']);
    }


    public function makeDraftActive($dialogueId)
    {
        $draft = $this->find('draft', array('dialogue-id'=>$dialogueId));
        if ($draft) {
            $draft[0]['Dialogue']['activated'] = 1;
            $this->create();
            $this->id = $draft[0]['Dialogue']['_id'];
            $this->save($draft[0]['Dialogue']);
            return $draft[0];
        }
        return false;
    }


    public function getActiveDialogues()
    {
        $dialogueQuery = array(
            'key' => array(
                'dialogue-id' => true,
                ),
            'initial' => array('Dialogue' => 0),
            'reduce' => 'function(obj, prev){
                if (obj.activated && prev.Dialogue==0) 
                    prev.Dialogue = obj;
                else if (obj.activated && prev.Dialogue.modified < obj.modified) {
                    prev.Dialogue = obj;
                }}',
            );
        $dialogues = $this->getDataSource()->group($this, $dialogueQuery);
        return array_filter(
            $dialogues['retval'],
            array($this, "_filterDraft")
            );
    }


    protected function _filterDraft($dialogue)
    {
        return ($dialogue['Dialogue']!=0);
    }


    public function saveDialogue($dialogue)
    {
        if (!isset($dialogue['Dialogue']['dialogue-id']))
            return $this->save($dialogue);

        $draft = $this->find('draft', array('dialogue-id'=>$dialogue['Dialogue']['dialogue-id']) );
        $this->create();
        if ($draft) {
            $this->id                    = $draft[0]['Dialogue']['_id'];
            $dialogue['Dialogue']['_id'] = $draft[0]['Dialogue']['_id'];
        }
        return $this->save($dialogue);
    }


}
