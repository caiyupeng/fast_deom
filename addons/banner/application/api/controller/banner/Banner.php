<?php

namespace app\api\controller\banner;

use app\common\controller\Api;

/**
 * 获取轮播图
 */
class Banner extends Api
{

    // 无需登录的接口,*表示全部
    protected $noNeedLogin = [ 'getBannerList' ];
    // 无需签名校验的接口,*表示全部
    protected $noNeedAjax = [ 'getBannerList' ];
    // 无需小程序授权登录的
    protected $noNeedAuth = [ 'getBannerList' ];

    protected $banner_model;
    protected $key_model;
    protected $data_model;

    public function _initialize()
    {
        parent::_initialize();
        $this->banner_model = new  \app\common\model\banner\Banner();
    }

    /**
     * 获取轮播图列表
     */
    public function getBannerList()
    {
        $list = $this->banner_model
            ->where([ 'status' => 1 ])
            ->order('weigh desc,id desc')
            ->field('id,name,image,url,remark')
            ->select();
        $list = collection($list)->toArray();
        foreach ( $list as &$v ) {
            $v['image'] = request()->domain() . $v['image'];
        }
        $this->success('查询成功', $list);
    }


}
