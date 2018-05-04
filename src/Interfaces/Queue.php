<?php
	namespace Bolt\Interfaces;

	interface Queue
	{
		public function add($payload, $priority = 5);
		public function fetch();
		public function release($receipt);
		public function delete($receipt);
	}
?>
