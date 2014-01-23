<?php
App::uses('Component', 'Controller');
App::uses('Program', 'Model');
App::uses('Dialogue', 'Model');
App::uses('Request', 'Model');
App::uses('ProgramSetting', 'Model');


class KeywordComponent extends Component 
{

    public $components = array('Session'); 


    public function __construct($collection, $settings = array())
    {
        parent::__construct($collection, $settings);
        
        $this->Program = new Program();
    }


    public function areProgramKeywordsUsedByOtherPrograms($programDb, $shortCode) 
    {   
        $options              = array('database' => ($programDb));
        $this->Dialogue       = new Dialogue($options);
        $this->Request        = new Request($options);

        $keywords = $this->Dialogue->getKeywords();
        $keywordToValidates = array_merge($keywords, $this->Request->getKeywords());

        return $this->areKeywordsUsedByOtherPrograms($programDb, $shortCode, $keywordToValidates);
    }


    public function areUsedKeywords($programDb, $shortcode, $keywords)
    {
       $usedKeywords = array();
       //Get the Request keyword or the Dialogue
       $dialogueModel = new Dialogue(array('database' => $programDb));
       $usedKeywords = array_merge($usedKeywords, $this->_getUsedKeywords($dialogueModel, $keywords));
       $requestModel = new Request(array('database' => $programDb));
       $usedKeywords = array_merge($usedKeywords, $this->_getUsedKeywords($requestModel, $keywords));
       $otherProgramFoundKeywords = $this->areKeywordsUsedByOtherPrograms($programDb, $shortcode, $keywords);
       return array_merge($usedKeywords, $otherProgramFoundKeywords);
    }


    protected function _getUsedKeywords($model, $keywords, $programName='') {
        $usedKeywords = array();
        $foundKeywords = $model->useKeyword($keywords);
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

    // Refactor to loop program then loop on keywords
    public function areKeywordsUsedByOtherPrograms($programDb, $shortCode, $keywords)
    {
        $usedKeywords = array();
        $programs = $this->Program->find(
            'all', array('conditions' => array('Program.database !=' => $programDb)));
        foreach ($programs as $program) {
            $programSettingModel = new ProgramSetting(array('database' => $program['Program']['database']));
            if ($programSettingModel->find('hasProgramSetting', array('key'=>'shortcode', 'value'=> $shortCode))) {
                $dialogueModel = new Dialogue(array('database' => $program['Program']['database']));
                $foundDialogueKeywords = $this->_getUsedKeywords($dialogueModel, $keywords, $program['Program']['name']);
                $usedKeywords = array_merge($usedKeywords, $foundDialogueKeywords);
                $requestModel = new Request(array('database' => $program['Program']['database']));
                $foundRequestKeywords = $this->_getUsedKeywords($requestModel, $keywords, $program['Program']['name']);
                $usedKeywords = array_merge($usedKeywords, $foundRequestKeywords);
            }
        }
        return $usedKeywords;
    }


    //For now only display the first validation error
    public function validationToMessage($validations)
    {
        if ($validations === array()) {
            return null;
        }
        foreach($validations as $keyword => $errors) {
            return __("'%s' already used by a %s of program '%s'.", $keyword, $errors['by-type'], $errors['program-name']);
        }
    }

}