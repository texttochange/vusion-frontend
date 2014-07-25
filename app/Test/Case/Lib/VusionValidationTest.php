<?php
App::uses('VusionValidation', 'Lib');


class VusionValidationTest extends CakeTestCase
{

	public function testValidCustomizeContent_context()
	{
		$data = array('content' => 'Hello [context.message.2]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertTrue($result);

		$data = array('content' => 'Hello [context.message.after.2] ');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertTrue($result);

		$data = array('content' => 'Hello [context.message.before.2]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertTrue($result);
	}


	public function testValidCustomizeContent_context_fail()
	{
		$data = array('content' => 'Hello [context.message.3]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertEquals(
			$result,
			"On [context.message.x], x has to be either 1 or 2.");

		$data = array('content' => 'Hello [context.message.2.1]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertEquals(
			$result,
			"On [context.message.x.y], y is not allowed if x is a number.");

		$data = array('content' => 'Hello [context.message.after.3]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertEquals(
			$result,
			"On [context.message.after/before.x], x has to be either 1 or 2.");

		$data = array('content' => 'Hello [context.message.after.]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertEquals(
			$result,
			"On [context.message.after/before.x], x has to be either 1 or 2.");

		$data = array('content' => 'Hello [context.message.something.3]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertEquals(
			$result,
			"On [context.message.x], x can be 'after', 'before' or a number.");

	}

}