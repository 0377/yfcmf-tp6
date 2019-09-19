<?php

namespace app\admin\event;

class AdminLog
{
    public function handle()
    {
        if (request()->isPost()) {
            \app\admin\model\AdminLog::record();
        }
    }
}
