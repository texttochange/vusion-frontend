<?php
App::uses('AppController', 'Controller');
App::uses('Request', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Dialogue', 'Model');
App::uses('DialogueHelper', 'Helper');
App::uses('Participant', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramRequestsController extends AppController
{

    var $components = array('RequestHandler', 'LocalizeUtils', 'Utils');
    var $uses = array('Request');
    

    function constructClasses()
    {
        parent::constructClasses();
    }
    

    public function beforeFilter()
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
        $this->RequestHandler->accepts('json');
        $this->RequestHandler->addInputType('json', array('json_decode'));
        $options              = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->Request        = new Request($options);
        $this->Dialogue       = new Dialogue($options);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->Participant    = new Participant($options);
        $this->_instanciateVumiRabbitMQ();
    }


    protected function _instanciateVumiRabbitMQ(){
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }



    public function index()
    {
        $this->set('requests', $this->paginate());
    }


    public function add()
    {
        $this->set('conditionalActionOptions', $this->_getConditionalActionOptions());

        $programUrl = $this->params['program'];

        if ($this->request->is('post')) {
            $this->Request->create();
            if ($this->Request->save($this->request->data)) {
                $this->_notifyUpdateRegisteredKeywords($programUrl);
                $this->set(
                    'result', array(
                        'status' => 'ok',
                        'request-id' => $this->Request->id,
                        'message' => __('Request created, wait for redirection.'))
                    );
            } else {
                $this->Request->validationErrors = $this->Utils->fillNonAssociativeArray($this->Request->validationErrors);
                $this->set(
                    'result', array(
                        'status' => 'fail',
                        'message' => array('Request' => $this->Request->validationErrors))
                    );
            }
        }
    }


    public function edit()
    {
        $this->set('conditionalActionOptions', $this->_getConditionalActionOptions());

        $programUrl = $this->params['program'];
        $id         = $this->params['id'];

        if ($this->request->is('post')) {
            $this->Request->create();
            $this->Request->id = $id;
            if ($this->Request->save($this->request->data)) {
                $this->_notifyUpdateRegisteredKeywords($programUrl);
                $this->set(
                    'result', array(
                        'status' => 'ok',
                        'request-id' => $this->Request->id,
                        'message' => 'Request saved.')
                    );
            } else {
                $this->Request->validationErrors = $this->Utils->fillNonAssociativeArray($this->Request->validationErrors);
                $this->set(
                    'result', array(
                        'status' => 'fail',
                        'message' => array('Request' => $this->Request->validationErrors)
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


    protected function _getConditionalActionOptions()
    {   
        return $this->LocalizeUtils->localizeLabelInArray(
            $this->Participant->getFilters('conditional-action'));
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
            $this->_notifyUpdateRegisteredKeywords($programUrl);
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

        $keywords          = $this->request->data['keyword'];
        $DialogueHelper    = new DialogueHelper();
        $keywordToValidate = $DialogueHelper->getRequestKeywordToValidate($keywords);

        /**Search in the same Program*/
        $dialogueUsingKeyword = $this->Dialogue->getActiveDialogueUseKeyword($keywordToValidate);
        if ($dialogueUsingKeyword) {
            $this->set(
                'result', array(
                    'status'=>'fail', 
                    'message'=> __("'%s' already used in same program in the '%s' dialogue.", $keywordToValidate, $dialogueUsingKeyword['Dialogue']['name'])
                ));
            return;
        }

        if (isset($this->request->data['object-id'])) 
            $conditions = array(    
                'keywords'=> $keywords,
                'excludeRequest' => $this->request->data['object-id']
                );
         else
            $conditions = array('keywords'=> $keywords);
        $foundKeyword = $this->Request->find(
            'keyphrase', 
            $conditions
            );
        if ($foundKeyword) {
            $this->set(
                'result', array(
                    'status'=>'fail', 
                    'message'=> __("'%s' already used in the same program by a request.", $foundKeyword)
                    )
                );
            return;
        }
        
        /**Search in another program*/
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
    

    protected function _notifyUpdateRegisteredKeywords($workerName)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateRegisteredKeywords($workerName);
    }



}
