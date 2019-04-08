<?php

namespace addons\wechatapp;

use app\common\library\Menu;
use think\Addons;

/**
 * 小程序配置
 */
class Wechatapp extends Addons
{
    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
         $menu = [
            [
                'name'    => 'wechatapp',
                'title'   => '小程序管理',
                'icon'    => 'fa fa-wechat',
                'remark'  => '普通的小程序管理封装',
                'ismenu'  =>1,
                'sublist' => [
                    [
                        'name' => 'wechatapp/user',
                        'icon'    => 'fa fa-wechat',
                        'title' => '用户管理',
                        'remark'  => '用户管理',
                        'ismenu'=>1,
                        'sublist'=>[
                            ['name' => 'wechatapp/user/index', 'title' => '查看'],
                            ['name' => 'wechatapp/user/add', 'title' => '添加'],
                            ['name' => 'wechatapp/user/edit', 'title' => '编辑'],
                            ['name' => 'wechatapp/user/del', 'title' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'wechatapp/config',
                        'icon'    => 'fa fa-wechat',
                        'title' => '小程序配置管理',
                        'remark'  => '小程序配置管理',
                        'ismenu'=>1,
                        'sublist'=>[
                            ['name' => 'wechatapp/config/index', 'title' => '查看'],
                            ['name' => 'wechatapp/config/add', 'title' => '添加'],
                            ['name' => 'wechatapp/config/edit', 'title' => '编辑'],
                            ['name' => 'wechatapp/config/del', 'title' => '删除'],
                            ['name' => 'wechatapp/config/updateconfig', 'title' => '更新缓存'],
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
        Menu::delete('wechatapp');
        return true;
    }
    
    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::enable('wechatapp');
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable('wechatapp');
    }

}
