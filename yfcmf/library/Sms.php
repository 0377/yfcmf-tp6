<?php
/**
 * *
 *  * ============================================================================
 *  * Created by PhpStorm.
 *  * User: Ice
 *  * 邮箱: ice@sbing.vip
 *  * 网址: https://sbing.vip
 *  * Date: 2019/9/19 下午4:21
 *  * ============================================================================.
 */

namespace app\common\library;

use think\facade\Event;

/**
 * 短信验证码类.
 */
class Sms
{
    /**
     * 验证码有效时长
     *
     * @var int
     */
    protected static $expire = 120;

    /**
     * 最大允许检测的次数.
     *
     * @var int
     */
    protected static $maxCheckNums = 10;

    /**
     * 获取最后一次手机发送的数据.
     *
     * @param int    $mobile 手机号
     * @param string $event  事件
     *
     * @return Sms
     */
    public static function get($mobile, $event = 'default')
    {
        $sms = \app\common\model\Sms::
        where(['mobile' => $mobile, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        Event::trigger('sms_get', $sms, true);

        return $sms ? $sms : null;
    }

    /**
     * 发送验证码
     *
     * @param int    $mobile 手机号
     * @param int    $code   验证码,为空时将自动生成4位数字
     * @param string $event  事件
     *
     * @return bool
     */
    public static function send($mobile, $code = null, $event = 'default')
    {
        $code = is_null($code) ? mt_rand(1000, 9999) : $code;
        $time = time();
        $ip = request()->ip();
        $sms = \app\common\model\Sms::create(['event' => $event, 'mobile' => $mobile, 'code' => $code, 'ip' => $ip, 'createtime' => $time]);
        $result = Event::trigger('sms_send', $sms,true);
        if (! $result) {
            $sms->delete();

            return false;
        }

        return true;
    }

    /**
     * 发送通知.
     *
     * @param mixed  $mobile   手机号,多个以,分隔
     * @param string $msg      消息内容
     * @param string $template 消息模板
     * @param array  $data     模板变量
     *
     * @return bool
     */
    public static function notice($mobile, $msg = '', $template = null, $data = [])
    {
        $params = [
            'mobile'   => $mobile,
            'msg'      => $msg,
            'template' => $template,
            'data'     => $data,
        ];
        $result = Event::trigger('sms_notice', $params, true);

        return $result ? true : false;
    }

    /**
     * 校验验证码
     *
     * @param int    $mobile 手机号
     * @param int    $code   验证码
     * @param string $event  事件
     *
     * @return bool
     */
    public static function check($mobile, $code, $event = 'default')
    {
        $time = time() - self::$expire;
        $sms = \app\common\model\Sms::where(['mobile' => $mobile, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        if ($sms) {
            if ($sms['createtime'] > $time && $sms['times'] <= self::$maxCheckNums) {
                $correct = $code == $sms['code'];
                if (! $correct) {
                    $sms->times = $sms->times + 1;
                    $sms->save();

                    return false;
                } else {
                    $result = Event::trigger('sms_check', $sms, true);

                    return $result;
                }
            } else {
                // 过期则清空该手机验证码
                self::flush($mobile, $event);

                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 清空指定手机号验证码
     *
     * @param int    $mobile 手机号
     * @param string $event  事件
     *
     * @return bool
     */
    public static function flush($mobile, $event = 'default')
    {
        \app\common\model\Sms::
        where(['mobile' => $mobile, 'event' => $event])
            ->delete();
        Event::trigger('sms_flush');

        return true;
    }
}
