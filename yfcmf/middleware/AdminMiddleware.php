<?php


namespace yfcmf\middleware;


use think\facade\Config;
use think\facade\Event;
use think\facade\Lang;
use think\facade\View;
use yfcmf\provider\YfcmfRequest;

class AdminMiddleware
{
    public function handle(YfcmfRequest $request, \Closure $next)
    {
        $site   = Config::get('site');
        $upload = \app\model\Config::upload();
        // 上传信息配置后
        $res = Event::trigger('upload_config_init', $upload);
        foreach ($res as $item) {
            if (is_array($item)) {
                $upload = array_merge($upload, $item);
            }
        }
        Config::set(array_merge(Config::get('upload'), $upload), 'upload');

        View::engine();
        View::assign('site', $site);
        View::assign('user', app()->auth->getUser());
        return $next($request);
    }
}