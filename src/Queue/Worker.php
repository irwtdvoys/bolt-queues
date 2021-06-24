<?php
	namespace Bolt\Queue;

	use Bolt\Interfaces\Connection;
	use Bolt\Job;
	use Bolt\Metrics;

	/**
	 * Class QueueWorker
	 * This class has been designed to run standalone jobs issued from any chosen queueing implementation.
	 *
	 * @package Bolt
	 */
	class Worker
	{
		/**
		 * Adapter for handling queue
		 *
		 * @var
		 */
		public $adapter;

		/**
		 * Current amount of work iterations a worker has carried out.
		 */
		private int $loopNumber = 0;

		/**
		 * Should the worker continue running after finishing it's current job
		 */
		private bool $shouldRun = true;

		/**
		 * The chosen queueing implementation to get tasks from
		 */
		private Connection $connection;

		/**
		 * If the current platform has support for the pcntl extension
		 */
		private bool $pcntl;

		/**
		 * @param Connection $connection
		 */
		public function __construct(Connection $connection)
		{
			declare(ticks = 1);

			$this->connection = $connection;

			// set adapter
			$className = "App\\Adapters\\Queue\\" . $connection->className(false);
			$this->adapter = new $className($connection);

			$this->pcntl = extension_loaded('pcntl');

			if ($this->pcntl)
			{
				pcntl_signal(SIGTERM, array($this, 'signalHandler'));
				pcntl_signal(SIGINT,  array($this, 'signalHandler'));
				pcntl_signal(SIGQUIT, array($this, 'signalHandler'));
			}
		}

		/**
		 * Handles signals sent to the process by the parent system
		 *
		 * @param int $signal The signal number received
		 */
		public function signalHandler($signal)
		{
			switch ($signal)
			{
				case SIGTERM:
				case SIGINT:
				case SIGQUIT:
					$this->output("Exiting", "system");
					$this->shouldRun = false;
					break;
			}
		}

		/**
		 * Pops the next job out of the chosen queue platform
		 *
		 * @return Job | false
		 */
		protected function getJob()
		{
			$data = $this->adapter->fetch();

			if ($data === false)
			{
				return false;
			}

			$jobClass = "\\App\\Jobs\\" . str_replace(".", "\\", $data->type);

			if (!class_exists($jobClass))
			{
				$this->output("'" . $data->type . "' job not found", "error");
				return false;
			}

			$job = new $jobClass($this->connection);
			$job->data($data->data);
			$job->receipt($data->receipt);

			return $job;
		}

		/**
		 * Begins working on jobs in the chosen queue platform
		 *
		 */
		public function start()
		{
			$this->output("Starting PHP queue worker", "system");

			while ($this->shouldRun)
			{
				$this->loopNumber++;
				$job = $this->getJob();

				if ($job === false)
				{
					continue;
				}

				try
				{
					// Todo: alter location of job type - build from class name
					$this->output("Job received (" . $job->type . ")", "job");

					try
					{
						$result = $job->run();
					}
					catch (\Exception $e)
					{
						$this->output($e->getMessage(), "error");
						$result = new Metrics();
					}

					if ($result->success() === true)
					{
						$this->output("Job deleted", "job");
						$this->adapter->delete($job->receipt());
					}
					else
					{
						$this->output("Job released", "job");
						$this->adapter->release($job->receipt());
					}
				}
				catch (\Exception $e)
				{
					$this->output("Job released", "job");
					$this->adapter->release($job->receipt());
					error_log($e->getTraceAsString());
				}

				if ($this->pcntl)
				{
					pcntl_signal_dispatch();
				}
			}
		}

		private function output($message, $type = null)
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

			echo(sprintf("> %s(%s) %s \033[0m \n", $colour, date("c"), $message));
		}
	}
?>
