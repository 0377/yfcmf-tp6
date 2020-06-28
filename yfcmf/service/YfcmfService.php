<?php
/**
 *  =============================================================
 *  Created by PhpStorm.
 *  User: Ice
 *  邮箱: ice@sbing.vip
 *  网址: https://sbing.vip
 *  Date: 2020/6/28 下午12:05
 *  ==============================================================
 */

namespace yfcmf\service;

use think\Lang;
use think\middleware\SessionInit;
use think\Service;
use think\facade\Env;
use think\facade\Config;
use yfcmf\library\AdminAuth;
use yfcmf\library\Auth;
use yfcmf\library\Common;
use yfcmf\library\Form;
use yfcmf\middleware\AdminMiddleware;
use yfcmf\middleware\FrontendMiddleware;

/**
 * 框架核心服务
 */
class YfcmfService extends Service
{
    /** @var Lang */
    protected $lang;

    public function register()
    {
        // 服务注册
        if (defined('YFCMF_ADMIN') && YFCMF_ADMIN == true) {
            $this->app->bind('auth', AdminAuth::class);
        } else {
            $this->app->bind('auth', Auth::class);
        }

    }

    public function boot(Lang $lang)
    {
        // 服务启动
        $this->lang = $lang;
        $this->initCore();
        if (Common::getPhpFile() === 'index') {
            $this->app->event->listen('HttpRun', function () {
                $this->app->middleware->add(SessionInit::class);
                $this->app->middleware->add(FrontendMiddleware::class);
            });
            Common::hook('init_yfcmf');
        } elseif (defined('YFCMF_ADMIN') && YFCMF_ADMIN == true) {
            $this->app->event->listen('HttpRun', function () {
                $this->app->middleware->add(SessionInit::class);
                $this->app->middleware->add(AdminMiddleware::class);
            });
            Common::hook('init_admin');
        }
    }


    /**
     * 初始化服务
     */
    private function initCore()
    {
        // 设置mbstring字符编码
        mb_internal_encoding('UTF-8');
        $this->initLang();
        $this->initView();
        // 设置替换内容
        $this->initReplaceString();
        //设置DEBUG环境
        $this->initDebugEnv();
        // Form别名
        if (!class_exists('Form')) {
            class_alias(Form::class, 'Form');
        }
    }

    /**
     * 配置视图目录
     */
    private function initView()
    {
        if (Common::getPhpFile() === 'index') {
            //主题配置
            $theme = \config('site.theme') ?: 'default';
            $path  = $this->app->getRootPath().'resources'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'index'.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR;
            Config::set(['view_path' => $path], 'view');
        } elseif (defined('YFCMF_ADMIN') && YFCMF_ADMIN == true) {
            $path = $this->app->getRootPath().'resources'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR;
            Config::set(['view_path' => $path], 'view');
        }
    }

    /**
     * 加载语言包
     */
    private function initLang()
    {
        // 自动侦测当前语言
        $langset = $this->lang->detect(app()->request);

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
    }

    /**
     * 调试模式缓存
     */
    private function initDebugEnv()
    {
        if (Env::get('APP_DEBUG')) {
            // 如果是调试模式将version置为当前的时间戳可避免缓存
            Config::set(['version' => time()], 'site');
            //如果是调试模式将关闭视图缓存
            Config::set(['tpl_cache' => false], 'view');
            // 如果是开发模式那么将异常模板修改成官方的
            Config::set(['exception_tmpl' => app()->getThinkPath().'tpl/think_exception.tpl']);
        }
    }

    /**
     * 模板内容替换
     */
    private function initReplaceString()
    {
        // 设置替换字符串
        $url = ltrim(dirname(app()->request->root()), DIRECTORY_SEPARATOR);
        // 如果未设置__CDN__则自动匹配得出
        $tpl_replace_string = Config::get('view.tpl_replace_string');
        if (!Config::get('view.tpl_replace_string.__CDN__')) {
            $tpl_replace_string['__CDN__'] = $url;
        }
        // 如果未设置__PUBLIC__则自动匹配得出
        if (!Config::get('view.tpl_replace_string.__PUBLIC__')) {
            $tpl_replace_string['__PUBLIC__'] = $url.'/';
        }
        // 如果未设置__ROOT__则自动匹配得出
        if (!Config::get('view.tpl_replace_string.__ROOT__')) {
            $tpl_replace_string['__ROOT__'] = preg_replace("/\/public\/$/", '', $url.'/');
        }
        Config::set(['tpl_replace_string' => $tpl_replace_string], 'view');
        Config::set($tpl_replace_string, 'view_replace_str');
        if (!Config::get('site.cdnurl')) {
            Config::set(['cdnurl' => $url], 'site');
        }
        // 如果未设置cdnurl则自动匹配得出
        if (!Config::get('upload.cdnurl')) {
            Config::set(['cdnurl' => $url], 'upload');
        }
    }
}
