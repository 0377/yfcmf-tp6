<?php
namespace app\controller\admin;



class Index
{
    public function login()
    {
        return 'admin  index login';
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
