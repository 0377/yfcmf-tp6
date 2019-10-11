<?php

namespace addons\example;

use app\common\library\Menu;
use think\Addons;

/**
 * Example
 */
class Example extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name'    => 'example',
                'title'   => '开发示例管理',
                'icon'    => 'fa fa-magic',
                'sublist' => [
                    [
                        'name'    => 'example/bootstraptable',
                        'title'   => '表格完整示例',
                        'icon'    => 'fa fa-table',
                        'sublist' => [
                            ['name' => 'example/bootstraptable/index', 'title' => '查看'],
                            ['name' => 'example/bootstraptable/detail', 'title' => '详情'],
                            ['name' => 'example/bootstraptable/change', 'title' => '变更'],
                            ['name' => 'example/bootstraptable/del', 'title' => '删除'],
                            ['name' => 'example/bootstraptable/multi', 'title' => '批量更新'],
                        ]
                    ],
                    [
                        'name'    => 'example/customsearch',
                        'title'   => '自定义搜索',
                        'icon'    => 'fa fa-table',
                        'sublist' => [
                            ['name' => 'example/customsearch/index', 'title' => '查看'],
                            ['name' => 'example/customsearch/del', 'title' => '删除'],
                            ['name' => 'example/customsearch/multi', 'title' => '批量更新'],
                        ]
                    ],
                    [
                        'name'    => 'example/customform',
                        'title'   => '自定义表单示例',
                        'icon'    => 'fa fa-edit',
                        'sublist' => [
                            ['name' => 'example/customform/index', 'title' => '查看'],
                        ]
                    ],
                    [
                        'name'    => 'example/tablelink',
                        'title'   => '表格联动示例',
                        'icon'    => 'fa fa-table',
                        'sublist' => [
                            ['name' => 'example/tablelink/index', 'title' => '查看'],
                        ]
                    ],
                    [
                        'name'    => 'example/colorbadge',
                        'title'   => '彩色角标',
                        'icon'    => 'fa fa-table',
                        'sublist' => [
                            ['name' => 'example/colorbadge/index', 'title' => '查看'],
                            ['name' => 'example/colorbadge/del', 'title' => '删除'],
                            ['name' => 'example/colorbadge/multi', 'title' => '批量更新'],
                        ]
                    ],
                    [
                        'name'    => 'example/controllerjump',
                        'title'   => '控制器间跳转',
                        'icon'    => 'fa fa-table',
                        'sublist' => [
                            ['name' => 'example/controllerjump/index', 'title' => '查看'],
                            ['name' => 'example/controllerjump/del', 'title' => '删除'],
                            ['name' => 'example/controllerjump/multi', 'title' => '批量更新'],
                        ]
                    ],
                    [
                        'name'    => 'example/cxselect',
                        'title'   => '多级联动',
                        'icon'    => 'fa fa-table',
                        'sublist' => [
                            ['name' => 'example/cxselect/index', 'title' => '查看'],
                            ['name' => 'example/cxselect/del', 'title' => '删除'],
                            ['name' => 'example/cxselect/multi', 'title' => '批量更新'],
                        ]
                    ],
                    [
                        'name'    => 'example/multitable',
                        'title'   => '多表格示例',
                        'icon'    => 'fa fa-table',
                        'sublist' => [
                            ['name' => 'example/multitable/index', 'title' => '查看'],
                            ['name' => 'example/multitable/del', 'title' => '删除'],
                            ['name' => 'example/multitable/multi', 'title' => '批量更新'],
                        ]
                    ],
                    [
                        'name'    => 'example/relationmodel',
                        'title'   => '关联模型示例',
                        'icon'    => 'fa fa-table',
                        'sublist' => [
                            ['name' => 'example/relationmodel/index', 'title' => '查看'],
                            ['name' => 'example/relationmodel/del', 'title' => '删除'],
                            ['name' => 'example/relationmodel/multi', 'title' => '批量更新'],
                        ]
                    ],
                    [
                        'name'    => 'example/tabletemplate',
                        'title'   => '表格模板示例',
                        'icon'    => 'fa fa-table',
                        'sublist' => [
                            ['name' => 'example/tabletemplate/index', 'title' => '查看'],
                            ['name' => 'example/tabletemplate/detail', 'title' => '详情'],
                            ['name' => 'example/tabletemplate/del', 'title' => '删除'],
                            ['name' => 'example/tabletemplate/multi', 'title' => '批量更新'],
                        ]
                    ],
                    [
                        'name'    => 'example/baidumap',
                        'title'   => '百度地图示例',
                        'icon'    => 'fa fa-map-pin',
                        'sublist' => [
                            ['name' => 'example/baidumap/index', 'title' => '查看'],
                            ['name' => 'example/baidumap/map', 'title' => '详情'],
                            ['name' => 'example/baidumap/del', 'title' => '删除'],
                        ]
                    ],
                ]
            ]
        ];
        Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete('example');
        return true;
    }
    
    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::enable('example');
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable('example');
    }

}
