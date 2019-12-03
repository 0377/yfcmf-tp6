<?php
/**
 * *
 *  * ============================================================================
 *  * Created by PhpStorm.
 *  * User: Ice
 *  * 邮箱: ice@sbing.vip
 *  * 网址: https://sbing.vip
 *  * Date: 2019/9/19 下午3:33
 *  * ============================================================================.
 */

declare(strict_types=1);

namespace app\common\controller;

use think\App;
use think\Validate;
use think\facade\View;
use app\common\library\traits\Jump;
use think\exception\ValidateException;

/**
 * 控制器基础类.
 */
abstract class BaseController
{
    /**
     * Request实例.
     *
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例.
     *
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     *
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件.
     *
     * @var array
     */
    protected $middleware = [];

    protected $view;

    use Jump;

    /**
     * 构造方法.
     *
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        app()->http->setBind(true);
        $this->request = $this->app->request;
        $this->view = $this->app->view;
        // 控制器初始化
        $this->_initialize();
    }

    protected function assign($name, $value = null)
    {
        View::assign($name, $value);
    }

    // 初始化
    protected function _initialize()
    {
    }

    /**
     * 验证数据.
     *
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
     *
     * @throws ValidateException
     *
     * @return array|string|true
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v = new $class();
            if (! empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }
}
