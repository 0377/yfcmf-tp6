<?php


namespace yfcmf\middleware;

use think\facade\Config;
use think\facade\Event;
use think\facade\Lang;
use think\facade\View;
use yfcmf\provider\YfcmfRequest;

class FrontendMiddleware
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
        // 配置信息
        $modulename = 'index';
        $config     = [
            'site'           => array_intersect_key($site,
                array_flip(['name', 'cdnurl', 'version', 'timezone', 'languages'])),
            'upload'         => $upload,
            'modulename'     => $modulename,
            'controllername' => $request->controller(),
            'actionname'     => $request->action(),
            'jsname'         => 'frontend/'.str_replace('.', '/', $request->controller()),
            'moduleurl'      => rtrim(url("/{$modulename}", [], false), '/'),
            'language'       => Lang::getLangSet(),
        ];

        // 配置信息后

        $res = Event::trigger('config_init', $config);;
        foreach ($res as $item) {
            if (is_array($item)) {
                $config = array_merge($config, $item);
            }
        }
        View::engine();
        View::assign('site', $site);
        View::assign('config', $config);
        View::assign('user', app()->auth->getUser());
        return $next($request);
    }
}