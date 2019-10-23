<?php
/**
 * *
 *  * ============================================================================
 *  * Created by PhpStorm.
 *  * User: Ice
 *  * 邮箱: ice@sbing.vip
 *  * 网址: https://sbing.vip
 *  * Date: 2019/9/19 下午3:20
 *  * ============================================================================.
 */

namespace app\common\controller;

use think\facade\Lang;
use think\facade\View;
use think\facade\Event;
use think\facade\Config;
use app\common\library\Auth;

/**
 * 前台控制器基类.
 */
class Frontend extends BaseController
{
    /**
     * 布局模板
     *
     * @var string
     */
    protected $layout = '';

    /**
     * 无需登录的方法,同时也就不需要鉴权了.
     *
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录.
     *
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 权限Auth.
     *
     * @var Auth
     */
    protected $auth = null;

    public function _initialize()
    {
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');
        $modulename = app()->http->getName();
        $controllername = strtolower($this->request->controller());
        $actionname = strtolower($this->request->action());

        // 如果有使用模板布局
        if ($this->layout) {
            View::engine()->layout('layout/'.$this->layout);
        }
        $this->auth = app()->auth;

        // token
        $token = $this->request->server('HTTP_TOKEN',
            $this->request->request('token', \think\facade\Cookie::get('token')) ?: '');

        $path = str_replace('.', '/', $controllername).'/'.$actionname;
        // 设置当前请求的URI
        $this->auth->setRequestUri($path);
        // 检测是否需要验证登录
        if (! $this->auth->match($this->noNeedLogin)) {
            //初始化
            $this->auth->init($token);
            //检测是否登录
            if (! $this->auth->isLogin()) {
                $this->error(__('Please login first'), 'index/user/login');
            }
            // 判断是否需要验证权限
            if (! $this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法判断是否有对应权限
                if (! $this->auth->check($path)) {
                    $this->error(__('You have no permission'));
                }
            }
        } else {
            // 如果有传递token才验证是否登录状态
            if ($token) {
                $this->auth->init($token);
            }
        }

        $this->view->assign('user', $this->auth->getUser());

        // 语言检测
        $lang = strip_tags(Lang::getLangSet());

        $site = Config::get('site');

        $upload = \app\common\model\Config::upload();

        // 上传信息配置后
        Event::trigger('upload_config_init', $upload);

        // 配置信息
        $config = [
            'site'           => array_intersect_key($site,
                array_flip(['name', 'cdnurl', 'version', 'timezone', 'languages'])),
            'upload'         => $upload,
            'modulename'     => $modulename,
            'controllername' => $controllername,
            'actionname'     => $actionname,
            'jsname'         => 'frontend/'.str_replace('.', '/', $controllername),
            'moduleurl'      => rtrim(url("/{$modulename}", [], false), '/'),
            'language'       => $lang,
        ];

        Config::set(array_merge(Config::get('upload'), $upload), 'upload');

        // 配置信息后
        Event::trigger('config_init', $config);
        // 加载当前控制器语言包
        $this->loadlang($this->request->controller());
        $this->assign('site', $site);
        $this->assign('config', $config);
    }

    /**
     * 加载语言文件.
     *
     * @param string $name
     */
    protected function loadlang($name)
    {
        Lang::load(app()->getAppPath().'/lang/'.Lang::getLangset().'/'.str_replace('.', '/',
                strtolower($name)).'.php');
    }

    /**
     * 渲染配置信息.
     *
     * @param mixed $name  键名或数组
     * @param mixed $value 值
     */
    protected function assignconfig($name, $value = '')
    {
        $this->view->config = array_merge($this->view->config ? $this->view->config : [],
            is_array($name) ? $name : [$name => $value]);
    }
}
