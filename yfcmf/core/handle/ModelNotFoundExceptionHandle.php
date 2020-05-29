<?php


namespace yfcmf\core\handle;

use Throwable;
use yfcmf\core\traits\JumpTraits;
use think\db\exception\ModelNotFoundException;


class ModelNotFoundExceptionHandle extends ExceptionHandle
{
    use JumpTraits;

    public function handle(Throwable $throwable, \think\Request $request): bool
    {
        $this->error(__('No results were found'));
        // 交给下一个异常处理器
        return false;
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ModelNotFoundException;
    }
}