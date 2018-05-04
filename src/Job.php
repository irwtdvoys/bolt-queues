<?php
	namespace Bolt;

	use Bolt\Interfaces\Connection;

	abstract class Job extends Base
	{
		protected $connection;

		public $type = null;
		public $data = array();
		public $receipt = null;

		public function __construct(Connection $connection = null)
		{
			$this->connection = $connection;
		}

		abstract public function execute($data = null);

		protected function output($message, $type = null)
		{
			switch ($type)
			{
				case "system":
					$colour = "\e[33m";
					break;
				case "job":
					$colour = "\e[34m";
					break;
				case "error":
					$colour = "\e[31m";
					break;
				default:
					$colour = "\033[0m";
					break;
			}

			echo(sprintf("- %s(%s) %s \033[0m \n", $colour, date("c"), $message));
		}
	}
?>
