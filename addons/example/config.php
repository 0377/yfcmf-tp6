<?php

return [
    [
        'name'    => 'title',
        'title'   => '标题',
        'type'    => 'string',
        'content' => [
        ],
        'value'   => '示例标题',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => ''
    ],
    [
        //配置唯一标识
        'name'    => 'theme',
        //显示的标题
        'title'   => '皮肤',
        //类型
        'type'    => 'string',
        //数据字典
        'content' => [
        ],
        //值
        'value'   => 'default',
        //验证规则 
        'rule'    => 'required',
        //错误消息
        'msg'     => '',
        //提示消息
        'tip'     => '',
        //成功消息
        'ok'      => '',
        //扩展信息
        'extend'  => ''
    ],
    [
        'name'    => 'domain',
        'title'   => '绑定二级域名前缀',
        'type'    => 'string',
        'content' => [
        ],
        'value'   => '',
        'rule'    => '',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => ''
    ],
    [
        'name'    => 'rewrite',
        'title'   => '伪静态',
        'type'    => 'array',
        'content' => [],
        'value'   => [
            'index/index' => '/example$',
            'demo/index'  => '/example/d/[:name]',
            'demo/demo1'  => '/example/d1/[:name]',
            'demo/demo2'  => '/example/d2/[:name]',
        ],
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => ''
    ],
];
