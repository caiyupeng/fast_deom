<?php

namespace app\admin\controller\article;

use app\common\controller\Backend;
use fast\Tree;

/**
 * 文章类别
 *
 * @icon fa fa-circle-o
 */
class Category extends Backend
{
    /**
     * 无需鉴权的方法,但需要登录
     *
     * @var array
     */
    protected $noNeedRight = [ 'setdata', 'setpath' ];

    /**
     * Category模型对象
     *
     * @var \app\common\model\article\Category
     */
    protected $model = null;
    protected $categorylist = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model  = new \app\common\model\article\Category;
        $categorydata = [ 0 => __('None') ];
        $this->view->assign('categorydata', $categorydata);
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
        if ( $this->request->isAjax() ) {
            $this->setdata();
            $list   = $this->categorylist;
            $total  = count($this->categorylist);
            $result = [ "total" => $total, "rows" => $list ];

            return json($result);
        }

        return $this->view->fetch();
    }

    public function setdata()
    {
        $data = $this->model
            ->order('weigh', 'asc')
            ->select();
        // 必须将结果集转换为数组
        $categoryList = collection($data)->toArray();
        unset($v);
        Tree::instance()->init($categoryList);
        $this->categorylist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
        $categorydata       = [ 0 => __('None') ];
        foreach ( $this->categorylist as $k => &$v ) {
            $categorydata[$v['id']] = $v['name'];

        }
        $this->view->assign('categorydata', $categorydata);
    }

    /**
     * 添加
     */
    public function add($pid = null)
    {
        if ( $this->request->isPost() ) {
            $params = $this->request->post("row/a");
            if ( $params ) {
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
                        $this->setpath($this->model->id);
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
        $this->view->assign("pid", $pid);
        $this->setdata();

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
                        $this->setpath($ids);
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
        $this->setdata();

        return $this->view->fetch();
    }

    protected function setpath($id)
    {
        $row   = $this->model->get($id);
        $pid   = $row['pid'];
        $pinfo = $this->model->get($pid);
        $path  = '';
        if ( $pinfo ) {
            $path .= $pinfo->path;
        }
        $path .= $row['id'] . '-';
        $row->path = $path;
        $row->save();
    }
}
