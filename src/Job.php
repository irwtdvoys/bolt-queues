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
					$colour = Job\Output::SYSTEM;
					break;
				case "job":
					$colour = Job\Output::JOB;
					break;
				case "error":
					$colour = Job\Output::ERROR;
					break;
				default:
					$colour = Job\Output::DEFAULT;
					break;
			}

			echo(sprintf("- %s(%s) %s \033[0m \n", $colour, date("c"), $message));
		}
	}
?>
