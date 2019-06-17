<?php

namespace app\admin\controller\article;

use app\common\controller\Backend;

/**
 * 文章内容
 *
 * @icon fa fa-circle-o
 */
class Content extends Backend
{

    /**
     * Content模型对象
     *
     * @var \app\common\model\article\Content
     */
    protected $model = null;
    protected $category_model = null;


    public function _initialize()
    {
        parent::_initialize();
        $this->model          = new \app\common\model\article\Content;
        $this->category_model = new \app\common\model\article\Category;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter([ 'strip_tags' ]);
        if ( $this->request->isAjax() ) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ( $this->request->request('keyField') ) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('title,category.name');
            $total = $this->model
                ->with([ 'category' ])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with([ 'category' ])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ( $list as $row ) {

                $row->getRelation('category')->visible([ 'name' ]);
            }
            $list   = collection($list)->toArray();
            $result = [ "total" => $total, "rows" => $list ];

            return json($result);
        }

        return $this->view->fetch();
    }


    /**
     * 添加
     */
    public function add($pid = null)
    {
        if ( $this->request->isPost() ) {
            $params = $this->request->post("row/a");
            if ( $params ) {
                $params['article_category_path'] = '';
                if ( !!$params['article_category_id'] ) {
                    $category = $this->category_model->get($params['article_category_id']);
                    if ( $category ) {
                        $params['article_category_path'] = $category->path;
                    }
                }
                if ( $this->dataLimit && $this->dataLimitFieldAutoFill ) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {
                    //是否采用模型验证
                    if ( $this->modelValidate ) {
                        $name     = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    if ( $result !== false ) {
                        $this->success();
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch ( \think\exception\PDOException $e ) {
                    $this->error($e->getMessage());
                } catch ( \think\Exception $e ) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if ( !$row ) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if ( is_array($adminIds) ) {
            if ( !in_array($row[$this->dataLimitField], $adminIds) ) {
                $this->error(__('You have no permission'));
            }
        }
        if ( $this->request->isPost() ) {
            $params = $this->request->post("row/a");

            $params['article_category_path'] = '';
            if ( !!$params['article_category_id'] ) {
                $category = $this->category_model->get($params['article_category_id']);
                if ( $category ) {
                    $params['article_category_path'] = $category->path;
                }
            }
            if ( $params ) {
                try {
                    //是否采用模型验证
                    if ( $this->modelValidate ) {
                        $name     = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ( $result !== false ) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch ( \think\exception\PDOException $e ) {
                    $this->error($e->getMessage());
                } catch ( \think\Exception $e ) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);

        return $this->view->fetch();
    }
}
