<?php

namespace app\common\model\article;

use think\Model;
use traits\model\SoftDelete;

class Category extends Model
{
    use SoftDelete;
    protected $deleteTime = 'deletetime';
    // 表名
    protected $name = 'article_category';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
    ];


    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update([ 'weigh' => $row[$pk] ]);
        });
    }


    protected function setDeletetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


    public function getPath($id)
    {
        $info = self::get($id);

        return $info ? $info->path : '';
    }


}
