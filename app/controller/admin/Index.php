<?php
namespace app\controller\admin;



use think\facade\View;
use yfcmf\core\annotation\NeedAuth;
use yfcmf\core\annotation\NeedLogin;
use yfcmf\core\Controller;

class Index extends Controller
{
    public function login()
    {
        View::engine()->layout('admin/layout/blank');
        return $this->view->fetch();
    }

    /**
     * @NeedAuth(true)
     * @NeedLogin(true)
     */
    public function index()
    {
        halt($this->request->originController());
        return $this->view->fetch();
    }
    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
