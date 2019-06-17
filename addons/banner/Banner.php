<?php

namespace addons\banner;

use app\common\library\Menu;
use think\Addons;

/**
 * 轮播图
 */
class Banner extends Addons
{
    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
         $menu = [
            [
                'name'    => 'banner',
                'title'   => '轮播图管理',
                'icon'    => 'fa fa-image',
                'remark'  => '普通的轮播图管理封装',
                'ismenu'  =>1,
                'sublist' => [
                    [
                        'name' => 'banner/banner',
                        'icon'    => 'fa fa-image',
                        'title' => '轮播图管理',
                        'remark'  => '轮播图管理',
                        'ismenu'=>1,
                        'sublist'=>[
                            ['name' => 'banner/banner/index', 'title' => '查看'],
                            ['name' => 'banner/banner/add', 'title' => '添加'],
                            ['name' => 'banner/banner/edit', 'title' => '编辑'],
                            ['name' => 'banner/banner/del', 'title' => '删除'],
                        ]
                    ]
                    
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
        Menu::delete('banner');
        return true;
    }
    
    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::enable('banner');
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable('banner');
    }

}
