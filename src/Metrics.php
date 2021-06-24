<?php
	namespace Bolt;

	class Metrics extends Base
	{
		public bool $success = false;
		public string $message;
		public array $data;

		public function __toString()
		{
			return (string)$this->message();
		}
	}
?>
