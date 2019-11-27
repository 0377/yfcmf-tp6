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
use think\facade\Config;

/**
 * 插件中间件.
 */
class Addon
{
    /**
     * 插件中间件.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        $tpl_replace_string = Config::get('view.tpl_replace_string');
        // 设置替换字符串
        $cdnurl = Config::get('site.cdnurl');
        $addon = $request->param('addon')?$request->param('addon'):'';
        $tpl_replace_string['__ADDON__'] = $cdnurl . "/assets/addons/" . $addon;
        Config::set(['tpl_replace_string'=>$tpl_replace_string],'view');
        Config::set($tpl_replace_string,'view_replace_str');

        hook('addon_middleware', $request);

        return $next($request);
    }
}
