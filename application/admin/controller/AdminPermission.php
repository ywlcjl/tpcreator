<?php

/**
 * 管理员控制器
 */

namespace app\admin\controller;

use think\controller;

class AdminPermission extends controller
{

    protected function _initialize()
    {
        parent::_initialize();

        //检查登陆情况
        tc_admin_login_jump();

        //检查权限
        tc_admin_permission_jump(1);
    }

    public function index()
    {
        //列表页
        $data = array();
        //筛选查询条件数组
        $param = array();
        
        $adminPermissionModel = model('adminPermission');
        
        //获取状态数组
        $data['statuss'] = $adminPermissionModel->getStatus();
        
        //搜索筛选
        $data['filter'] = input('get.filter');
        if($data['filter']) {
            $data['id'] = input('get.id');
            if($data['id']) {
                $param['id'] = $data['id'];
            }
            
            $data['name'] = input('get.name');
            if($data['name']) {
                $param['name'] = array('like', "%{$data['name']}%");
            }
            
            $data['status'] = input('get.status');
            if($data['status'] !== '') {
                $param['status'] = $data['status'];
            }
            
            $data['createTimeStart'] = input('get.createTimeStart', TRUE);
            $data['createTimeEnd'] = input('get.createTimeEnd', TRUE);
            if($data['createTimeStart'] && $data['createTimeEnd']) {
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
        $adminPermissions = $adminPermissionModel->getPage($param, $pagePer, $gets, 'id DESC');
        $adminPermissionArray = $adminPermissions->toArray();
        
        //结果集
        $data['result'] = $adminPermissionArray['data'];
        //分页生成
        $data['page'] = $adminPermissions->render();

        return $this->fetch('admin_permission/index', $data);
    }

    public function save()
    {
        //编辑页
        $data = array();
        $adminPermissionModel = model('adminPermission');
        $data['statuss'] = $adminPermissionModel->getStatus();
        
        $post = input('post.');

        if ($post) {
            $rule = array(
                'name|名称' => 'require',
                'desc_txt|描述' => 'require',
                'status|状态' => 'require'
            );
            //自定义错误提示信息
            $msg = array();

            //表单处理
            $param = array(
                'id' => intval(input('post.id')),
                'name' => input('post.name'),
                'desc_txt' => input('post.desc_txt'),
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
            } else {
                //数据插入或更新
                $save = $adminPermissionModel->toSave($param);

                if ($save) {
                    //登陆成功
                    $success = TRUE;
                    $message = '保存成功';
                } else {
                    $message = '保存失败';
                }
            }

            if ($success) {
                tc_to_link(MODULE_URL.'adminPermission/index/');
            } else {
                //输出表单页
                $data['message'] = $message;
                return $this->fetch('admin_permission/save', $data);
            }
        } else {
            //编辑页
            $id = intval(input('get.id'));
            if ($id) {
                $data['row'] = $adminPermissionModel->getRow(array('id' => $id));
            }
            return $this->fetch('admin_permission/save', $data);
        }
    }

    public function manage()
    {
        //批量处理
        $data = array();
        $adminPermissionModel = model('adminPermission');
        
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
                                    'is_root' => 0,
                                );
                                //删除记录
                                $adminPermissionModel->destroy($param);
                            }
                        }
                        $message = '删除成功';
                    } elseif ($manageName == 'setStatus') {
                        $status = input('post.setStatus');
                        if ($status !== '') {
                            foreach ($ids as $key => $id) {
                                $param = array(
                                    'id' => $id,
                                    'status' => $status,
                                );
                                $adminPermissionModel->toSave($param);
                            }
                            $message = '操作成功';
                        } else {
                            $message = '设置选项不能为空.';
                        }
                    }
                }
            }
            tc_admin_show_message(MODULE_URL.'adminPermission/index', $message);
            
        } else {
            echo 'No Arguments!';
        }
        
    }

}
