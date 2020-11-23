<?php 
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
use \Workerman\Worker;
use \GatewayWorker\BusinessWorker;
use Workerman\MySQL\Connection;

// 自动加载类
require_once __DIR__ . '/../../vendor/autoload.php';

// bussinessWorker 进程
$worker = new BusinessWorker();
// worker名称
$worker->name = 'ChatBusinessWorker';
// bussinessWorker进程数量
$worker->count = 2;
// 服务注册地址
$worker->registerAddress = '127.0.0.1:1238';
$worker->onWorkerStart = function ($worker){
    global $db1;
    $db1 = new Connection(
        '0.0.0.0',//服务器ip
        3306,
        'tp_layim',//服务器mysql账号
        '*******',//服务器mysql密码
        'tp_layim',//服务器mysql数据库名
        'utf8'
    );
};
// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

