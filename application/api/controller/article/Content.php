<?php

namespace app\api\controller\article;

use app\common\controller\Api;

/**
 * 获取分类
 */
class Content extends Api
{

    // 无需登录的接口,*表示全部
    protected $noNeedLogin = [ 'getContentList', 'getContentDetail' ];
    // 无需签名校验的接口,*表示全部
    protected $noNeedAjax = [ 'getContentList', 'getContentDetail' ];
    // 无需小程序授权登录的
    protected $noNeedAuth = [ 'getContentList', 'getContentDetail' ];

    protected $content_model;
    protected $category_model;

    /**
     * 接口url
     * Api/article.content/getContentList
     * Api/article.content/getContentDetail
     */


    public function _initialize()
    {
        parent::_initialize();
        $this->content_model  = new  \app\common\model\article\Content();
        $this->category_model = new  \app\common\model\article\Category();
    }

    /**
     * 获取列表
     */
    public function getContentList()
    {
        $search     = $this->request->param('search');
        $page       = $this->request->param('page', 1);
        $pagesize   = $this->request->param('pagesize', 5);
        $categoryid = $this->request->param('categoryid');

        $where           = [];
        $where['status'] = 1;
        if ( !empty($search) ) {
            $where['title'] = [ 'like', '%' . $search . '%' ];
        }
        if ( !empty($categoryid) ) {
            $path                           = $this->category_model->getPath($categoryid);
            $where['article_category_path'] = [ 'like', $path . '%' ];
        }
        $list = $this->content_model
            ->where($where)
            ->page($page, $pagesize)
            ->order('id desc ')
            ->field('id,title,images,createtime,avatar,virtualreadnum,readnum,author')
            ->select();
        $list = collection($list)->toArray();
        foreach ( $list as $k => $v ) {
            $list[$k]['createtime'] = date('Y-m-d H:i', $list[$k]['createtime']);
            $list[$k]['imagearr']   = explode(',', $list[$k]['images']);
            foreach ( $list[$k]['imagearr'] as $a => $b ) {
                $list[$k]['imagearr'][$a] = request()->domain() . $list[$k]['imagearr'][$a];
            }
            $list[$k]['images']  = join(',', $list[$k]['imagearr']);
            $list[$k]['avatar']  = request()->domain() . $list[$k]['avatar'];
            $list[$k]['readnum'] = intval($list[$k]['virtualreadnum']) + intval($list[$k]['readnum']);
            unset($list[$k]['virtualreadnum']);
        }
        $this->success('查询成功', $list);
    }


    /**
     * 获取详情
     */
    public function getContentDetail()
    {
        $id   = $this->request->param('id');
        $data = $this->content_model
            ->where([ 'id' => $id, 'status' => 1 ])
            ->field('id,title,images,createtime,avatar,virtualreadnum,readnum,desc,content,resource,author')
            ->find();
        if ( !$data ) {
            $this->error('数据不存在');
        }
        $data               = $data->toArray();
        $data['createtime'] = date('Y-m-d H:i', $data['createtime']);
        $data['imagearr']   = explode(',', $data['images']);
        foreach ( $data['imagearr'] as $a => $b ) {
            $data['imagearr'][$a] = request()->domain() . $data['imagearr'][$a];
        }
        $data['images']  = join(',', $data['imagearr']);
        $data['avatar']  = request()->domain() . $data['avatar'];
        $data['readnum'] = intval($data['virtualreadnum']) + intval($data['readnum']);
        unset($data['virtualreadnum']);
        //阅读次数加1
        $this->content_model->where('id', $id)->setInc('readnum');
        $this->success('查询成功', $data);
    }

}
