<?php
/**
 *  ============================================================================
 *  Created by PhpStorm.
 *  User: Ice
 *  邮箱: ice@sbing.vip
 *  网址: https://sbing.vip
 *  Date: 2019/11/27 下午6:09
 *  ============================================================================
 */

// [ 应用入口文件 ]

namespace think;

// 判断是否安装YFCMF-TP6
if (! is_file('../config/install.lock')) {
    header("location:./install.php");
    exit;
}

//是否composer
if (! file_exists('../vendor')) {
    exit('根目录缺少vendor,请先composer install');
}

require __DIR__.'/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
