<?php
	namespace Bolt\Job;
	
	use Bolt\Enum;

	class Output extends Enum
	{
		const DEFAULT = "\033[0m";
		const SYSTEM = "\e[33m";
		const JOB = "\e[34m";
		const ERROR = "\e[31m";
	}
?>
