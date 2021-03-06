<?php

namespace app\common\model\weixin;

use think\Model;

class Config extends Model
{
    // 表名
    protected $name = 'weixin_config';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'deletetime_text',
        'status_text'
    ];


    public function getStatusList()
    {
        return [ '1' => __('Status 1'), '2' => __('Status 2') ];
    }


    public function getDeletetimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['deletetime'];

        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list  = $this->getStatusList();

        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setDeletetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
