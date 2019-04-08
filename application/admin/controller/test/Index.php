<?php

namespace app\admin\controller\test;

use app\common\controller\Backend;
use app\common\library\Menu;

/**
 * 管理员管理
 *
 * @icon fa fa-users
 * @remark 一个管理员可以有多个角色组,左侧的菜单根据管理员所拥有的权限进行生成
 */
class Index extends Backend
{

    /**
     * 查看
     */
    public function index()
    {

        
        $str = "&lt;&gt;三会一课全流程测试-终&lt;&gt;“”";

        echo $str;

    }


}
