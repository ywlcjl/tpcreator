<?php

/**
 * example 控制器
 */

namespace app\admin\controller;

use think\controller;

class Example extends controller 
{

    protected function _initialize()
    {
        parent::_initialize();

        //检查登陆情况
        tc_admin_login_jump();

        //检查权限
        //tc_admin_permission_jump(1);
    }
                
    public function index() 
    {
        $data = array();
        $param = array();
        
        $exampleModel = model('example');
        $articleModel = model('article');

        $data['articles'] = $articleModel->getResult(array(), '', '', 'id DESC');
        $data['statuss'] = $exampleModel->getStatus();

        //搜索筛选
        $data['filter'] = input('get.filter');
        if($data['filter']) {

            $data['id'] = input('get.id');
            if($data['id'] !== '') {
                $param['id'] = $data['id'];
            }

            $data['name'] = input('get.name');
            if($data['name']) {
                $param['name'] = array('like', "%name%");
            }

            $data['desc_txt'] = input('get.desc_txt');
            if($data['desc_txt']) {
                $param['desc_txt'] = array('like', "%desc_txt%");
            }

            $data['priceMin'] = input('get.priceMin');
            if ($data['priceMin'] !== '') {
                $param['price'] = array('>=', $data['priceMin']);
            }
            $data['priceMax'] = input('get.priceMax');
            if ($data['priceMax'] !== '' && $data['priceMin'] !== '') {
                $param['price'] = array(array('>=', $data['priceMin']), array('<',  $data['priceMax']));
            } else if ($data['priceMax'] !== '') {
                $param['price'] = array('<',  $data['priceMax']);
            }

            $data['article_id'] = input('get.article_id');
            if($data['article_id'] !== '') {
                $param['article_id'] = $data['article_id'];
            }

            $data['status'] = input('get.status');
            if($data['status'] !== '') {
                $param['status'] = $data['status'];
            }

            $data['postTimeStart'] = input('get.postTimeStart');
            $data['postTimeEnd'] = input('get.postTimeEnd');
            if ($data['postTimeStart'] && $data['postTimeEnd']) {
                $param['post_time'] = array('between time', array(
                    date('Y-m-d', strtotime($data['postTimeStart'])),
                    date('Y-m-d', strtotime($data['postTimeEnd']))
                ));
            }

            $data['updateTimeStart'] = input('get.updateTimeStart');
            $data['updateTimeEnd'] = input('get.updateTimeEnd');
            if ($data['updateTimeStart'] && $data['updateTimeEnd']) {
                $param['update_time'] = array('between time', array(
                    date('Y-m-d', strtotime($data['updateTimeStart'])),
                    date('Y-m-d', strtotime($data['updateTimeEnd']))
                ));
            }

            $data['createTimeStart'] = input('get.createTimeStart');
            $data['createTimeEnd'] = input('get.createTimeEnd');
            if ($data['createTimeStart'] && $data['createTimeEnd']) {
                $param['create_time'] = array('between time', array(
                    date('Y-m-d', strtotime($data['createTimeStart'])),
                    date('Y-m-d', strtotime($data['createTimeEnd']))
                ));
            }

        }

        //获取所有筛选get参数
        $gets = input('get.');
               
        //每页显示数量
        $pagePer = 20;
        
        //获取分页结果
        $createTimes = $exampleModel->getPage($param, $pagePer, $gets, 'id DESC');
        $createTimeArray = $createTimes->toArray();
        
        //结果集
        $data['result'] = $createTimeArray['data'];
        //分页生成
        $data['page'] = $createTimes->render();

        return $this->fetch('example/index', $data);
    }

    public function save() {
        $data = array();

        $exampleModel = model('example');
        $articleModel = model('article');

        $data['articles'] = $articleModel->getResult(array(), '', '', 'id DESC');
        $data['statuss'] = $exampleModel->getStatus();
 
        $post = input('post.');
        
        if ($post) {
            $rule = array(
                'name|名称' => 'require',
                'desc_txt|描述' => 'require',
                'price' => 'require',
                'article_id|文章ID' => 'require',
                'status|状态' => 'require',
                'post_time|发布时间' => 'require',
            );
            
            //自定义错误提示信息
            $msg = array();

            $param = array(
                'id' => intval(input('post.id')),
                'name' => input('post.name'),
                'desc_txt' => input('post.desc_txt'),
                'price' => input('post.price'),
                'article_id' => input('post.article_id'),
                'status' => input('post.status'),
                'post_time' => input('post.post_time'),
                'update_time' => date('Y-m-d H:i:s'),

        );
            $success = FALSE;
            $message = '';

            //创建自定义验证器
            $validate = tc_my_validate($rule, $msg);

            if (!$validate->check($param)) {
                //$message = $validate->getError();
                $message = '表单填写有误';
                
            } else {
                //数据插入或更新
                $save = $exampleModel->toSave($param);

                if ($save) {
                    $success = TRUE;
                    $message = '保存成功';
                } else {
                    $message = '保存失败';
                }
            }

            if ($success) {
                tc_to_link(MODULE_URL.'example/index/');
            } else {
                //输出表单页
                $data['message'] = $message;
                return $this->fetch('example/save', $data);
            }
        } else {
            //编辑页
            $id = intval(input('get.id'));
            
            if ($id) {
                $data['row'] = $exampleModel->getRow(array('id' => $id));
            }
            return $this->fetch('example/save', $data);
        }
    }

    public function manage() 
    {
        //批量处理
        $data = array();
        $exampleModel = model('example');
        
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
                    if ($manageName == 'delete') {
                        //删除记录
                        foreach ($ids as $key => $id) {
                            if ($id) {
                                $param = array(
                                    'id' => $id,
                                );
                                //删除记录
                                $exampleModel->destroy($param);
                            }
                        }
                        $message = '删除成功';
                    } elseif ($manageName == 'set_article_id') {
                    
                        $setValue = input('post.set_article_id');
                        if ($setValue !== '') {
                            foreach ($ids as $key => $id) {
                                $param = array(
                                    'id' => $id,
                                    'article_id' => $setValue,
                                );
                                $exampleModel->toSave($param);
                            }
                            $message = '操作成功';
                        } else {
                            $message = '设置值不能为空.';
                        }
                    } elseif ($manageName == 'set_status') {
                        $setValue = input('post.set_status');
                        if ($setValue !== '') {
                            foreach ($ids as $key => $id) {
                                $param = array(
                                    'id' => $id,
                                    'status' => $setValue,
                                );
                                $exampleModel->toSave($param);
                            }
                            $message = '操作成功';
                        } else {
                            $message = '设置值不能为空.';
                        }
                    }
                }
            }
            tc_admin_show_message(MODULE_URL.'example/index', $message);
        } else {
            echo 'No Arguments!';
        }
    }
}