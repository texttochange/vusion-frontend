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


    public function areKeywordsUsedByOtherPrograms($programDb, $shortCode, $keywords)
    {
        $usedKeywords = array();
        foreach($keywords as $keywordToValidate) {            
            $programs = $this->Program->find(
                'all', 
                array('conditions'=> 
                    array('Program.database !=' => $programDb)
                    )
                );
            foreach ($programs as $program) {
                $programSettingModel = new ProgramSetting(array(
                    'database' => $program['Program']['database']));
                if ($programSettingModel->find('hasProgramSetting', array('key'=>'shortcode', 'value'=> $shortCode))) {
                    $dialogueModel = new Dialogue(array(
                        'database' => $program['Program']['database']));
                    $foundKeyword = $dialogueModel->useKeyword($keywordToValidate);
                    if ($foundKeyword) {
                        $usedKeywords[$foundKeyword] = array(
                            'programName' => $program['Program']['name'],
                            'type' => 'dialogue');
                    }
                    $requestModel = new Request(array('database' => $program['Program']['database']));
                    $foundKeyword = $requestModel->find('keyword', array('keywords' => $keywordToValidate));
                    if ($foundKeyword) {
                        $usedKeywords[$foundKeyword] = array(
                            'programName' => $program['Program']['name'],
                            'type' => 'request');
                    }
                }
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
            return __("'%s' already used by a %s of program '%s'.", $keyword, $errors['type'], $errors['programName']);
        }
    }

}