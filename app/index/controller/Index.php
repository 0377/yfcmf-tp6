<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\facade\Db;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        $url=addon_url('example/demo/demo2',['name'=>1]);
        dump($url);
        $url=url('/example/d2/[:name]',['name'=>1])->build();
        dump($url);
        //halt(app()->route);
        return $this->view->fetch();
    }

    public function news()
    {
        $newslist = [];
        return jsonp(['newslist' => $newslist, 'new' => count($newslist), 'url' => 'https://www.fastadmin.net?ref=news']);
    }

}
