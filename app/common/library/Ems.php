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
 * 邮箱验证码类.
 */
class Ems
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
     * 获取最后一次邮箱发送的数据.
     *
     * @param  int  $email  邮箱
     * @param  string  $event  事件
     *
     * @return Ems
     */
    public static function get($email, $event = 'default')
    {
        $ems = \app\common\model\Ems::
        where(['email' => $email, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        Event::trigger('ems_get', $ems, true);

        return $ems ? $ems : null;
    }

    /**
     * 发送验证码
     *
     * @param  int  $email  邮箱
     * @param  int  $code  验证码,为空时将自动生成4位数字
     * @param  string  $event  事件
     *
     * @return bool
     */
    public static function send($email, $code = null, $event = 'default')
    {
        $code = is_null($code) ? mt_rand(1000, 9999) : $code;
        $time = time();
        $ip = request()->ip();
        $ems = \app\common\model\Ems::create([
            'event'      => $event, 'email' => $email, 'code' => $code, 'ip' => $ip,
            'createtime' => $time,
        ]);
        $result = Event::trigger('ems_send', $ems, true);
        if (! $result) {
            $ems->delete();

            return false;
        }

        return true;
    }

    /**
     * 发送通知.
     *
     * @param  mixed  $email  邮箱,多个以,分隔
     * @param  string  $msg  消息内容
     * @param  string  $template  消息模板
     *
     * @return bool
     */
    public static function notice($email, $msg = '', $template = null)
    {
        $params = [
            'email'    => $email,
            'msg'      => $msg,
            'template' => $template,
        ];
        $result = Event::trigger('ems_notice', $params, true);

        return $result ? true : false;
    }

    /**
     * 校验验证码
     *
     * @param  int  $email  邮箱
     * @param  int  $code  验证码
     * @param  string  $event  事件
     *
     * @return bool
     */
    public static function check($email, $code, $event = 'default')
    {
        $time = time() - self::$expire;
        $ems = \app\common\model\Ems::where(['email' => $email, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        if ($ems) {
            if ($ems['createtime'] > $time && $ems['times'] <= self::$maxCheckNums) {
                $correct = $code == $ems['code'];
                if (! $correct) {
                    $ems->times = $ems->times + 1;
                    $ems->save();

                    return false;
                } else {
                    $result = Event::trigger('ems_check', $ems, true);

                    return true;
                }
            } else {
                // 过期则清空该邮箱验证码
                self::flush($email, $event);

                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 清空指定邮箱验证码
     *
     * @param  int  $email  邮箱
     * @param  string  $event  事件
     *
     * @return bool
     */
    public static function flush($email, $event = 'default')
    {
        \app\common\model\Ems::
        where(['email' => $email, 'event' => $event])
            ->delete();
        Event::trigger('ems_flush');

        return true;
    }
}
