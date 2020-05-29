<?php

namespace app\model;

use think\Model;

/**
 * 会员余额日志模型.
 *
 * @property float  $after      变更后余额
 * @property float  $before     变更前余额
 * @property float  $money      变更余额
 * @property int    $id
 * @property int    $user_id    会员ID
 * @property string $createtime 创建时间
 * @property string $memo       备注
 */
class MoneyLog extends Model
{
    // 表名
    protected $name = 'user_money_log';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];
}
