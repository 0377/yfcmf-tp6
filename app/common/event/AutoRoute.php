<?php

namespace app\common\event;

use think\facade\Route;

class AutoRoute
{
    public function handle()
    {
        if (app()->http->getName() == 'admin') {
            Route::get('general/config/check', '\app\admin\controller\general\Config@check');
            Route::get('general/config/add', '\app\admin\controller\general\Config@add');
            Route::get('general/config/index', '\app\admin\controller\general\Config@index');
            Route::get('general/config', '\app\admin\controller\general\Config@index');
        }
    }
}
