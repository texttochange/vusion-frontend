<?php
App::uses('Component', 'Controller');
App::uses('Program', 'Model');
App::uses('Dialogue', 'Model');
App::uses('Request', 'Model');
App::uses('ProgramSetting', 'Model');

class KeywordComponent extends Component {

    public $components = array('Session'); 

    public function __construct($collection, $settings = array())
    {
        parent::__construct($collection, $settings);

        $this->Program        = new Program();
    }


    public function validateProgramKeywords($programUrl, $shortCode) 
    {   
        $options              = array('database' => ($this->Session->read($programUrl."_db")));
        $this->Dialogue       = new Dialogue($options);
        $this->Request        = new Request($options);

        $keywords = $this->Dialogue->getKeywords();
        $keywordToValidates = array_merge($keywords, $this->Request->getKeywords());
         
        foreach($keywordToValidates as $keywordToValidate) {
    
            /**Is the keyword used by another program*/
            $programs = $this->Program->find(
                'all', 
                array('conditions'=> 
                    array('Program.url !='=> $programUrl)
                    )
                );
            foreach ($programs as $program) {
                $programSettingModel = new ProgramSetting(array('database'=>$program['Program']['database']));
                if ($programSettingModel->find('hasProgramSetting', array('key'=>'shortcode', 'value'=> $shortCode))) {
                    $dialogueModel = new Dialogue(array('database'=>$program['Program']['database']));
                    $foundKeyword = $dialogueModel->useKeyword($keywordToValidate);
                    if ($foundKeyword) {
                        return array(
                            'status'=>'fail', 
                            'message'=>__("'%s' already used by a dialogue of program '%s'.", $foundKeyword, $program['Program']['name'])
                            );
                    }
                    $requestModel = new Request(array('database'=>$program['Program']['database']));
                    $foundKeyword = $requestModel->find('keyword', array('keywords'=> $keywordToValidate));
                    if ($foundKeyword) {
                        return array(
                            'status'=>'fail', 
                            'message'=> __("'%s' already used by a request of program '%s'.", $foundKeyword, $program['Program']['name'])
                            );
                        
                    }
                }
            }
        }
        return array('status'=>'ok');
    }

}