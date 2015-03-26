<?php
App::uses('ModelValidator', 'Model');


class MongoModelValidator extends ModelValidator
{

	public function invalidate($field, $message = true) {
		if ($message === "noMessage") {
			return;
		}
		$this->getModel()->validationErrors[$field][] = $message;
	}


}
