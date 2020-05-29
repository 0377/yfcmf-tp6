<?php


namespace yfcmf\core\annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * 登录注解类
 *
 * @Annotation
 * @Target({"METHOD"})
 */
final class NeedLogin extends Annotation
{

}