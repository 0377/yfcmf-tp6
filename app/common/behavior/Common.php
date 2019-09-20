<?php
/**
 * *
 *  * ============================================================================
 *  * Created by PhpStorm.
 *  * User: Ice
 *  * 邮箱: ice@sbing.vip
 *  * 网址: https://sbing.vip
 *  * Date: 2019/9/19 下午3:32
 *  * ============================================================================
 *
 */

namespace app\common\behavior;

use think\facade\Config;
use think\Lang;
use think\Loader;

class Common
{
    public function moduleInit(&$request)
    {
        // 设置mbstring字符编码
        mb_internal_encoding("UTF-8");

        // 如果修改了index.php入口地址，则需要手动修改cdnurl的值
        $url = preg_replace("/\/(\w+)\.php$/i", '', $request->root());
        // 如果未设置__CDN__则自动匹配得出
        if (!Config::get('view_replace_str.__CDN__')) {
            Config::set('view_replace_str.__CDN__', $url);
        }
        // 如果未设置__PUBLIC__则自动匹配得出
        if (!Config::get('view_replace_str.__PUBLIC__')) {
            Config::set('view_replace_str.__PUBLIC__', $url . '/');
        }
        // 如果未设置__ROOT__则自动匹配得出
        if (!Config::get('view_replace_str.__ROOT__')) {
            Config::set('view_replace_str.__ROOT__', preg_replace("/\/public\/$/", '', $url . '/'));
        }
        // 如果未设置cdnurl则自动匹配得出
        if (!Config::get('site.cdnurl')) {
            Config::set('site.cdnurl', $url);
        }
        // 如果未设置cdnurl则自动匹配得出
        if (!Config::get('upload.cdnurl')) {
            Config::set('upload.cdnurl', $url);
        }
        if (Config::get('app.app_debug')) {
            // 如果是调试模式将version置为当前的时间戳可避免缓存
            Config::set('site.version', time());
            // 如果是开发模式那么将异常模板修改成官方的
            Config::set('exception_tmpl', THINK_PATH . 'tpl' . DIRECTORY_SEPARATOR . 'think_exception.tpl');
        }
        // 如果是trace模式且Ajax的情况下关闭trace
        if (Config::get('app_trace') && $request->isAjax()) {
            Config::set('app_trace', false);
        }
        // 切换多语言
        if (Config::get('lang_switch_on') && $request->get('lang')) {
            \think\facade\Cookie::set('think_var', $request->get('lang'));
        }
        // Form别名
        if (!class_exists('Form')) {
            class_alias('fast\\Form', 'Form');
        }
    }

    public function addonBegin(&$request)
    {
        // 加载插件语言包
        Lang::load([
            APP_PATH . 'common' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $request->langset() . DIRECTORY_SEPARATOR . 'addon' . EXT,
        ]);
        $this->moduleInit($request);
    }
}
