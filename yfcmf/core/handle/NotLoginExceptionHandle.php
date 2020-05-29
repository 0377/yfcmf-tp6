<?php


namespace yfcmf\core\handle;


use Throwable;
use yfcmf\core\exception\NotLoginException;
use yfcmf\core\traits\JumpTraits;

class NotLoginExceptionHandle extends ExceptionHandle
{
    use JumpTraits;

    public function handle(Throwable $throwable, \think\Request $request): bool
    {
        $this->error($throwable->getMessage(), url('user/login'));
        // 交给下一个异常处理器
        return false;
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof NotLoginException;
    }
}