<?php
use think\facade\Route;

// 插件目录
define('ADDON_PATH', app()->getBasePath() . 'addons' . DIRECTORY_SEPARATOR);
// 定义路由
Route::any('addons/:addon/[:controller]/[:action]', '\app\common\controller\AddonsRoute@execute')
    ->middleware(\app\common\middleware\Addon::class);

// 如果插件目录不存在则创建
if (!is_dir(ADDON_PATH)) {
    @mkdir(ADDON_PATH, 0755, true);
}

