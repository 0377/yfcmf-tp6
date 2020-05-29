<?php

declare(strict_types=1);

namespace yfcmf\core\handle;

use Throwable;


/**
 * 应用异常处理类.
 */
abstract class ExceptionHandle
{

    /**
     * Handle the exception, and return the specified result.
     *
     * @param  Throwable       $throwable
     * @param  \think\Request  $request
     *
     * @return bool
     */
    abstract public function handle(Throwable $throwable, \think\Request $request):bool ;

    /**
     * Determine if the current exception handler should handle the exception,.
     *
     * @param  Throwable  $throwable
     *
     * @return bool
     *              If return true, then this exception handler will handle the exception,
     *              If return false, then delegate to next handler
     */
    abstract public function isValid(Throwable $throwable): bool;
}
