<?php


namespace yfcmf\core\handle;


use Throwable;
use yfcmf\core\exception\NotAuthException;
use yfcmf\core\traits\JumpTraits;

class NotAuthExceptionHandle extends ExceptionHandle
{
    use JumpTraits;

    public function handle(Throwable $throwable, \think\Request $request): bool
    {
        $this->error($throwable->getMessage());
        // 交给下一个异常处理器
        return false;
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof NotAuthException;
    }
}