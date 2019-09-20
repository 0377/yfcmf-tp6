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

halt(app()->getAppPath() . 'addons' . DIRECTORY_SEPARATOR);
// 插件目录
define('ADDON_PATH', app()->getAppPath() . 'addons' . DIRECTORY_SEPARATOR);
// 定义路由
Route::any('addons/:addon/[:controller]/[:action]', '\app\common\controller\AddonsRoute@execute');

// 如果插件目录不存在则创建
if (!is_dir(ADDON_PATH)) {
    @mkdir(ADDON_PATH, 0755, true);
}
