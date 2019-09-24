<?php

return [
    array(
        'name'    => 'rewrite',
        'title'   => '伪静态',
        'type'    => 'array',
        'content' =>
        array(
        ),
        'value'   =>
        array(
            'index/index' => '/qrcode$',
            'index/build' => '/qrcode/build$',
        ),
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ),
];
