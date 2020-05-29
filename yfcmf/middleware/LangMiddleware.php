<?php


namespace yfcmf\middleware;

use Closure;
use think\App;
use think\Lang;
use think\Request;
use think\Response;
use yfcmf\library\Common;

/**
 * 多语言中间件
 *
 * @package yfcmf\middleware
 */
class LangMiddleware
{
    protected $app;

    protected $lang;

    public function __construct(App $app, Lang $lang)
    {
        $this->app  = $app;
        $this->lang = $lang;
    }

    /**
     * 路由初始化（路由规则注册）
     *
     * @access public
     *
     * @param  Request  $request
     * @param  Closure  $next
     *
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // 自动侦测当前语言
        $langset = $this->lang->detect($request);

        // 加载系统语言包
        $path = '';
        if (Common::getPhpFile() === 'index') {
            $path = $this->app->getRootPath().'resources'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'index';
        } elseif (defined('YFCMF_ADMIN') && YFCMF_ADMIN == true) {
            $path = $this->app->getRootPath().'resources'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'admin';
        }
        if ($path) {
            $files1 = glob($path.DIRECTORY_SEPARATOR.$langset.'.*');
            $files2 = glob($path.DIRECTORY_SEPARATOR.$langset.DIRECTORY_SEPARATOR.'*');
            $files  = array_merge($files1, $files2);
            $this->lang->load($files);
        }

        $this->lang->saveToCookie($this->app->cookie);

        return $next($request);
    }
}