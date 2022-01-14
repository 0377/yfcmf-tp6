<?php
/**
 * *
 *  * ============================================================================
 *  * Created by PhpStorm.
 *  * User: Ice
 *  * 邮箱: ice@sbing.vip
 *  * 网址: https://sbing.vip
 *  * Date: 2019/9/19 下午3:52
 *  * ============================================================================.
 */

return [
    \think\middleware\LoadLangPack::class,
    \think\middleware\SessionInit::class,
    app\common\middleware\FastInit::class,
];
