<?php

/**
 * 管理员控制器
 */

namespace app\admin\controller;

use think\controller;

class ArticleCategory extends controller
{

    protected function _initialize()
    {
        parent::_initialize();

        //检查登陆情况
        tc_admin_login_jump();

        //检查权限
        tc_admin_permission_jump(2);
    }

    public function index()
    {
        //列表页
        $data = array();
        //筛选查询条件数组
        $param = array();
        
        $articleCategoryModel = model('articleCategory');
        
        //获取状态数组
        $data['statuss'] = $articleCategoryModel->getStatus();
        
        $categorys = $articleCategoryModel->getQueueCategory();
        
        if($categorys) {
            foreach($categorys as $key=>$value) {
                $category = $articleCategoryModel->getRow(array('id'=>$value['parent_id']));
                $categorys[$key]['parentName'] = $category['name'] ? $category['name'] : 0;
            }
        }
        
        $data['result'] = $categorys;

        return $this->fetch('article_category/index', $data);
    }

    public function save()
    {
        //编辑页
        $data = array();
        $articleCategoryModel = model('articleCategory');
        $data['statuss'] = $articleCategoryModel->getStatus();
        $data['categorys'] = $articleCategoryModel->getQueueCategory();
        
        $post = input('post.');

        if ($post) {
            $rule = array(
                'name|名称' => 'require',
                'parent_id|父分类ID' => 'require',
                'sort|排序' => 'require',
                'status|状态' => 'require'
            );
            //自定义错误提示信息
            $msg = array();

            //表单处理
            $param = array(
                'id' => intval(input('post.id')),
                'name' => input('post.name'),
                'parent_id' => input('post.parent_id'),
                'sort' => input('post.sort'),
                'status' => input('post.status'),
                'update_time' => date('Y-m-d H:i:s'),
            );

            $success = FALSE;
            $message = '';

            //创建自定义验证器
            $validate = tc_my_validate($rule, $msg);

            if (!$validate->check($param)) {
                //$message = $validate->getError();
                $message = '表单输入有误';
            } elseif ($param['id'] && $param['parent_id'] == $param['id']) {
                //父ID不能是自己本身
                $message = '父ID不能是自己本身';
            } else {
                //数据插入或更新
                $save = $articleCategoryModel->toSave($param);

                if ($save) {
                    //登陆成功
                    $success = TRUE;
                    $message = '保存成功';
                } else {
                    $message = '保存失败';
                }
            }

            if ($success) {
                tc_to_link(MODULE_URL.'articleCategory/index/');
            } else {
                //输出表单页
                $data['message'] = $message;
                return $this->fetch('article_category/save', $data);
            }
        } else {
            //编辑页
            $id = intval(input('get.id'));
            if ($id) {
                $data['row'] = $articleCategoryModel->getRow(array('id' => $id));
            }
            return $this->fetch('article_category/save', $data);
        }
    }

    public function manage()
    {
        //批量处理
        $data = array();
        $articleCategoryModel = model('articleCategory');
        
        $post = input('post.');
        
        if ($post) {
            $rule = array(
                'ids|IDs' => 'require',
                'manageName|操作选项' => 'require'
            );
            //自定义错误提示信息
            $msg = array();
            
            $manageName = input('post.manageName');
            $ids = tc_clean_ids($_POST['ids']);
            
            $param = array(
                'ids' => $ids,
                'manageName' => $manageName,
            );
            
            $success = false;
            $message = '';

            //创建自定义验证器
            $validate = tc_my_validate($rule, $msg);
            
            if (!$validate->check($param)) {
                $message = $validate->getError();
            } else {
                if ($ids != null) {
                    if ($manageName == 'setStatus') {
                        $status = input('post.setStatus');
                        if ($status !== '') {
                            foreach ($ids as $key => $id) {
                                $param = array(
                                    'id' => $id,
                                    'status' => $status,
                                );
                                $articleCategoryModel->toSave($param);
                            }
                            $message = '操作成功';
                        } else {
                            $message = '设置选项不能为空.';
                        }
                    }
                }
            }
            tc_admin_show_message(MODULE_URL.'articleCategory/index', $message);
            
        } else {
            echo 'No Arguments!';
        }
        
    }
    
    public function delete() {
        $data = array();

        $id = intval(input('get.id'));

        $success = FALSE;
        $message = '';

        if ($id) {
            $articleCategoryModel = model('articleCategory');
            $articleModel = model('article');
            
            $row = $articleCategoryModel->getRow(array('id' => $id));

            if ($row) {
                $articleNum = $articleModel->getCount(array('article_category_id' => $id));
                $childrenNum = $articleCategoryModel->getCount(array('parent_id' => $id));

                if ($articleNum) {
                    $message = '删除失败, 请转移该分类下的文章';
                } elseif ($childrenNum) {
                    $message = '删除失败, 请转移该分类下的子分类';
                } else {
                    //删除记录
                    $delete = $articleCategoryModel->destroy(array('id'=>$id));

                    if ($delete) {
                        $message = '删除成功';
                        $success = TRUE;
                    } else {
                        $message = '删除失败';
                    }
                }
            } else {
                $message = '无效分类ID';
            }
            
        } else {
            $message = '没有分类ID';
        }

        tc_admin_show_message(MODULE_URL . 'articleCategory/index', $message);
    }

}
