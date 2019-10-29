<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;

/**
 * 会员管理.
 *
 * @icon fa fa-user
 */
class User extends Backend
{
    protected $relationSearch = true;

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\User();
    }

    /**
     * 查看.
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            [$where, $sort, $order, $offset, $limit] = $this->buildparams();
            $total = $this->model
                ->withJoin('group')
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->withJoin('group')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v) {
                $v->hidden(['password', 'salt']);
            }
            $result = ['total' => $total, 'rows' => $list];

            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * 编辑.
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (! $row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign('groupList',
            build_select('row[group_id]', \app\admin\model\UserGroup::column('name', 'id'), $row['group_id'],
                ['class' => 'form-control selectpicker']));

        return parent::edit($ids);
    }
}
