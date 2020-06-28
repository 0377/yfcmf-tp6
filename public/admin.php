<?php
/**
 *  =============================================================
 *  Created by PhpStorm.
 *  User: Ice
 *  邮箱: ice@sbing.vip
 *  网址: https://sbing.vip
 *  Date: 2020/6/28 下午12:06
 *  ==============================================================
 */

namespace think;

require __DIR__ . '/../vendor/autoload.php';

! defined('YFCMF_ADMIN') && define('YFCMF_ADMIN', true);

$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
