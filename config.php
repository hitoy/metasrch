<?php
/*
 * 配置文件
 * 
 */

/**前台调用口令*/
define('KEY','yht123hito');

/**爬行蜘蛛名称*/
define('UserAgent','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Mobile Safari/537.36');

/**爬行超时时间 秒*/
define('TimeOut',5);

/**COOKIEFILE */
define('COOKIEFILE',dirname(__FILE__).'./caches/cookies.txt');

/**COOKIE清空间隔 秒*/
define('COOKREFRESH',7200);

/**路径 **/
define('ABSPATH',dirname(__FILE__).'/');
