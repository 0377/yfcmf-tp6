<?php

namespace app\admin\controller\example;

use app\common\controller\Backend;

/**
 * 自定义表单示例
 *
 * @icon   fa fa-table
 * @remark FastAdmin支持在控制器间跳转,点击后将切换到另外一个TAB中,无需刷新当前页面
 */
class Customform extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('AdminLog');
    }

    public function index()
    {
        if ($this->request->isPost()) {
            $this->success("提交成功", null, ['data' => json_encode($this->request->post("row/a"), JSON_UNESCAPED_UNICODE)]);
        }
        return $this->view->fetch();
    }
}
