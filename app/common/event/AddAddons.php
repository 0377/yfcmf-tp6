<?php

namespace app\common\event;

use think\exception\HttpException;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\facade\Env;
use think\facade\Event;
use think\facade\Route;

class AddAddons
{
    public function handle()
    {
        // 插件目录
        define('ADDON_PATH', app()->getRootPath() . 'addons' . DIRECTORY_SEPARATOR);
        // 如果插件目录不存在则创建
        if (!is_dir(ADDON_PATH)) {
            @mkdir(ADDON_PATH, 0755, true);
        }
        Route::rule('addons/:addon/[:controller]/[:action]',"\\think\\addons\\Route::execute")
            ->middleware(\app\common\middleware\Addon::class);

        //注册路由
        $routeArr = (array)Config::get('addons.route');
        $domains = [];
        $rules = [];
        $execute = "\\think\\addons\\Route::execute?addon=%s&controller=%s&action=%s";
        $execute = "\\think\\addons\\Route::execute";
        foreach ($routeArr as $k => $v) {
            if (is_array($v)) {
                $addon = $v['addon'];
                $domain = $v['domain'];
                $drules = [];
                foreach ($v['rule'] as $m => $n) {
                    list($addon, $controller, $action) = explode('/', $n);
                    $drules[$m] = sprintf($execute . '&indomain=1', $addon, $controller, $action);
                }
                //$domains[$domain] = $drules ? $drules : "\\addons\\{$k}\\controller";
                $domains[$domain] = $drules ? $drules : [];
                $domains[$domain][':controller/[:action]'] = sprintf($execute . '&indomain=1', $addon, ":controller", ":action");
            } else {
                if (!$v)
                    continue;
                list($addon, $controller, $action) = explode('/', $v);
                //$rules[$k] = sprintf($execute, $addon, $controller, $action);
                //Route::rule($k, sprintf($execute, $addon, $controller, $action));
                Route::rule($k, "\\think\\addons\\Route::execute")
                    ->name($k)
                    ->completeMatch(true)
                    ->append(["addon"=>$addon, "controller"=>$controller, "action"=>$action]);
            }
        }
        // 获取系统配置
        $hooks =Env::get('APP_DEBUG') ? [] : Cache::get('hooks', []);
        if (empty($hooks)) {
            $hooks = (array)Config::get('addons.hooks');
            // 初始化钩子
            foreach ($hooks as $key => $values) {
                if (is_string($values)) {
                    $values = explode(',', $values);
                } else {
                    $values = (array)$values;
                }
                $hooks[$key] = array_filter(array_map('get_addon_class', $values));
            }
            Cache::set('hooks', $hooks);
        }
        //如果在插件中有定义app_init，则直接执行
        if (isset($hooks['app_init'])) {
            foreach ($hooks['app_init'] as $k => $v) {
                Event::trigger('app_init',$v);
            }
        }
        Event::listenEvents($hooks);
    }
}
