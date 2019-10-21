<?php

return array (
  0 => 
  array (
    'name' => 'sso_server',
    'title' => '单点登陆地址',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'http://login.localtest.me/login',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  1 => 
  array (
    'name' => 'sso_broker_id',
    'title' => '授权ID',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'fast_tp6',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  2 => 
  array (
    'name' => 'sso_broker_secret',
    'title' => '授权密钥',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'Rdx9Yc68lF6mU2Umg9HPtTQgqzwURYHDK1ayYOwi',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  3 => 
  array (
    'name' => 'domain',
    'title' => '绑定二级域名前缀',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'login',
    'rule' => '',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  4 => 
  array (
    'name' => 'rewrite',
    'title' => '伪静态',
    'type' => 'array',
    'content' => 
    array (
    ),
    'value' => 
    array (
      'index/index' => '/login$',
    ),
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
);
