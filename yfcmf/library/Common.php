<?php


namespace yfcmf\library;


use think\facade\Event;

class Common
{

    /**
     * 处理插件钩子.
     *
     * @param  string      $event   钩子名称
     * @param  array|null  $params  传入参数
     * @param  bool        $once
     *
     * @return mixed
     */
    public static function hook($event, $params = null, bool $once = false)
    {
        return Event::trigger($event, $params, $once);
    }

    /**
     * 获取入口文件名
     *
     * @return mixed|string
     */
    public static function getPhpFile()
    {
        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            $file = $_SERVER['SCRIPT_FILENAME'];
        } elseif (isset($_SERVER['argv'][0])) {
            $file = realpath($_SERVER['argv'][0]);
        }
        return isset($file) ? pathinfo($file, PATHINFO_FILENAME) : '';
    }

    /**
     * var_export方法array转[]
     *
     * @param        $expression
     * @param  bool  $return
     *
     * @return mixed|string|string[]|null
     */
    public static function varexport($expression, $return = false)
    {
        $export = var_export($expression, true);
        $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
        $array  = preg_split("/\r\n|\n|\r/", $export);
        $array  = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [null, ']$1', ' => ['], $array);
        $export = join(PHP_EOL, array_filter(["["] + $array));
        if ((bool) $return) {
            return $export;
        } else {
            echo $export;
        }
    }

}