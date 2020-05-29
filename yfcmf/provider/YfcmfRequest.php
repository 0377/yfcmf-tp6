<?php

namespace yfcmf\provider;

use think\Exception;
use yfcmf\library\Common;

/**
 * 应用请求对象类
 *
 * @package yfcmf\provider
 */
class YfcmfRequest extends \think\Request
{
    protected $filter = 'trim,strip_tags,htmlspecialchars';
    protected $originController = '';

    public function getModule()
    {
        if (Common::getPhpFile() === 'index') {
            if ($this->originController) {
                if (strpos($this->originController, 'api.') === 0) {
                    return 'api';
                } else {
                    return 'index';
                }
            }
        } elseif (defined('YFCMF_ADMIN') && YFCMF_ADMIN == true) {
            return 'admin';
        }
        throw new Exception('module error');
    }

    /**
     * 获取当前的控制器名
     *
     * @access public
     *
     * @param  bool  $convert  转换为小写
     *
     * @return string
     */
    public function controller(bool $convert = false): string
    {
        $controller             = parent::controller();
        $this->originController = $controller;
        if (Common::getPhpFile() === 'index') {
            if (strpos($controller, 'index.') === 0) {
                return substr($controller, 6);
            }
        }
        return $controller;
    }

    /**
     * 设置当前的控制器名
     *
     * @access public
     *
     * @param  string  $controller  控制器名
     *
     * @return $this
     */
    public function setController(string $controller)
    {
        $this->controller       = $controller;
        $this->originController = $controller;
        return $this;
    }

    /**
     * 获取真实控制器路径
     *
     * @return string
     */
    public function originController(): string
    {
        return $this->originController;
    }

    /**
     * 获取环境变量【不允许全部获取】
     *
     * @access public
     *
     * @param  string  $name     数据名称
     * @param  string  $default  默认值
     *
     * @return mixed
     */
    public function env(string $name = '', string $default = null)
    {
        if (empty($name)) {
            return [];
        } else {
            $name = strtoupper($name);
        }

        return $this->env->get($name, $default);
    }
}
