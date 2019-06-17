<?php

namespace app\api\controller\article;

use app\common\controller\Api;

/**
 * 获取分类
 */
class Category extends Api
{

    // 无需登录的接口,*表示全部
    protected $noNeedLogin = [ 'getCategoryList' ];
    // 无需签名校验的接口,*表示全部
    protected $noNeedAjax = [ 'getCategoryList' ];
    // 无需小程序授权登录的
    protected $noNeedAuth = [ 'getCategoryList' ];

    protected $category_model;


    /**
     * 接口url
     * Api/article.category/getCategoryList
     */



    public function _initialize()
    {
        parent::_initialize();
        $this->category_model = new  \app\common\model\article\Category();
    }

    /**
     * 获取分类列表
     */
    public function getCategoryList()
    {
        $list = $this->category_model
            ->where([ 'pid' => 0 ])
            ->order('weigh desc,id desc')
            ->field('id,name')
            ->select();
        $list = collection($list)->toArray();
        foreach ( $list as &$v ) {
            $sub= $this->getSubCategoryList($v['id']);
            if(count($sub)>0){
                $v['sub'] = $sub;
            }
        }
        $this->success('查询成功', $list);
    }

    /**
     * 获取子列表
     */
    public function getSubCategoryList($pid)
    {
        $list = $this->category_model
            ->where([ 'pid' => $pid ])
            ->order('weigh desc,id desc')
            ->field('id,name')
            ->select();
        $list = collection($list)->toArray();
        foreach ( $list as &$v ) {
            $sub= $this->getSubCategoryList($v['id']);
            if(count($sub)>0){
                $v['sub'] = $sub;
            }
        }
        return $list;
    }

}
