<?php
/*
 * 配置文件
 * 
 */

/**前台调用口令*/
define('KEY','yht123hito');

/**路径 **/
define('ABSPATH',dirname(__FILE__).'/');

/**爬行蜘蛛名称*/
define('UserAgent','Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36');

/**爬行超时时间 秒*/
define('TimeOut',5);

/**COOKIEFILE */
define('COOKIEFILE',ABSPATH.'caches/cookies.txt');

/**COOKIE清空间隔 秒*/
define('COOKREFRESH', 120);
