<?php

namespace app\model;

use think\Model;

/**
 * 会员积分日志模型.
 *
 * @property int    $after      变更后积分
 * @property int    $before     变更前积分
 * @property int    $id
 * @property int    $score      变更积分
 * @property int    $user_id    会员ID
 * @property string $createtime 创建时间
 * @property string $memo       备注
 */
class ScoreLog extends Model
{
    // 表名
    protected $name = 'user_score_log';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];
}
