<?php

use think\facade\Lang;

if (!function_exists('cdnurl')) {

    /**
     * 获取上传资源的CDN的地址
     *
     * @param  string  $url     资源相对地址
     * @param  bool    $domain  是否显示域名 或者直接传入域名
     *
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        $url   = preg_match($regex, $url) ? $url : \think\facade\Config::get('upload.cdnurl').$url;
        if ($domain && !preg_match($regex, $url)) {
            $domain = is_bool($domain) ? request()->domain() : $domain;
            $url    = $domain.$url;
        }

        return $url;
    }
}

if (!function_exists('__')) {

    /**
     * 获取语言变量值
     *
     * @param  string  $name  语言变量名
     * @param  array   $vars  动态变量值
     * @param  string  $lang  语言
     *
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }

        return Lang::get($name, $vars, $lang);
    }
}