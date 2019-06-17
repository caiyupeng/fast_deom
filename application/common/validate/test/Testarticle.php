<?php

namespace app\common\validate\test;

use think\Validate;

class Testarticle extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        '__token__' => 'token',
        'name'      => 'require|length:3,50',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'name' => [ 'require' => '标题必须', 'length' => '标题长度必须在3-50中间' ]
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [ '__token__', ],
        'edit' => [ '__token__', 'name' ],
    ];

}
