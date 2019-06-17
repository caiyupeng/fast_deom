<?php

namespace app\common\model\wechatapp;

use think\Model;

class Formidlist extends Model
{
    // 表名
    protected $name = 'wechatapp_formidlist';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [

    ];
}
