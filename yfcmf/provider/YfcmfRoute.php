<?php

namespace yfcmf\provider;

use think\Route;
use yfcmf\library\Common;

/**
 * 路由控制
 *
 * @package yfcmf\provider
 */
class YfcmfRoute extends Route
{
    /**
     * 获取当前请求URL的pathinfo信息(不含URL后缀)
     *
     * @access protected
     * @return string
     */
    protected function path(): string
    {
        $path    = parent::path();
        $runFile = Common::getPhpFile();
        if ($runFile === 'index') {
            if (strpos($path, 'api/') === 0) {
                $path = 'api.'.substr($path, 4);
            } else {
                $path = $path ? 'index.'.$path : 'index.index/index';
            }
        } elseif (defined('YFCMF_ADMIN') && YFCMF_ADMIN == true) {
            $path = $path !== $runFile.'.php' ? 'admin.'.$path : 'admin.index/index';
        }

        return $path;
    }


}