<?php

namespace addons\sso\model;

use app\common\model\BaseModel;

class SsoBorker extends BaseModel
{
    // 表名
    protected $name = 'sso_broker';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];
}
