<?php

namespace app\index\controller;

use think\facade\Lang;
use app\api\controller\Common;
use app\common\controller\Frontend;

/**
 * Ajax异步请求接口.
 *
 * @internal
 */
class Ajax extends Frontend
{
    protected $noNeedLogin = ['lang'];
    protected $noNeedRight = ['*'];
    protected $layout = '';

    /**
     * 加载语言包.
     */
    public function lang()
    {
        header('Content-Type: application/javascript');
        $controllername = input('controllername');
        $this->loadlang($controllername);
        //强制输出JSON Object
        $result = jsonp(Lang::get(), 200, [], ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);

        return $result;
    }

    /**
     * 上传文件.
     */
    public function upload()
    {
        return (new Common(app()))->upload();
    }
}
