<?php
/**
 * run with command
 * php start.php start
 */
require_once __DIR__ . '/define.php';

ini_set('display_errors', 'on');

use Workerman\Worker;


// 标记是全局启动
define('GLOBAL_START', 1);

require_once __DIR__ . '/vendor/autoload.php';

// 加载所有Game/start*.php，以便启动所有服务
foreach (glob(APP_PATH . 'start*.php') as $start_file) {
    require_once $start_file;
}
// 运行所有服务
Worker::runAll();