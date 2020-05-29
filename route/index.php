<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;
use yfcmf\middleware\AuthMiddleware;

    if (isset($_SERVER['SCRIPT_FILENAME'])) {
        $file = $_SERVER['SCRIPT_FILENAME'];
    } elseif (isset($_SERVER['argv'][0])) {
        $file = realpath($_SERVER['argv'][0]);
    }
    $file =isset($file) ? pathinfo($file, PATHINFO_FILENAME) : '';
if($file==='index'){
//    Route::group(function () {
//        Route::group(function () {
//            Route::rule('/', 'index');
//        })->prefix('index.index/');
//    })->middleware(AuthMiddleware::class);
}elseif($file==='admin'){
    //非index.php文件 指向到admin
//    halt('admin');
}
