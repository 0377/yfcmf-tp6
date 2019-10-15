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
use think\facade\Config;
use think\facade\Env;
use think\facade\View;
use think\Request;
use think\Response;

/**
 * Fast初始化.
 */
class FastInit
{
    /**
     * Session初始化.
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

        // 如果修改了index.php入口地址，则需要手动修改cdnurl的值
        //$url = preg_replace("/\/(\w+)\.php$/i", '', $request->root());
        $url = ltrim(dirname($request->root()), DIRECTORY_SEPARATOR);
        View::filter(function ($content) use ($url) {
            return str_replace(['__CDN__', '__PUBLIC__', '__ROOT__'],
                [$url, $url.'/', preg_replace("/\/public\/$/", '', $url.'/')], $content);
        });
        // 如果未设置cdnurl则自动匹配得出
        if (!Config::get('site.cdnurl')) {
            Config::set(['cdnurl' => $url], 'site');
        }
        // 如果未设置cdnurl则自动匹配得出
        if (!Config::get('upload.cdnurl')) {
            Config::set(['cdnurl' => $url], 'upload');
        }
        if (Env::get('APP_DEBUG')) {
            // 如果是调试模式将version置为当前的时间戳可避免缓存
            Config::set(['version' => time()], 'site');
            //如果是调试模式将关闭视图缓存
            Config::set(['tpl_cache' => false], 'view');
            // 如果是开发模式那么将异常模板修改成官方的
            Config::set(['exception_tmpl' => app()->getThinkPath().'tpl/think_exception.tpl']);
        }
        // 切换多语言
        if ($request->get('lang')) {
            \think\facade\Cookie::set('think_var', $request->get('lang'));
        }
        // Form别名
        if (!class_exists('Form')) {
            class_alias('fast\\Form', 'Form');
        }

        return $next($request);
    }
}
