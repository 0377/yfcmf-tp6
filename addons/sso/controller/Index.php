<?php

namespace addons\sso\controller;

use addons\sso\library\Broker;
use addons\sso\library\SsoServer;
use think\addons\Controller;

class Index extends Controller
{

    public function index()
    {
        $ssoServer = new SsoServer();
        $command = isset($_REQUEST['command']) ? $_REQUEST['command'] : null;
        if (!$command || !method_exists($ssoServer, $command)) {
            header("HTTP/1.1 404 Not Found");
            header('Content-type: application/json; charset=UTF-8');

            echo json_encode(['error' => 'Unknown command']);
            exit();
        }
        try {
            $result = $ssoServer->$command();
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        return $this->success('ok', $result);
    }

}
