<?php

namespace app\controller\index;

use app\model\Ems;
use yfcmf\core\annotation\NeedAuth;
use yfcmf\core\annotation\NeedLogin;
use yfcmf\core\Controller;


/**
 * 会员中心.
 */
class User extends Controller
{
    /**
     * @NeedLogin(true)
     * 会员中心.
     */
    public function index()
    {
        $this->view->assign('title', __('User center'));

        return $this->view->fetch();
    }

    /**
     * 注册会员.
     */
    public function register()
    {
        $url = $this->request->request('url', '');
        if ($this->auth->id) {
            $this->success(__('You\'ve logged in, do not login again'), $url ? $url : url('user/index'));
        }
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $email    = $this->request->post('email');
            $mobile   = $this->request->post('mobile', '');
            $captcha  = $this->request->post('captcha');
            $token    = $this->request->post('__token__');
            $rule     = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:6,30',
                'email'     => 'require|email',
                'mobile'    => 'regex:/^1\d{10}$/',
                //'captcha'   => 'require|captcha',
                '__token__' => 'require|token',
            ];

            $msg  = [
                'username.require' => 'Username can not be empty',
                'username.length'  => 'Username must be 3 to 30 characters',
                'password.require' => 'Password can not be empty',
                'password.length'  => 'Password must be 6 to 30 characters',
                //'captcha.require'  => 'Captcha can not be empty',
                //'captcha.captcha'  => 'Captcha is incorrect',
                'email'            => 'Email is incorrect',
                'mobile'           => 'Mobile is incorrect',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                'email'     => $email,
                'mobile'    => $mobile,
                //'captcha'   => $captcha,
                '__token__' => $token,
            ];
            //验证码
            $captchaResult = true;
            $captchaType   = config("fastadmin.user_register_captcha");
            if ($captchaType) {
                if ($captchaType == 'mobile') {
                    $captchaResult = Sms::check($mobile, $captcha, 'register');
                } elseif ($captchaType == 'email') {
                    $captchaResult = Ems::check($email, $captcha, 'register');
                } elseif ($captchaType == 'wechat') {
                    $captchaResult = WechatCaptcha::check($captcha, 'register');
                } elseif ($captchaType == 'text') {
                    $captchaResult = \think\facade\Validate::is($captcha, 'captcha');
                }
            }
            if (!$captchaResult) {
                $this->error(__('Captcha is incorrect'));
            }
            $validate = validate($rule, $msg, false, false);
            $result   = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->buildToken()]);
            }
            if ($this->auth->register($username, $password, $email, $mobile)) {
                $this->success(__('Sign up successful'), $url ? $url : url('user/index'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->buildToken()]);
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('captchaType', config('fastadmin.user_register_captcha'));
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Register'));

        return $this->view->fetch();
    }

    /**
     * 会员登录.
     */
    public function login()
    {
        //dump($this->view);
        $url = $this->request->request('url', '');
        if ($this->auth->id) {
            $this->success(__('You\'ve logged in, do not login again'), $url ? $url : url('user/index'));
        }
        if ($this->request->isPost()) {
            $account   = $this->request->post('account');
            $password  = $this->request->post('password');
            $keeplogin = (int) $this->request->post('keeplogin');
            $token     = $this->request->post('__token__');
            $rule      = [
                'account'  => 'require|length:3,50',
                'password' => 'require|length:6,30',
                //'__token__' => 'require|token',
            ];

            $msg      = [
                'account.require'  => 'Account can not be empty',
                'account.length'   => 'Account must be 3 to 50 characters',
                'password.require' => 'Password can not be empty',
                'password.length'  => 'Password must be 6 to 30 characters',
            ];
            $data     = [
                'account'   => $account,
                'password'  => $password,
                '__token__' => $token,
            ];
            $validate = validate($rule, $msg, false, false);
            $result   = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->buildToken()]);

                return false;
            }
            if ($this->auth->login($account, $password)) {
                $this->success(__('Logged in successful'), $url ? $url : url('user/index'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->buildToken()]);
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Login'));

        return $this->view->fetch();
    }

    /**
     * @NeedLogin(true)
     * 注销登录.
     */
    public function logout()
    {
        //注销本站
        $this->auth->logout();
        $this->success(__('Logout successful'), url('user/index'));
    }

    /**
     * @NeedLogin(true)
     * 个人信息.
     */
    public function profile()
    {
        $this->view->assign('title', __('Profile'));

        return $this->view->fetch();
    }

    /**
     * @NeedLogin(true)
     * 修改密码
     */
    public function changepwd()
    {
        if ($this->request->isPost()) {
            $oldpassword   = $this->request->post('oldpassword');
            $newpassword   = $this->request->post('newpassword');
            $renewpassword = $this->request->post('renewpassword');
            $token         = $this->request->post('__token__');
            $rule          = [
                'oldpassword|'.__('Old password')     => 'require|length:6,30',
                'newpassword|'.__('New password')     => 'require|length:6,30',
                'renewpassword|'.__('Renew password') => 'require|length:6,30|confirm:newpassword',
                '__token__'                           => 'token',
            ];

            $msg      = [
            ];
            $data     = [
                'oldpassword'   => $oldpassword,
                'newpassword'   => $newpassword,
                'renewpassword' => $renewpassword,
                '__token__'     => $token,
            ];
            $validate = validate($rule, $msg, false, false);
            $result   = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);

                return false;
            }

            $ret = $this->auth->changepwd($newpassword, $oldpassword);
            if ($ret) {
                $this->success(__('Reset password successful'), url('user/login'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        $this->view->assign('title', __('Change password'));

        return $this->view->fetch();
    }
}
