<?php
/**
 * *
 *  * ============================================================================
 *  * Created by PhpStorm.
 *  * User: Ice
 *  * 邮箱: ice@sbing.vip
 *  * 网址: https://sbing.vip
 *  * Date: 2019/9/19 下午3:37
 *  * ============================================================================.
 */

return [
    //是否开启前台会员中心
    'usercenter' => true,
    //会员注册验证码类型email/mobile/wechat/text/false
    'user_register_captcha' => 'text',
    //登录验证码
    'login_captcha' => true,
    //登录失败超过10次则1天后重试
    'login_failure_retry' => true,
    //是否同一账号同一时间只能在一个地方登录
    'login_unique' => false,
    //是否开启IP变动检测
    'loginip_check' => true,
    //登录页默认背景图
    'login_background' => '',
    //是否启用多级菜单导航
    'multiplenav' => false,
    //是否开启多选项卡(仅在开启多级菜单时起作用)
    'multipletab'           => true,
    //后台皮肤,为空时表示使用skin-blue-light
    'adminskin'             => '',
    //自动检测更新
    'checkupdate' => false,
    //API是否允许跨域
    'api_cross' => false,
    //版本号
    'version' => '3.0.3',
    //API接口地址
    'api_url' => 'https://api.iuok.cn',
    // 是否开启多语言
    'lang_switch_on' => true
];
