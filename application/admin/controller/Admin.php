<?php

/**
 * 管理员控制器
 */

namespace app\admin\controller;

use think\controller;

class Admin extends controller
{

    protected function _initialize()
    {
        parent::_initialize();

        //检查登陆情况
        tc_admin_login_jump();
        
        //权限检查
        tc_admin_permission_jump(1);
    }

    public function index()
    {
        //列表页
        $data = array();
        //筛选查询条件数组
        $param = array();
        
        $adminModel = model('admin');
        
        //获取状态数组
        $data['statuss'] = $adminModel->getStatus();
        
        //搜索筛选
        $data['filter'] = input('get.filter');
        if($data['filter']) {
            $data['id'] = input('get.id');
            if($data['id']) {
                $param['id'] = $data['id'];
            }
            
            $data['username'] = input('get.username');
            if($data['username']) {
                $param['username'] = array('like', "%{$data['username']}%");
            }
            
            $data['status'] = input('get.status');
            if($data['status'] !== '') {
                $param['status'] = $data['status'];
            }
            
            $data['loginTimeStart'] = input('get.loginTimeStart');
            $data['loginTimeEnd'] = input('get.loginTimeEnd');
            if($data['loginTimeStart'] && $data['loginTimeEnd']) {
                $param['login_time'] = array('between time', array(
                    date('Y-m-d', strtotime($data['loginTimeStart'])), 
                    date('Y-m-d', strtotime($data['loginTimeEnd']))
                ));
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
        $admins = $adminModel->getPage($param, $pagePer, $gets, 'id DESC');
        $adminArray = $admins->toArray();
        
        //结果集
        $data['result'] = $adminArray['data'];
        //分页代码
        $data['page'] = $admins->render();

        return $this->fetch('admin/index', $data);
    }

    public function save()
    {
        //编辑页
        $data = array();
        $adminModel = model('admin');
        $data['statuss'] = $adminModel->getStatus();
        
        $post = input('post.');

        if ($post) {
            $rule = array(
                'username|用户名' => 'require|max:20|min:3',
                'status|状态' => 'require'
            );
            //自定义错误提示信息
            $msg = array();

            //表单处理
            $param = array(
                'id' => intval(input('post.id')),
                'username' => input('post.username'),
                'status' => input('post.status'),
                'update_time' => date('Y-m-d H:i:s'),
            );
            
            //密码特殊处理
            $password = input('post.password');

            $success = FALSE;
            $message = '';

            //创建自定义验证器
            $validate = tc_my_validate($rule, $msg);

            if (!$validate->check($param)) {
                //$message = $validate->getError();
                $message = '表单输入有误';
            } else if ($password != '' && (strlen($password) < 6 || strlen($password) > 20)) {
                $message = '密码长度必须在6~20位';
                $data['passwordError'] = 1;
            } else {
                //密码md5处理
                $param['password'] = md5($password);
                
                //数据插入或更新
                $save = $adminModel->toSave($param);

                if ($save) {
                    //登陆成功
                    $success = TRUE;
                    $message = '保存成功';
                } else {
                    $message = '保存失败';
                }
            }

            if ($success) {
                tc_to_link(MODULE_URL.'admin/index/');
            } else {
                //输出表单页
                $data['message'] = $message;
                return $this->fetch('save', $data);
            }
        } else {
            //编辑页
            $id = intval(input('get.id'));
            if ($id) {
                $data['row'] = $adminModel->getRow(array('id' => $id));
            }
            return $this->fetch('admin/save', $data);
        }
    }

    public function manage()
    {
        //批量处理
        $data = array();
        $adminModel = model('admin');
        
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
                                $adminModel->destroy($param);
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
                                $adminModel->toSave($param);
                            }
                            $message = '操作成功';
                        } else {
                            $message = '设置选项不能为空.';
                        }
                    }
                }
            }
            tc_admin_show_message(MODULE_URL.'admin/index', $message);
            
        } else {
            echo 'No Arguments!';
        }
    }
    
    public function addPermission() {
        //添加权限
        $data = array();
        
        $adminModel = model('admin');
        $adminPermissionModel = model('adminPermission');
                
        $post = input('post.');
        
        if ($post) {
            $rule = array(
                'id|用户ID' => 'require',
            );
            //自定义错误提示信息
            $msg = array();
            
            //权限数组
            $permissions = tc_clean_ids($_POST['permission']);

            //权限字段
            if ($permissions != null) {
                $adminPermission = implode('|', $permissions);
            } else {
                $adminPermission = '';
            }

            $param = array(
                'id' => intval(input('post.id')),
                'admin_permission' => $adminPermission,
                'update_time' => date('Y-m-d H:i:s'),
            );

            $success = FALSE;
            $message = '';

            //创建自定义验证器
            $validate = tc_my_validate($rule, $msg);
            
            if (!$validate->check($param)) {
                //表单填写错误
                $message = $validate->getError();
            } else {
                //保存用户权限
                $save = $adminModel->toSave($param);

                if ($save) {
                    $message = '保存成功';
                    $success = true;
                } else {
                    $message = '保存失败';
                }
            }

            //必须跳转
            tc_admin_show_message(MODULE_URL . "admin/addPermission/?adminId={$param['id']}", $message);
        } else {
            //显示添加用户权限表单
            $adminId = intval(input('get.adminId'));
            if ($adminId) {
                
                //用户权限
                $data['admin'] = $adminModel->getRow(array('id' => $adminId));
                $data['permissionArray'] = array();
                if ($data['admin']['admin_permission'] != null) {
                    //拆分权限数组
                    $data['permissionArray'] = explode('|', $data['admin']['admin_permission']);
                }

                //权限数组
                $data['permissionList'] = $adminPermissionModel->getResult(array('status' => 1), '', '', 'id ASC');

                return $this->fetch('admin/add_permission', $data);
            } else {
                tc_admin_show_message(MODULE_URL . 'admin', '无效管理员ID');
            }
        }
    }

}
