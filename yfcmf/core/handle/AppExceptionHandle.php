<?php

namespace yfcmf\core\handle;

use Throwable;
use think\Response;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\exception\HttpResponseException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use yfcmf\core\exception\NotAuthException;
use yfcmf\core\exception\NotLoginException;
use yfcmf\core\traits\JumpTraits;
use yfcmf\provider\YfcmfRequest;

/**
 * 应用异常处理类.
 */
class AppExceptionHandle extends Handle
{
    use JumpTraits;
    /**
     * 不需要记录信息（日志）的异常类列表.
     *
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
        NotLoginException::class,
        NotAuthException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）.
     *
     * @param  Throwable  $exception
     *
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * @param  YfcmfRequest  $request
     * @param  Throwable     $e
     *
     * @return Response
     * @throws \Exception
     */
    public function render($request, Throwable $e): Response
    {
        $module     = $request->getModule();
        $exceptions = config('exceptions');
        if (isset($exceptions[$module])) {
            foreach ($exceptions[$module] as $class) {
                /** @var ExceptionHandle $handler */
                $handler = $this->app->make($class);
                if ($handler->isValid($e)) {
                    if (!$handler->handle($e, $request)) {
                        break;
                    }
                }
            }
        }
        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}
