<?php

namespace app\admin\model;

use think\facade\Cache;
use app\common\model\BaseModel;
use think\helper\Str;

class AuthRule extends BaseModel
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    protected static function onAfterWrite($model)
    {
        Cache::delete('__menu__');
    }

    public function getTitleAttr($value, $data)
    {
        return __($value);
    }

    public function setNameAttr($value, $data)
    {
        if (empty($data['route'])) {
            $route = '';
            $_arr  = explode('/', $data['name']);
            if (count($_arr) >= 3) {
                foreach ($_arr as $_k => $_v) {
                    $route .= $_v;
                    ($_k == 0) ? $route .= '.' : $route .= '/';
                }
                $route = rtrim($route, '/');
            } elseif (count($_arr) == 2) {
                $class = "app\\admin\\controller\\".Str::studly($_arr[0]);
                if (class_exists($class) && method_exists($class, Str::snake($_arr[1]))) {
                    $route = $data['name'];
                } else {
                    //省略了index
                    $route = implode('.', $_arr).'/index';
                }
            }
            $this->set('route', $route);
        }
        $this->set('name', $value);
    }
}
