<?php

namespace app\admin\model;

use fast\Tree;
use app\common\model\BaseModel;
use think\Model;

class UserRule extends BaseModel
{
    // 表名
    protected $name = 'user_rule';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'status_text',
    ];
    public static function onAfterWrite(Model $row)
    {
        $pk = $row->getPk();
        $row->where($pk, $row[$pk])->save(['weigh' => $row[$pk]]);
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();

        return isset($list[$value]) ? $list[$value] : '';
    }

    public static function getTreeList($selected = [])
    {
        $ruleList = self::where('status', 'normal')->order('weigh desc,id desc')->select()->toArray();
        $nodeList = [];
        Tree::instance()->init($ruleList);
        $ruleList = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
        $hasChildrens = [];
        foreach ($ruleList as $k => $v) {
            if ($v['haschild']) {
                $hasChildrens[] = $v['id'];
            }
        }
        foreach ($ruleList as $k => $v) {
            $state = ['selected' => in_array($v['id'], $selected) && ! in_array($v['id'], $hasChildrens)];
            $nodeList[] = ['id'          => $v['id'],
                                'parent' => $v['pid'] ? $v['pid'] : '#',
                                'text'   => __($v['title']),
                                'type'   => 'menu',
                                'state'  => $state,
            ];
        }

        return $nodeList;
    }
}
