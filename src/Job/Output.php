<?php
	namespace Bolt\Job;
	
	use Bolt\Enum;

	class Output extends Enum
	{
		const DEFAULT = "default";
		const SYSTEM = "system";
		const JOB = "job";
		const ERROR = "error";
	}
?>
