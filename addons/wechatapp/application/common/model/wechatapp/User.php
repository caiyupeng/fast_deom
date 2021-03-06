<?php

namespace app\common\model\wechatapp;

use think\Model;

class User extends Model
{
    // 表名
    protected $name = 'wechatapp_user';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'sex_text'
    ];


    public function getSexList()
    {
        return [ '0' => __('Sex 0'), '1' => __('Sex 1'), '2' => __('Sex 2') ];
    }


    public function getSexTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['sex']) ? $data['sex'] : '');
        $list  = $this->getSexList();

        return isset($list[$value]) ? $list[$value] : '';
    }


}
