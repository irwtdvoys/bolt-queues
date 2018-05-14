<?php
	namespace Bolt;

	class Metrics extends Base
	{
		public $success = false;
		public $message;
		public $data;

		public function __toString()
		{
			return (string)$this->message();
		}
	}
?>
