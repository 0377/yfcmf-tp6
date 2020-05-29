<?php

namespace app\controller\index;

use yfcmf\core\annotation\NeedAuth;
use yfcmf\core\annotation\NeedLogin;
use yfcmf\core\Controller;

/**
 * Class Index
 *
 * @package app\controller\index
 */
class Index extends Controller
{

    /**
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        \app\model\User::where('id',111)->findOrFail();
        return $this->view->fetch();
    }

    /**
     * @NeedAuth(true)
     * @NeedLogin(true)
     * @return \think\response\Jsonp
     */
    public function news()
    {
        //halt($this->auth->id);
        $newslist = [];

        return jsonp(['newslist' => $newslist, 'new' => count($newslist), 'url' => 'https://www.iuok.cn?ref=news']);
    }
}
