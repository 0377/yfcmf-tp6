<?php

namespace app\admin\model;

use think\facade\Cache;
use app\common\model\BaseModel;

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
}
