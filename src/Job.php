<?php
	namespace Bolt;

	use Bolt\Interfaces\Connection;
	use Bolt\Job\Colours;

	abstract class Job extends Base
	{
		protected ?Connection $connection;
		protected Metrics $metrics;

		public array $data = array();
		public ?string $receipt = null;

		public function __construct(Connection $connection = null)
		{
			$this->connection = $connection;
		}

		abstract public function execute(): void;

		public function run($data = null): Metrics
		{
			$this->metrics(new Metrics());
			$this->output("Started `" . $this->className(false) . "` job", Output::JOB);

			if (isset($data))
			{
				$this->data($data);
			}

			$this->execute();

			return $this->metrics();
		}

		public function output($message, $type = null)
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
