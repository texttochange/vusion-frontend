<?php
/* Programs Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ParticipantsController', 'Controller');

class TestParticipantsController extends ParticipantsController {
/**
 * Auto render
 *
 * @var boolean
 */
	public $autoRender = false;

/**
 * Redirect action
 *
 * @param mixed $url
 * @param mixed $status
 * @param boolean $exit
 * @return void
 */
	public function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

/**
 * ScriptsController Test Case
 *
 */
class ParticipantsControllerTestCase extends ControllerTestCase {
/**
 * Data
 *
 */

               var $programData = array(
			0 => array( 
				'Program' => array(
					'name' => 'Test Name',
					'url' => 'testurl',
					'timezone' => 'utc',
					'database' => 'testdbprogram'
				)
			));
	
	
/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Participants = new TestParticipantsController();
		ClassRegistry::config(array('ds' => 'test'));
		
		$this->dropData();
	}

	protected function dropData() {
		$this->instanciateParticipantModel();
		$this->Participants->Participant->deleteAll(true, false);
	}
	
	protected function instanciateParticipantModel() {
		$options = array('database' => $this->programData[0]['Program']['database']);
		$this->Participants->Participant = new Participant($options);
	}
	
/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		
		$this->dropData();
		
		unset($this->Participants);

		parent::tearDown();
	}

/**
 * testIndex method
 *
 * @return void
 */
	public function testImport() 
	{
		$Participants = $this->generate('Participants', array(
			'components' => array(
				'Acl' => array('check'),
				'Session' => array('read')
			),
			'models' => array(
				'Program' => array('find', 'count'),
				'Group' => array()
			),
		));
		
		$Participants->Acl
			->expects($this->any())
			->method('check')
			->will($this->returnValue('true'));
		
		$Participants->Program
			->expects($this->once())
			->method('find')
			->will($this->returnValue($this->programData));
			
		$Participants->Session
			->expects($this->any())
			->method('read')
			->will($this->onConsecutiveCalls(
				'4', 
				'2',
				$this->programData[0]['Program']['database'],
				$this->programData[0]['Program']['name']
				));


		$this->testAction("/testurl/participants/import", array(
			'method' => 'post',
			'data' => array(
				'Import'=> array(
					'file' => array(
						'error' => 0,
						'tmp_name' => TESTS . 'files/wellformattedparticipants.csv',
						'name' => 'wellformattedparticipants.csv')))
			));

		$participantInDatabase = $this->Participants->Participant->find('count');
		$this->assertEquals(2, $participantInDatabase);
	}


	public function testImport_duplicate() 
	{
		$Participants = $this->generate('Participants', array(
			'components' => array(
				'Acl' => array('check'),
				'Session' => array('read')
			),
			'models' => array(
				'Program' => array('find', 'count'),
				'Group' => array()
			),
		));
		
		$Participants->Acl
			->expects($this->any())
			->method('check')
			->will($this->returnValue('true'));
		
		$Participants->Program
			->expects($this->once())
			->method('find')
			->will($this->returnValue($this->programData));
			
		$Participants->Session
			->expects($this->any())
			->method('read')
			->will($this->onConsecutiveCalls(
				'4', 
				'2',
				$this->programData[0]['Program']['database'],
				$this->programData[0]['Program']['name']
				));

		$this->instanciateParticipantModel();
		$this->Participants->Participant->create();
		$this->Participants->Participant->save(array(
			'phone' => '256712747841',
			'name' => 'Gerald'
			));


		$this->testAction("/testurl/participants/import", array(
			'method' => 'post',
			'data' => array(
				'Import'=> array(
					'file' => array(
						'error' => 0,
						'tmp_name' => TESTS . 'files/wellformattedparticipants.csv',
						'name' => 'wellformattedparticipants.csv')))
			));

		$participantInDatabase = $this->Participants->Participant->find('count');
		$this->assertEquals(2, $participantInDatabase);

		
		$this->assertEquals('256788601462,"Olivier Vernin" insert ok', $this->vars['entries'][1]);

	}


}
