<?php

namespace app\common\model\weixin;

use think\Model;

class Member extends Model
{
    // 表名
    protected $name = 'weixin_member';

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
        return [ '1' => __('男'), '2' => __('女') ];
    }


    public function getSexTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['sex']) ? $data['sex'] : '');
        $list  = $this->getSexList();

        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCreatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['createtime'];

        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getUpdatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['updatetime'];

        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setUpdatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
