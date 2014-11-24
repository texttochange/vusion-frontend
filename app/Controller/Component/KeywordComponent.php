<?php
App::uses('Component', 'Controller');
App::uses('Program', 'Model');
App::uses('Dialogue', 'Model');
App::uses('Request', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('ProgramSpecificMongoModel', 'Model');


class KeywordComponent extends Component 
{

    var $components = array(
        'Session'); 


    public function __construct($collection, $settings = array())
    {
        parent::__construct($collection, $settings);    
        $this->Program = ClassRegistry::init('Program');
    }


    public function areProgramKeywordsUsedByOtherPrograms($programDb, $shortCode) 
    {   
        $Dialogue = ProgramSpecificMongoModel::init(
            'Dialogue', $programDb, true);
        $Request = ProgramSpecificMongoModel::init(
            'Request', $programDb, true);

        $dialogueKeywords = $Dialogue->getKeywords();
        $requestKeywords  = $Request->getKeywords(); 
        $keywordToValidates = array_merge($dialogueKeywords, $requestKeywords);

        return $this->areKeywordsUsedByOtherPrograms($programDb, $shortCode, $keywordToValidates);
    }


    //TODO Clarify $keyword Parameter, it can be keyphrases or keywords
    public function areUsedKeywords($programDb, $shortcode, $keywords, $excludeType=null, $excludeId=null)
    {
       $keyphrases = DialogueHelper::cleanKeyphrases($keywords);
       $keywords = DialogueHelper::cleanKeywords($keywords);
       $usedKeywords = array();
       $dialogueModel = ProgramSpecificMongoModel::init('Dialogue', $programDb, true);
       if ($excludeType == 'Dialogue') {
           $foundKeywords = $this->_getUsedKeywords($dialogueModel, $keywords, '', $excludeId);
       } else {
           $foundKeywords = $this->_getUsedKeywords($dialogueModel, $keywords);
       }
       $usedKeywords = $usedKeywords + $foundKeywords;
       $requestModel = ProgramSpecificMongoModel::init('Request', $programDb, true);
       if ($excludeType == 'Request') {
           // In case we compare request, we need to consider the keyphrase and not only the keyword
           $foundKeywords = $this->_getUsedKeyphrases($requestModel, $keyphrases, '', $excludeId);
       } else {
           $foundKeywords = $this->_getUsedKeywords($requestModel, $keywords);
       }
       $usedKeywords = $usedKeywords + $foundKeywords;
       $otherProgramFoundKeywords = $this->areKeywordsUsedByOtherPrograms($programDb, $shortcode, $keywords);       
       return $usedKeywords + $otherProgramFoundKeywords;
    }


    protected function _getUsedKeywords($model, $keywords, $programName='', $exclude=null) {
        $usedKeywords = array();
        $foundKeywords = $model->useKeyword($keywords, $exclude);
        if (!$foundKeywords) {
            return $usedKeywords;
        }
        foreach ($foundKeywords as $foundKeyword => $details) {
            $usedKeywords[$foundKeyword] = array_merge(
                $details, 
                array(
                    'program-db' => $model->databaseName,
                    'program-name' => $programName,
                    'by-type'=> $model->alias));
        }
        return $usedKeywords;
    }


    protected function _getUsedKeyphrases($model, $keyphrases, $programName='', $exclude=null) {
        $usedKeyphrases = array();
        $foundKeyphrases = $model->useKeyphrase($keyphrases, $exclude);
        if (!$foundKeyphrases) {
            return $usedKeyphrases;
        }
        foreach ($foundKeyphrases as $foundKeyphrase => $details) {
            $usedKeyphrases[$foundKeyphrase] = array_merge(
                $details, 
                array(
                    'program-db' => $model->databaseName,
                    'program-name' => $programName,
                    'by-type'=> $model->alias));
        }
        return $usedKeyphrases;
    }


    public function areKeywordsUsedByOtherPrograms($programDb, $shortCode, $keywords)
    {
        $usedKeywords = array();
        $programs = $this->Program->find(
            'all', array('conditions' => array(
                'Program.database !=' => $programDb,
                'Program.status != "archived"')));
        foreach ($programs as $program) {
            $usedKeywords = $this->areKeywordsUsedByOtherProgram($program, $shortCode, $keywords, $usedKeywords);
        }
        return $usedKeywords;
    }

    public function areKeywordsUsedByOtherProgram($program, $shortCode, $keywords, $usedKeywords)
    {
        $programDb = $program['Program']['database'];
        $programSettingModel = ProgramSpecificMongoModel::init('ProgramSetting', $programDb, true);
        if ($programSettingModel->find('hasProgramSetting', array('key'=>'shortcode', 'value'=> $shortCode))) {
            $dialogueModel = ProgramSpecificMongoModel::init('Dialogue', $programDb, true);
            $foundDialogueKeywords = $this->_getUsedKeywords($dialogueModel, $keywords, $program['Program']['name']);
            $usedKeywords = $usedKeywords + $foundDialogueKeywords;
            $requestModel = ProgramSpecificMongoModel::init('Request', $programDb, true);
            $foundRequestKeywords = $this->_getUsedKeywords($requestModel, $keywords, $program['Program']['name']);
            $usedKeywords = $usedKeywords + $foundRequestKeywords;
        }
        return $usedKeywords;
    }

    
    //For now only display the first validation error
    public function foundKeywordsToMessage($programDb, $foundKeywords)
    {
        if ($foundKeywords === array()) {
            return null;
        }
        $keyword = key($foundKeywords);
        return DialogueHelper::foundKeywordsToMessage($programDb, $keyword, $foundKeywords[$keyword]);
    }


}