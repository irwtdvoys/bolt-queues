<?php

	/**
	 * queueRunner.php
	 * Usage:
	 * > php queueRunner.php -q queueName
	 */

	(PHP_SAPI === "cli") ?: die("Command line usage only");

	require("vendor/autoload.php");

	define("ROOT_SERVER", __DIR__ . "/");

	require_once(ROOT_SERVER . "library/config.php");

	#set_error_handler(array("Cube\\Handler", "error"), E_ALL & ~E_NOTICE);
	#set_exception_handler(array("Cube\\Handler", "exception"));

	$options = getopt("q:");

	if (!$options['q'] || preg_match('/[^a-z_\-0-9]/i', $options['q']))
	{
		die("Invalid queue name");
	}

	$connection = new App\Connections\Aws\Sqs();
	$connection->queue($options['q']);

	$worker = new Queue\Worker($connection);
	$worker->start();
?>
