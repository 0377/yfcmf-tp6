<?php

use think\facade\Env;

return [
    'default' => Env::get('filesystem.driver', 'local'),
    'disks'   => [
        'local'  => [
            'type' => 'local',
            'root'       => app()->getRootPath().'public',
            'url'        => '/',
            'visibility' => 'public',
        ],
        'runtime' => [
            'type' => 'local',
            'root' => app()->getRootPath().'runtime',
        ],
        // 更多的磁盘配置信息
    ],
];
