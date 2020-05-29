<?php


namespace yfcmf\core;

use Doctrine\Common\Annotations\Reader;
use think\facade\Request;
use yfcmf\core\annotation\Layout;
use yfcmf\library\Auth;
use think\annotation\Inject;
use yfcmf\provider\YfcmfRequest;
use yfcmf\core\traits\AuthTraits;
use yfcmf\core\traits\JumpTraits;

/**
 * 继承控制器
 *
 * @package yfcmf\core
 */
abstract class Controller
{
    use JumpTraits, AuthTraits;
    /**
     * @Inject()
     * @var YfcmfRequest
     */
    protected $request;

    /**
     * @Inject()
     * @var \think\View
     */
    protected $view;

    /**
     * @Inject()
     * @var Auth
     */
    protected $auth;

    /**
     * Controller constructor.
     *
     * @throws exception\NotAuthException
     * @throws exception\NotLoginException
     */
    public function __construct()
    {
        $this->checkAuth();
        //$this->layoutInject();
    }
    /**
     * 注解layout
     */
    protected function layoutInject()
    {
        $action    = Request::action();
        $refObject = new \ReflectionObject($this);
        $reader    = \app()->make(Reader::class);
        foreach ($refObject->getMethods() as $method) {
            if ($action === $method->getName()) {
                $annotationLayout = $reader->getMethodAnnotation($method, Layout::class);
                $layout           = '';
                if ($annotationLayout) {
                    if ($annotationLayout->value) {
                        $layout = $annotationLayout->value;
                    }
                } else {
                    $layout = 'default';
                }
                if (!empty($layout)) {
                    halt($this->view);
                    $this->view->engine()->layout('layout/'.$layout);
                }
            }
        }
    }
}