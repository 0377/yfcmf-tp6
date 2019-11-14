<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

namespace think;

//是否composer
if (! file_exists('../vendor')) {
    exit('根目录缺少vendor,请先composer install');
}

require __DIR__.'/../vendor/autoload.php';

// 判断是否安装fastadmin-tp6
if (! is_file('../config/install.lock')) {
    header("location:./install.php");
    exit;
}

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
