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
class User extends Model
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

    /**
     * 获取个人URL.
     *
     * @param  string  $value
     * @param  array   $data
     *
     * @return string
     */
    public function getUrlAttr($value, $data)
    {
        return '/u/'.$data['id'];
    }

    /**
     * 获取头像.
     *
     * @param  string  $value
     * @param  array   $data
     *
     * @return string
     */
    public function getAvatarAttr($value, $data)
    {
        if (!$value) {
            //如果不需要启用首字母头像，请使用
            //$value = '/assets/img/avatar.png';
            $value = letter_avatar($data['nickname']);
        }

        return $value;
    }

    /**
     * 获取会员的组别.
     */
    public function getGroupAttr($value, $data)
    {
        return UserGroup::find($data['group_id']);
    }

    /**
     * 获取验证字段数组值
     *
     * @param  string  $value
     * @param  array   $data
     *
     * @return object
     */
    public function getVerificationAttr($value, $data)
    {
        $value = array_filter((array) json_decode($value, true));
        $value = array_merge(['email' => 0, 'mobile' => 0], $value);

        return (object) $value;
    }

    /**
     * 设置验证字段.
     *
     * @param  mixed  $value
     *
     * @return string
     */
    public function setVerificationAttr($value)
    {
        $value = is_object($value) || is_array($value) ? json_encode($value) : $value;

        return $value;
    }

    /**
     * 变更会员余额.
     *
     * @param  int     $money    余额
     * @param  int     $user_id  会员ID
     * @param  string  $memo     备注
     */
    public static function money($money, $user_id, $memo)
    {
        $user = self::find($user_id);
        if ($user && $money != 0) {
            $before = $user->money;
            $after  = $user->money + $money;
            //更新会员信息
            $user->save(['money' => $after]);
            //写入日志
            MoneyLog::create([
                'user_id' => $user_id,
                'money'   => $money,
                'before'  => $before,
                'after'   => $after,
                'memo'    => $memo,
            ]);
        }
    }

    /**
     * 变更会员积分.
     *
     * @param  int     $score    积分
     * @param  int     $user_id  会员ID
     * @param  string  $memo     备注
     */
    public static function score($score, $user_id, $memo)
    {
        $user = self::find($user_id);
        if ($user && $score != 0) {
            $before = $user->score;
            $after  = $user->score + $score;
            $level  = self::nextlevel($after);
            //更新会员信息
            $user->save(['score' => $after, 'level' => $level]);
            //写入日志
            ScoreLog::create([
                'user_id' => $user_id,
                'score'   => $score,
                'before'  => $before,
                'after'   => $after,
                'memo'    => $memo,
            ]);
        }
    }

    /**
     * 根据积分获取等级.
     *
     * @param  int  $score  积分
     *
     * @return int
     */
    public static function nextlevel($score = 0)
    {
        $lv    = [
            1  => 0,
            2  => 30,
            3  => 100,
            4  => 500,
            5  => 1000,
            6  => 2000,
            7  => 3000,
            8  => 5000,
            9  => 8000,
            10 => 10000,
        ];
        $level = 1;
        foreach ($lv as $key => $value) {
            if ($score >= $value) {
                $level = $key;
            }
        }

        return $level;
    }
}
