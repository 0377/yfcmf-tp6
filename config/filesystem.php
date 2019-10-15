<?php

use think\facade\Env;

return [
    'default' => Env::get('filesystem.driver', 'local'),
    'disks'   => [
        'local'  => [
            'type' => 'local',
            'root' => app()->getRuntimePath().'storage',
        ],
        'runtime'  => [
            'type' => 'local',
            'root' => app()->getRootPath().'runtime',
        ],
        'public' => [
            'type'       => 'local',
            'root'       => app()->getRootPath().'public',
            'url'        => '/',
            'visibility' => 'public',
        ],
        // 更多的磁盘配置信息
    ],
];
