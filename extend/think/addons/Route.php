<?php
/**
 * *
 *  * ============================================================================
 *  * Created by PhpStorm.
 *  * User: Ice
 *  * 邮箱: ice@sbing.vip
 *  * 网址: https://sbing.vip
 *  * Date: 2019/9/20 下午6:00
 *  * ============================================================================.
 */

namespace think\addons;

use think\App;
use think\facade\Lang;
use think\facade\Event;
use think\facade\Request;
use think\exception\HttpException;

class Route
{
    public static function execute($addon = null, $controller = null, $action = null)
    {
        $request = \request();
        //halt($request->param(),app()->route);
        // 是否自动转换控制器和操作名
        $convert = true;
        $filter = $convert ? 'strtolower' : 'trim';
        $addon = $addon ? trim(call_user_func($filter, $addon)) : '';
        $controller = $controller ? trim(call_user_func($filter, $controller)) : 'index';
        $action = $action ? trim(call_user_func($filter, $action)) : 'index';
        Event::trigger('addon_begin', $request);
        if (! empty($addon) && ! empty($controller) && ! empty($action)) {
            $info = get_addon_info($addon);
            if (! $info) {
                throw new HttpException(404, __('addon %s not found', $addon));
            }
            if (! $info['state']) {
                throw new HttpException(500, __('addon %s is disabled', $addon));
            }
            // 设置当前请求的控制器、操作
            $request->setController($controller)->setAction($action);
            // 监听addon_module_init
            Event::trigger('addon_module_init', $request);
            $class = get_addon_class($addon, 'controller', $controller);
            if (! $class) {
                throw new HttpException(404, __('addon controller %s not found', parseName($controller, 1)));
            }
            $instance = new $class(app());
            $vars = [];
            if (is_callable([$instance, $action])) {
                // 执行操作方法
                $call = [$instance, $action];
            } elseif (is_callable([$instance, '_empty'])) {
                // 空操作
                $call = [$instance, '_empty'];
                $vars = [$action];
            } else {
                // 操作不存在
                throw new HttpException(404,
                    __('addon action %s not found', get_class($instance).'->'.$action.'()'));
            }
            Event::trigger('addon_action_begin', $call);

            return call_user_func_array($call, $vars);
        } else {
            abort(500, lang('addon can not be empty'));
        }
    }
}
