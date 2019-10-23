<?php
/**
 * 用法：
 * load_trait('controller/Jump');
 * class index
 * {
 *     use \traits\controller\Jump;
 *     public function index(){
 *         $this->error();
 *         $this->redirect();
 *     }
 * }.
 */

namespace app\common\library\traits;

use think\Response;
use think\facade\Config;
use think\facade\Request;
use think\response\Redirect;
use think\facade\View as ViewTemplate;
use think\exception\HttpResponseException;

trait Jump
{
    /**
     * 操作成功跳转的快捷方法.
     *
     * @param mixed  $msg    提示信息
     * @param string $url    跳转的 URL 地址
     * @param mixed  $data   返回的数据
     * @param int    $wait   跳转等待时间
     * @param array  $header 发送的 Header 信息
     *
     * @throws \Exception
     */
    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        if (is_null($url) && ! is_null(Request::server('HTTP_REFERER'))) {
            $url = Request::server('HTTP_REFERER');
        } elseif ('' !== $url && ! strpos($url, '://') && 0 !== strpos($url, '/')) {
            $url = url($url);
        }

        $type = $this->getResponseType();
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        if ('html' == strtolower($type)) {
            $result = ViewTemplate::fetch(Config::get('app.dispatch_success_tmpl'), $result);
        }

        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法.
     *
     * @param mixed  $msg    提示信息
     * @param string $url    跳转的 URL 地址
     * @param mixed  $data   返回的数据
     * @param int    $wait   跳转等待时间
     * @param array  $header 发送的 Header 信息
     *
     * @throws \Exception
     */
    protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        if (is_null($url)) {
            $url = Request::isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ('' !== $url && ! strpos($url, '://') && 0 !== strpos($url, '/')) {
            $url = url($url);
        }

        $type = $this->getResponseType();
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        if ('html' == strtolower($type)) {
            $result = ViewTemplate::fetch(Config::get('app.dispatch_success_tmpl'), $result);
        }
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }

    /**
     * 返回封装后的 API 数据到客户端.
     *
     * @param mixed  $data   要返回的数据
     * @param int    $code   返回的 code
     * @param mixed  $msg    提示信息
     * @param string $type   返回数据格式
     * @param array  $header 发送的 Header 信息
     */
    protected function result($data, $code = 0, $msg = '', $type = '', array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => Request::server('REQUEST_TIME'),
            'data' => $data,
        ];
        $type = $type ?: $this->getResponseType();
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }

    /**
     * URL 重定向.
     *
     * @param string    $url    跳转的 URL 表达式
     * @param array|int $params 其它 URL 参数
     * @param int       $code   http code
     * @param array     $with   隐式传参
     */
    protected function redirect($url, $params = [], $code = 302, $with = [])
    {
        if (is_int($params)) {
            $code = $params;
        }
        $response = \redirect($url);
        $response->code($code)->with($with);

        throw new HttpResponseException($response);
    }

    /**
     * 获取当前的 response 输出类型.
     *
     * @return string
     */
    protected function getResponseType()
    {
        return Request::isAjax()
            ? Config::get('app.default_ajax_return')
            : Config::get('app.default_return_type');
    }
}
