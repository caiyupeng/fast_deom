<?php

namespace app\common\model\wechatapp;

use think\Model;

class Send extends Model
{
    // 表名
    protected $name = 'wechatapp_send';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

}
