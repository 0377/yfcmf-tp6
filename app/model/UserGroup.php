<?php

namespace app\model;

use think\Model;

/**
 * Class app\model\UserGroup
 *
 * @property int    $id
 * @property string $createtime 添加时间
 * @property string $name       组名
 * @property string $rules      权限节点
 * @property string $status     状态
 * @property string $updatetime 更新时间
 */
class UserGroup extends Model
{
    // 表名
    protected $name = 'user_group';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];
}
