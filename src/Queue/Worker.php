<?php
	namespace Bolt\Queue;

	use Bolt\Interfaces\Connection;

	/**
	 * Class QueueWorker
	 * This class has been designed to run standalone jobs issued from any chosen queueing implementation.
	 *
	 * @package Cube
	 */
	class Worker
	{
		/**
		 * Current amount of work iterations a worker has carried out.
		 *
		 * @var int
		 */
		private $loopNumber = 0;

		/**
		 * Should the worker continue running after finishing it's current job
		 *
		 * @var bool
		 */
		private $shouldRun = true;

		/**
		 * The chosen queueing implementation to get tasks from
		 *
		 * @var Connection
		 */
		private $connection;

		/**
		 * If the current platform has support for the pcntl extension
		 *
		 * @var bool
		 */
		private $pcntl;

		/**
		 * @param Connection $connection
		 */
		public function __construct(Connection $connection)
		{
			declare(ticks = 1);

			$this->connection = $connection;
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
					$this->printOutput("Exiting", "system");
					$this->shouldRun = false;
					break;
			}
		}

		/**
		 * Pops the next job out of the chosen queue platform
		 *
		 * @param string $queueName The nice name for the queue we are requesting the job from
		 * @return AbstractJob
		 */
		protected function getJob()
		{
			$job = new \App\Models\Job($this->connection);
			$result = $job->fetch();

			if ($result === false)
			{
				return false;
			}

			return $job;
		}

		/**
		 * Begins working on jobs in the chosen queue platform
		 *
		 */
		public function start()
		{
			$this->printOutput("Starting PHP queue worker", "system");

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
					$this->printOutput("Job received (" . $job->type . ")", "job");

					try
					{
						$result = $job->execute();
					}
					catch (\Exception $e)
					{
						$this->printOutput($e->getMessage(), "job");
						$result = false;
					}

					if ($result === true)
					{
						$this->printOutput("Job deleted", "job");
						$job->delete();
					}
					else
					{
						$this->printOutput("Job released", "job");
						$job->release();
					}
				}
				catch (\Exception $e)
				{
					$this->printOutput("Job released", "job");
					$job->release();
					error_log($e->getTraceAsString());
				}

				if ($this->pcntl)
				{
					pcntl_signal_dispatch();
				}
			}
		}

		private function printOutput($message, $type = null)
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
