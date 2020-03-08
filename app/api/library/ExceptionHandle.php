<?php

namespace app\api\library;

use Throwable;
use think\Response;
use think\facade\Env;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\exception\HttpResponseException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;

/**
 * 自定义API模块的错误显示.
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表.
     *
     * @var array
     */
    protected $ignoreReport = [
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
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
     * Render an exception into an HTTP response.
     *
     * @param  \think\Request  $request
     * @param  Throwable  $e
     *
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 在生产环境下返回code信息
        if (! Env::get('app_debug')) {
            if ($e instanceof HttpResponseException) {
                return $e->getResponse();
            }
            $statuscode = $code = 500;
            $msg = 'An error occurred';
            // 验证异常
            if ($e instanceof ValidateException) {
                $code = 0;
                $statuscode = 200;
                $msg = $e->getError();
            }
            // Http异常
            if ($e instanceof HttpException) {
                $statuscode = $code = $e->getStatusCode();
            }

            return json(['code' => $code, 'msg' => $msg, 'time' => time(), 'data' => null], $statuscode);
        }
        //其它此交由系统处理
        if (request()->isJson()) {
            if ($e instanceof HttpResponseException || $e instanceof HttpException) {
                return parent::render($request, $e);
            } else {
                $response = parent::render($request, $e);
                $data = $response->getData();
                if (isset($data['tables']['Environment Variables'])) {
                    unset($data['tables']['Environment Variables']);
                    $response->data($data);
                }
                return $response;
            }
        }
        return parent::render($request, $e);
    }
}
