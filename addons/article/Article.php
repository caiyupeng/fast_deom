<?php

namespace addons\article;

use app\common\library\Menu;
use think\Addons;

/**
 * 文章管理
 */
class Article extends Addons
{
    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
         $menu = [
            [
                'name'    => 'article',
                'title'   => '文章管理',
                'icon'    => 'fa fa-list',
                'remark'  => '普通的文章管理封装',
                'ismenu'  =>1,
                'sublist' => [
                    [
                        'name' => 'article/category',
                        'icon'    => 'fa fa-align-center',
                        'title' => '分类管理',
                        'remark'  => '分类管理',
                        'ismenu'=>1,
                        'sublist'=>[
                            ['name' => 'article/category/index', 'title' => '查看'],
                            ['name' => 'article/category/add', 'title' => '添加'],
                            ['name' => 'article/category/edit', 'title' => '编辑'],
                            ['name' => 'article/category/del', 'title' => '删除']
                        ]
                    ],
                    [
                        'name' => 'article/content',
                        'icon'    => 'fa fa-book',
                        'title' => '文章内容',
                        'remark'  => '文章内容',
                        'ismenu'=>1,
                        'sublist'=>[
                            ['name' => 'article/content/index', 'title' => '查看'],
                            ['name' => 'article/content/add', 'title' => '添加'],
                            ['name' => 'article/content/edit', 'title' => '编辑'],
                            ['name' => 'article/content/del', 'title' => '删除']
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
        Menu::delete('article');
        return true;
    }
    
    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::enable('article');
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable('article');
    }

}
