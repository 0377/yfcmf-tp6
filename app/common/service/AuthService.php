<?php
/**
 * *
 *  * ============================================================================
 *  * Created by PhpStorm.
 *  * User: Ice
 *  * 邮箱: ice@sbing.vip
 *  * 网址: https://sbing.vip
 *  * Date: 2019/9/20 下午5:19
 *  * ============================================================================.
 */

namespace app\common\service;

use think\Service;
use app\common\library\Auth;

/**
 * 认证服务
 */
class AuthService extends Service
{
    public function register()
    {
        $this->app->bind('auth', Auth::class);
    }
}
