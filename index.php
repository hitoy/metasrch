<?php
/*
 * 
 */
require_once('./config.php');
require_once('./lib/functions.php');
require_once('./lib/class.crawler.php');
require_once('./lib/class.scheduler.php');
$scheduler = new Scheduler();
$scheduler->run();
