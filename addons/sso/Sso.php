<?php

namespace addons\sso;

use think\Addons;

/**
 * 单点登陆
 */
class Sso extends Addons
{

    /**
     * 插件安装方法
     *
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件卸载方法
     *
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 插件启用方法
     *
     * @return bool
     */
    public function enable()
    {
        return true;
    }

    /**
     * 插件禁用方法
     *
     * @return bool
     */
    public function disable()
    {
        return true;
    }

}
