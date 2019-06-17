<?php

namespace app\common\model\article;

use think\Model;
use traits\model\SoftDelete;

class Content extends Model
{
    use SoftDelete;
    protected $deleteTime = 'deletetime';
    // 表名
    protected $name = 'article_content';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['1' => __('Status 1'),'2' => __('Status 2')];
    }     


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function category()
    {
        return $this->belongsTo('Category', 'article_category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
