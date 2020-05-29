<?php

namespace app\controller\index;

use think\facade\Lang;
use yfcmf\core\Controller;

/**
 * Ajax异步请求接口.
 *
 * @internal
 */
class Ajax extends Controller
{
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
