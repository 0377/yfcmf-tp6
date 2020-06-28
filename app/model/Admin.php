<?php

namespace app\model;

use think\Model;

/**
 * 会员模型.
 *
 * @property bool        $gender         性别
 * @property bool        $level          等级
 * @property bool        $loginfailure   失败次数
 * @property float       $money          余额
 * @property int         $group_id       组别ID
 * @property int         $id             ID
 * @property int         $jointime       加入时间
 * @property int         $logintime      登录时间
 * @property int         $maxsuccessions 最大连续登录天数
 * @property int         $prevtime       上次登录时间
 * @property int         $score          积分
 * @property int         $successions    连续登录天数
 * @property object      $verification   验证
 * @property string      $avatar         头像
 * @property string      $bio            格言
 * @property string      $birthday       生日
 * @property string      $createtime     创建时间
 * @property string      $email          电子邮箱
 * @property string      $joinip         加入IP
 * @property string      $loginip        登录IP
 * @property string      $mobile         手机号
 * @property string      $nickname       昵称
 * @property string      $password       密码
 * @property string      $salt           密码盐
 * @property string      $status         状态
 * @property string      $token          Token
 * @property string      $updatetime     更新时间
 * @property string      $username       用户名
 * @property-read mixed  $group
 * @property-read string $url
 */
class Admin extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url',
    ];

}
