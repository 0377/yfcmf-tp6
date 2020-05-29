<?php

declare(strict_types=1);

/**
 * 按照先后顺序执行异常处理
 */

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
];
