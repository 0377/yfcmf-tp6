<?php

namespace addons\sso\library;


use addons\sso\model\SsoBorker;
use app\common\model\User;
use Ice\ValidationResult;

/**
 * Example SSO server.
 * Normally you'd fetch the broker info and user info from a database, rather then declaring them in the code.
 */
class SsoServer extends Server
{
    /**
     * Get the API secret of a broker and other info
     *
     * @param string $brokerId
     *
     * @return array
     */
    protected function getBrokerInfo($brokerId)
    {
        try {
            $sso_broker = SsoBorker::where('broker_id', '=', $brokerId)->find()->toArray();
        } catch (\Exception $exception) {
            $sso_broker = null;
        }
        return $sso_broker;
    }

    /**
     * Authenticate using user credentials
     *
     * @param string $username
     * @param string $password
     *
     * @return ValidationResult
     */
    protected function authenticate($username, $password)
    {
        if (!isset($username)) {
            return ValidationResult::error("username isn't set");
        }

        if (!isset($password)) {
            return ValidationResult::error("password isn't set");
        }
        return Auth::sso_login($username, $password);
    }


    /**
     * Get the user information
     *
     * @return array
     */
    protected function getUserInfo($username)
    {
        $fields = ['id', 'username', 'nickname', 'mobile', 'avatar', 'score'];
        try {
            $user = User::where('username', '=', $username)->field($fields)->find()->toArray();
        } catch (\Exception $exception) {
            $user = [];
        }
        return $user;
    }
}