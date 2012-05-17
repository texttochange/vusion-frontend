<?php
App::uses('AppController', 'Controller');
App::uses('Request', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Dialogue', 'Model');
App::uses('DialogueHelper', 'Helper');

class ProgramRequestsController extends AppController
{

    var $components = array('RequestHandler');
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('*');
        $this->RequestHandler->accepts('json');
        $this->RequestHandler->addInputType('json', array('json_decode'));
    }


    function constructClasses()
    {
        parent::constructClasses();
        $options              = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->Request        = new Request($options);
        $this->Dialogue       = new Dialogue($options);
        $this->ProgramSetting = new ProgramSetting($options);
    }


    public function index()
    {
        $this->set('requests', $this->Request->find('all'));
    }


    public function add()
    {
        $programUrl = $this->params['program'];

        if ($this->request->is('post')) {
            //print_r($saveData);
            $this->Request->create();
            if ($this->Request->save($this->request->data)) {
                $this->set(
                    'result', array(
                        'status' => 'ok',
                        'request-id' => $this->Request->id)
                    );
            }
        }
    }


    public function edit()
    {
        $programUrl = $this->params['program'];
        $id         = $this->params['id'];

        if ($this->request->is('post')) {
            $this->Request->create();
            $this->Request->id = $id;
            if ($this->Request->save($this->request->data)) {
                $this->set(
                    'result', array(
                        'status' => 'ok',
                        'request-id' => $this->Request->id)
                    );
            } else {
                $this->set(
                    'result', array(
                        'status' => 'fail'
                        )
                    );
            }
        } else {
            $this->Request->id = $id;
            if (!$this->Request->exists()) {
                throw new NotFoundException(__('Invalide Request') . $id);
            }
            $this->set('request', $this->Request->read(null, $id));
        }
    }


    public function delete()
    {
        $programUrl = $this->params['program']; 
        $id         = $this->params['id']; 
        
         $this->Request->id = $id;
        if (!$this->Request->exists()) {
            throw new NotFoundException(__('Invalid request') . $id);
        }
        if ($this->Request->delete()) {
            $this->Session->setFlash(
                __('The request has been deleted.'),
                'default',
                array('class'=>'message success')
            );
        } else {
            $this->Session->setFlash(__('The request has not been deleted.'));
        }
        $this->redirect(
            array(
                'program' => $programUrl,
                'action' => 'index'
                )
            );
    }

    public function validateKeyword()
    {
        $shortCode = $this->ProgramSetting->find('getProgramSetting', array('key'=>'shortcode'));
        if (!$shortCode) {
            $this->set('result', array(
                    'status'=>'fail', 
                    'message' => __('Program shortcode has not been defined, please go to program settings.')
                    ));
            return;
        }

        $DialogueHelper = new DialogueHelper();

        $keywordToValidate = $DialogueHelper->getRequestKeywordToValidate($this->request->data['keyword']);

        $dialogueUsingKeyword = $this->Dialogue->getActiveDialogueUseKeyword($keywordToValidate);
        if ($dialogueUsingKeyword) {
            $this->set(
                'result', array(
                    'status'=>'fail', 
                    'message'=> __("'%s' already used in same program in the '%s' dialogue.", $keywordToValidate, $dialogueUsingKeyword['Dialogue']['name'])
                ));
            return;
            }
        
        /**Is the keyword used by another program*/
        $programs = $this->Program->find(
            'all', 
            array('conditions'=> 
            array('Program.url !='=> $this->params['program'])
            )
        );
        foreach ($programs as $program) {
            $programSettingModel = new ProgramSetting(array('database'=>$program['Program']['database']));
            if ($programSettingModel->find('hasProgramSetting', array('key'=>'shortcode', 'value'=> $shortCode))) {
                $dialogueModel = new Dialogue(array('database'=>$program['Program']['database']));
                $foundKeyword = $dialogueModel->useKeyword($keywordToValidate);
                if ($foundKeyword) {
                    $this->set(
                        'result', array(
                            'status'=>'fail', 
                            'message'=> __("'%s' already used in a dialogue of program '%s'.", $foundKeyword, $program['Program']['name'])
                            )
                        );
                    return;
                }
                $requestModel = new Request(array('database'=>$program['Program']['database']));
                $foundKeyword = $requestModel->find('keyword', array('keywords'=> $keywordToValidate));
                if ($foundKeyword) {
                    $this->set(
                        'result', array(
                            'status'=>'fail', 
                            'message'=> __("'%s' already used in a request of program '%s'.", $foundKeyword, $program['Program']['name'])
                            )
                        );
                    return;
                }
            }
        }

        $this->set('result', array('status' => 'ok'));
    }
    

    public function getActiveDialogue()
    {

    }


}
