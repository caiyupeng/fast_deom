<?php

namespace addons\weixin;

use app\common\library\Menu;
use think\Addons;

/**
 * 定时任务
 */
class Weixin extends Addons
{
    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
         $menu = [
            [
                'name'    => 'weixin',
                'title'   => '微信管理',
                'icon'    => 'fa fa-wechat',
                'remark'  => '普通的微信管理封装',
                'ismenu'  =>1,
                'sublist' => [
                    [
                        'name' => 'weixin/member',
                        'icon'    => 'fa fa-wechat',
                        'title' => '用户管理',
                        'remark'  => '用户管理',
                        'ismenu'=>1,
                        'sublist'=>[
                            ['name' => 'weixin/member/index', 'title' => '查看'],
                            ['name' => 'weixin/member/info', 'title' => '查看人员详情'],
                            ['name' => 'weixin/member/add', 'title' => '添加'],
                            ['name' => 'weixin/member/edit', 'title' => '编辑'],
                            ['name' => 'weixin/member/del', 'title' => '删除'],
                        ]
                    ],
                    [
                        'name' => 'weixin/config',
                        'icon'    => 'fa fa-wechat',
                        'title' => '微信公众号配置管理',
                        'remark'  => '微信公众号配置管理',
                        'ismenu'=>1,
                        'sublist'=>[
                            ['name' => 'weixin/config/index', 'title' => '查看'],
                            ['name' => 'weixin/config/add', 'title' => '添加'],
                            ['name' => 'weixin/config/edit', 'title' => '编辑'],
                            ['name' => 'weixin/config/del', 'title' => '删除'],
                            ['name' => 'weixin/config/updateconfig', 'title' => '更新缓存'],
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
        Menu::delete('weixin');
        return true;
    }
    
    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::enable('weixin');
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable('weixin');
    }

}
