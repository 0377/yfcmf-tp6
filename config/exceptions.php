<?php
/**
 *  =============================================================
 *  Created by PhpStorm.
 *  User: Ice
 *  邮箱: ice@sbing.vip
 *  网址: https://sbing.vip
 *  Date: 2020/6/28 下午12:07
 *  ==============================================================
 */

declare(strict_types=1);

/**
 * 按照先后顺序执行异常处理
 */

use app\handle\AdminNotLoginExceptionHandle;
use yfcmf\core\handle\DataNotFoundExceptionHandle;
use yfcmf\core\handle\ModelNotFoundExceptionHandle;
use yfcmf\core\handle\NotAuthExceptionHandle;
use yfcmf\core\handle\NotLoginExceptionHandle;
use yfcmf\core\handle\ValidateExceptionHandle;

return [
    'index' => [
        ModelNotFoundExceptionHandle::class,
        DataNotFoundExceptionHandle::class,
        NotLoginExceptionHandle::class,
        NotAuthExceptionHandle::class,
        ValidateExceptionHandle::class,
    ],
    'admin' => [
        ModelNotFoundExceptionHandle::class,
        DataNotFoundExceptionHandle::class,
        AdminNotLoginExceptionHandle::class,
        NotAuthExceptionHandle::class,
        ValidateExceptionHandle::class,
    ],
];
