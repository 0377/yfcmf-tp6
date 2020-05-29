<?php

namespace yfcmf\core\traits;

use yfcmf\library\Auth;
use think\facade\Request;
use yfcmf\core\annotation\NeedAuth;
use yfcmf\core\annotation\NeedLogin;
use Doctrine\Common\Annotations\Reader;
use yfcmf\core\exception\NotLoginException;
use yfcmf\core\exception\NotAuthException;

trait AuthTraits
{
    /**
     * 检查注解登陆和权限
     *
     * @throws NotLoginException
     * @throws NotAuthException
     */
    protected function checkAuth()
    {
        $action     = Request::action();
        $controller = Request::originController();
        $refObject  = new \ReflectionObject($this);
        $reader     = \app()->make(Reader::class);
        foreach ($refObject->getMethods() as $method) {
            if ($action === $method->getName()) {
                $annotationLogin = $reader->getMethodAnnotation($method, NeedLogin::class);
                if ($annotationLogin && $annotationLogin->value === true) {
                    /** @var Auth $auth */
                    $auth = app()->auth;
                    if (!$auth->isLogin()) {
                        throw new NotLoginException(__('Please login first'));
                    }
                }
                $annotationAuth = $reader->getMethodAnnotation($method, NeedAuth::class);
                if ($annotationAuth && $annotationAuth->value === true) {
                    /** @var Auth $auth */
                    $auth = app()->auth;
                    if (!$auth->check($controller.'/'.$action)) {
                        throw new NotAuthException(__('You have no permission'));
                    }
                }
            }
        }
    }
}