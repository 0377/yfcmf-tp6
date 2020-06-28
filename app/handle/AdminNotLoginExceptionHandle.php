<?php
/**
 *  =============================================================
 *  Created by PhpStorm.
 *  User: Ice
 *  邮箱: ice@sbing.vip
 *  网址: https://sbing.vip
 *  Date: 2020/6/28 下午12:07
 *  ==============================================================
 */

namespace app\handle;


use Throwable;
use yfcmf\core\exception\NotLoginException;
use yfcmf\core\handle\ExceptionHandle;
use yfcmf\core\traits\JumpTraits;

/**
 * 后台登陆处理
 *
 * @package app\handle
 */
class AdminNotLoginExceptionHandle extends ExceptionHandle
{
    use JumpTraits;
    public function handle(Throwable $throwable, \think\Request $request): bool
    {
        $this->error($throwable->getMessage(), url('index/login'));
        // 交给下一个异常处理器
        return false;
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof NotLoginException;
    }
}