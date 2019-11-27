<?php
/**
 * *
 *  * ============================================================================
 *  * Created by PhpStorm.
 *  * User: Ice
 *  * 邮箱: ice@sbing.vip
 *  * 网址: https://sbing.vip
 *  * Date: 2019/9/19 下午3:53
 *  * ============================================================================.
 */
declare(strict_types=1);

namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;
use think\facade\Env;
use think\facade\Cookie;
use think\facade\Config;

/**
 * Fast初始化，Admin/Index/Addon都会执行此方法，除了Api.
 */
class FastInit
{
    /**
     * Fast6框架初始化.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // 设置mbstring字符编码
        mb_internal_encoding('UTF-8');

        // 设置替换内容
        $this->initReplaceString();
        //设置DEBUG环境
        $this->initDebugEnv();
        // 切换多语言
        if ($request->get('lang')) {
            Cookie::set('think_var', $request->get('lang'));
        }
        // Form别名
        if (! class_exists('Form')) {
            class_alias('fast\\Form', 'Form');
        }

        return $next($request);
    }

    /**
     * 模板内容替换
     */
    private function initReplaceString(){
        // 设置替换字符串
        $url = ltrim(dirname(app()->request->root()), DIRECTORY_SEPARATOR);
        // 如果未设置__CDN__则自动匹配得出
        $tpl_replace_string = Config::get('view.tpl_replace_string');
        if (!Config::get('view.tpl_replace_string.__CDN__')) {
            $tpl_replace_string['__CDN__']=$url;
        }
        // 如果未设置__PUBLIC__则自动匹配得出
        if (!Config::get('view.tpl_replace_string.__PUBLIC__')) {
            $tpl_replace_string['__PUBLIC__']= $url . '/';
        }
        // 如果未设置__ROOT__则自动匹配得出
        if (!Config::get('view.tpl_replace_string.__ROOT__')) {
            $tpl_replace_string['__ROOT__']= preg_replace("/\/public\/$/", '', $url . '/');
        }
        Config::set(['tpl_replace_string'=>$tpl_replace_string],'view');
        Config::set($tpl_replace_string,'view_replace_str');
        if (! Config::get('site.cdnurl')) {
            Config::set(['cdnurl' => $url], 'site');
        }
        // 如果未设置cdnurl则自动匹配得出
        if (! Config::get('upload.cdnurl')) {
            Config::set(['cdnurl' => $url], 'upload');
        }
    }

    /**
     * 调试模式缓存
     */
    private function initDebugEnv(){
        if (Env::get('APP_DEBUG')) {
            // 如果是调试模式将version置为当前的时间戳可避免缓存
            Config::set(['version' => time()], 'site');
            //如果是调试模式将关闭视图缓存
            Config::set(['tpl_cache' => false], 'view');
            // 如果是开发模式那么将异常模板修改成官方的
            Config::set(['exception_tmpl' => app()->getThinkPath().'tpl/think_exception.tpl']);
        }
    }
}
