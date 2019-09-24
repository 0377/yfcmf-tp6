<?php
/**
 * *
 *  * ============================================================================
 *  * Created by PhpStorm.
 *  * User: Ice
 *  * 邮箱: ice@sbing.vip
 *  * 网址: https://sbing.vip
 *  * Date: 2019/9/19 下午3:53
 *  * ============================================================================
 *
 */
declare (strict_types=1);

namespace app\common\middleware;

use Closure;
use think\facade\Env;
use think\facade\Event;
use think\facade\View;
use think\Request;
use think\Response;
use think\facade\Config;

/**
 * Fast初始化
 */
class Addon
{

    /**
     * 插件中间件
     * @access publi
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
