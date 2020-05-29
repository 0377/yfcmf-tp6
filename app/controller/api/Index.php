<?php
namespace app\controller\api;



class Index
{
    public function index()
    {
        return 'api index';
    }
    public function index2()
    {
        return 'b';
    }
    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
