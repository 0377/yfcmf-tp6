<?php

namespace app\model;

use think\Model;

/**
 * 短信验证码
 *
 * @property int    $id         ID
 * @property int    $times      验证次数
 * @property string $code       验证码
 * @property string $createtime 创建时间
 * @property string $event      事件
 * @property string $ip         IP
 * @property string $mobile     手机号
 */
class Sms extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];
}
