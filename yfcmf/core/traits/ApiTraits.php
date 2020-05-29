<?php

namespace yfcmf\core\traits;

use think\annotation\Inject;
use yfcmf\provider\YfcmfRequest;

trait ApiTraits
{
    use JumpTraits;

    /**
     * @Inject()
     * @var YfcmfRequest
     */
    protected $request;
}
