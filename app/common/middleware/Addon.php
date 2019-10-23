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
        hook('addon_middleware', $request);

        return $next($request);
    }
}
