<?php

namespace app\model;

use think\Model;

/**
 * Class app\model\UserRule
 *
 * @property bool   $ismenu     是否菜单
 * @property int    $id
 * @property int    $pid        父ID
 * @property int    $weigh      权重
 * @property string $createtime 创建时间
 * @property string $name       名称
 * @property string $remark     备注
 * @property string $status     状态
 * @property string $title      标题
 * @property string $updatetime 更新时间
 */
class AdminRule extends Model
{
    // 表名
    protected $name = 'admin_rule';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];
}
