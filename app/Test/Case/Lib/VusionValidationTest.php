<?php
App::uses('VusionValidation', 'Lib');


class VusionValidationTest extends CakeTestCase
{

	public function testValidCustomizeContent_context()
	{
		$data = array('content' => 'Hello [context.message.2]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertTrue($result);

		$data = array('content' => 'Hello [context.message.2.2]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertTrue($result);

		$data = array('content' => 'Hello [context.message.1.2] ');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertTrue($result);

		$data = array('content' => 'Hello [context.message.3.end]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertTrue($result);
	}


	public function testValidCustomizeContent_context_fail_x_is_0()
	{
		$data = array('content' => 'Hello [context.message.0]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertEquals(
			$result,
			"On [context.message.x], x has to be greater or equal to 1.");
	}


	public function testValidCustomizeContent_context_fail_x_is_end() 
	{
		$data = array('content' => 'Hello [context.message.end]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertEquals(
			$result,
			"On [context.message.x], x has to be a number.");
	}


	public function testValidCustomizeContent_context_fail_x_lower_y()
	{
		$data = array('content' => 'Hello [context.message.2.1]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertEquals(
			$result,
			"On [context.message.x.y], y cannot be lower than x.");
	}


	public function testValidCustomizeContent_context_fail_y_is_string()
	{
		$data = array('content' => 'Hello [context.message.2.something]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertEquals(
			$result,
			"On [context.message.x.y], y has to be a number or 'end'.");
	}


	public function testValidCustomizeContent_context_fail_y_not_set()
	{
		$data = array('content' => 'Hello [context.message.2.]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertEquals(
			$result,
			"On [context.message.x.y], y has to be a number or 'end'.");
	}


	public function testValidCustomizeContent_context_fail_x_is_string()
	{
		$data = array('content' => 'Hello [context.message.something.3]');
		$result = VusionValidation::validCustomizeContent('content', $data, VusionConst::CUSTOMIZE_CONTENT_DOMAIN_RESPONSE);
		$this->assertEquals(
			$result,
			"On [context.message.x.y], x has to be a number.");
	}


}