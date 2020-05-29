<?php

namespace yfcmf\core\annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * 权限注解类
 *
 * @Annotation
 * @Target({"METHOD"})
 */
final class NeedAuth extends Annotation
{
}