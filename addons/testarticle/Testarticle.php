<?php

namespace addons\testarticle;

use app\common\library\Menu;
use think\Addons;

/**
 * 定时任务
 */
class Testarticle extends Addons
{

    /**
     * 插件安装方法
     *
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name'    => 'test/testarticle',
                'title'   => '测试文章插件',
                'icon'    => 'fa fa-tasks',
                'remark'  => '测试文章插件',
                'sublist' => [
                    [ 'name' => 'test/testarticle/index', 'title' => '查看' ],
                    [ 'name' => 'test/testarticle/add', 'title' => '添加' ],
                    [ 'name' => 'test/testarticle/edit', 'title' => '编辑 ' ],
                    [ 'name' => 'test/testarticle/del', 'title' => '删除' ],
                    [ 'name' => 'test/testarticle/multi', 'title' => '批量更新' ],
                ]
            ]
        ];
        Menu::create($menu, 'test');

        return true;
    }

    /**
     * 插件卸载方法
     *
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete('test/testarticle');

        return true;
    }

    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::enable('test/testarticle');
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable('test/testarticle');
    }
}
