<?php
App::uses('AppController','Controller');
App::uses('Participant','Model');
App::uses('ParticipantsState', 'Model');

class ParticipantsController extends AppController {
	
	function constructClasses() {
		parent::constructClasses();
		
		$options = array('database' => ($this->Session->read($this->params['program']."_db")));
		
		$this->Participant = new Participant($options);
		$this->ParticipantsState = new ParticipantsState($options);
	}

	function beforeFilter() {
		parent::beforeFilter();
		//$this->Auth->allow('*');
	}
	
	public function index() {
		$programName = $this->Session->read($this->params['program'].'_name');
		$programUrl = $this->params['program'];
		$participants = $this->paginate();
		$this->set(compact('programName', 'programUrl', 'participants'));		
	}
	
	public function add() {	
		if ($this->request->is('post')) {
			$this->Participant->create();
			if ($this->Participant->save($this->request->data)) {
				$this->Session->setFlash(__('The participant has been saved.'));
				$this->redirect(array(
					'program' => $this->params['program'],  
					'controller' => 'participants',
					'action' => 'index'
					));
			} else {
				$this->Session->setFlash(__('The participant could not be saved.'));
			}
		}
		$programName = $this->Session->read($this->params['program'].'_name');
		$programUrl = $this->params['program'];
		$this->set(compact('programName', 'programUrl'));		
	}
	
	public function edit() {
		$id = $this->params['id'];
		$programName = $this->Session->read($this->params['program'].'_name');
		$programUrl = $this->params['program'];
		$this->Participant->id = $id;
		if (!$this->Participant->exists()) {
			throw new NotFoundException(__('Invalid participant'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Participant->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				$this->redirect(array('program' => $programUrl, 'controller'=>'participants', 'action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Participant->read(null, $id);
		}
		$this->set(compact('programName', 'programUrl')); 
	}
	
	public function delete () {
		$id = $this->params['id'];
		$programUrl = $this->params['program'];
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Participant->id = $id;
		if (!$this->Participant->exists()) {
			throw new NotFoundException(__('Invalid participant:') . $id);
		}
		if ($this->Participant->delete()) {
			$this->Session->setFlash(__('Participant deleted'));
			$this->redirect(array('program' => $programUrl,
				'controller' => 'participants',
				'action' => 'index'
				));
		}
		$this->Session->setFlash(__('Participant was not deleted'));
		$this->redirect(array('program' => $programUrl,
				'controller' => 'participants',
				'action' => 'index'
				));
	}
	
	public function view() {
		$programName = $this->Session->read($this->params['program'].'_name');
		$programUrl = $this->params['program'];
		$id = $this->params['id'];
		$this->Participant->id = $id;
		if (!$this->Participant->exists()) {
			throw new NotFoundException(__('Invalid participant'));
		}
		$participant = $this->Participant->read(null, $id);
		$histories = $this->ParticipantsState->find('participant', array(
				'phone' => $participant['Participant']['phone']
			));
		$this->set(compact('programName', 
			'programUrl',
			'participant',
			'histories'));
	
	}
	
	public function import(){

		require_once 'excel_reader2.php';
		//$data = new Spreadsheet_Excel_Reader("example.xls");

		$programName = $this->Session->read($this->params['program'].'_name');
		$programUrl = $this->params['program'];
		$this->set(compact('programName','programUrl'));

		if ($this->request->is('post')) {
			if(!$this->request->data['Import']['file']['error']){
				$fileName = $this->request->data['Import']['file']["name"];
				$ext = end(explode('.', $fileName));
				if (!($ext == 'csv') and !($ext == 'xls')) {
					$this->Session->setFlash('This file format is not supported');
					return;
				}

				$filePath = WWW_ROOT . "files/" . $programUrl; 

				if (!file_exists(WWW_ROOT . "files/".$programUrl)){
					echo 'create folder: ' . WWW_ROOT . "files/".$programUrl;
					mkdir($filePath);
					chmod($filePath, 0777);
				}
								
				/** in case the file has already been created, 
				* the chmod function should not be called.
				*/
				$wasFileAlreadyThere = false;
				if(file_exists($filePath . DS . $fileName)){
					$wasFileAlreadyThere = true;
				}

				copy($this->request->data['Import']['file']['tmp_name'],
					$filePath . DS . $fileName);
				
				if(!$wasFileAlreadyThere) {
					chmod($filePath . DS . $fileName, 0777);
				}
				
				if ($ext == 'csv') {
					$importedParticipants = fopen($filePath . DS . $fileName,"r");
					$entries = array();
					$participant = array();
					$count = 0;
					while(!feof($importedParticipants)){					
						$entries[] = fgets($importedParticipants);
						if($count > 0 && $entries[$count]){
							$this->Participant->create();
							$entries[$count] = str_replace("\n", "", $entries[$count]);
							$explodeLine = explode(",", $entries[$count]);
							$participant['phone'] = $explodeLine[0];
							$participant['name'] = $explodeLine[1];
							//print_r($participant);
							if ($this->Participant->save($participant)) {
								$entries[$count] .= " insert ok"; 
							} else {
								$entries[$count] .= " duplicated phone";
							}
							
						}
						$count++;
					}
				} else if ($ext == 'xls') {
					$data = new Spreadsheet_Excel_Reader($filePath . DS . $fileName);
					for( $i = 2; $i <= $data->rowcount($sheet_index=0); $i++) {
						//echo " iter:".$i;
						$participant['phone'] = $data->val($i,'A');
						$participant['name'] = $data->val($i,'B');
						$this->Participant->create();
						//for view report
						$entries[$i] = $participant['phone'] . ','.$participant['name'];
						if ($this->Participant->save($participant)) {
							$entries[$i] .= ", insert ok"; 
						} else {
							$entries[$i] .= ", duplicated phone";
						}
					}
					//$this->Session->setFlash('Import xls has to be written now');
				}
			}
		} 
		
		$this->set(compact('entries'));
	}
	

	
}

?>
