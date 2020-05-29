<?php

use yfcmf\core\handle\AppExceptionHandle;
use yfcmf\provider\YfcmfRequest;
use yfcmf\provider\YfcmfRoute;

return [
    'think\Route'            => YfcmfRoute::class,
    'think\Request'          => YfcmfRequest::class,
    'think\exception\Handle' => AppExceptionHandle::class,
];
