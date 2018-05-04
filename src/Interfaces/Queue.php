<?php
	namespace Bolt\Interfaces;

	interface Queue
	{
		public function fetch();
		public function release($receipt);
		public function delete($receipt);
	}
?>
