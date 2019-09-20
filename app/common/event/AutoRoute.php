<?php

namespace app\common\event;

use think\facade\Db;
use think\facade\Route;

class AutoRoute
{
    public function handle()
    {
        // 插件目录

        if (app()->http->getName() == 'admin') {
            /* $routes = Db::name('auth_rule')->field(['name', 'route'])
                 ->where('status', '=', 'normal')
                 ->where('route', '<>', '')->select()->toArray();
             foreach ($routes as $route) {
                 Route::get($route['name'], $route['route']);
             }*/
        }
    }
}
