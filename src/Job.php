<?php
	namespace Bolt;

	use Bolt\Interfaces\Connection;
	use Bolt\Job\Colours;

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
					$colour = Colours::SYSTEM;
					break;
				case "job":
					$colour = Colours::JOB;
					break;
				case "error":
					$colour = Colours::ERROR;
					break;
				default:
					$colour = Colours::DEFAULT;
					break;
			}

			echo(sprintf("- %s(%s) %s \033[0m \n", $colour, date("c"), $message));
		}
	}
?>
