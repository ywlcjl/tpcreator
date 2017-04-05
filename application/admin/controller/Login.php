<?php

/**
 * 后台登陆
 */

namespace app\admin\controller;

use think\Controller;

class Login extends Controller
{

    protected function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        //登录页
        $data = array();

        return $this->fetch('login/index', $data);
    }

    public function signIn()
    {
        //登陆请求页
        $data = array();

        //是否有表单提交
        $post = input('post.');

        if ($post) {
            //验证器处理
            $rule = array(
                'username|用户名' => 'require|max:20|min:3',
                'password|密码' => 'require',
                'captcha|验证码' => 'require',
            );
            //自定义错误提示信息
            $msg = array();

            //表单处理
            $param = array(
                'username' => input('post.username'),
                'password' => input('post.password'),
            );
            
            $captcha = input('post.captcha');
            
            //验证规则数组
            $postData = $param;
            $postData['captcha'] = $captcha;

            $success = false;
            $message = '';

            //创建自定义验证器
            $validate = tc_my_validate($rule, $msg);

            //多重验证 $validate->batch()->check($postData)
            //单个验证 $validate->check($postData)
            if (!$validate->check($postData)) {
                //$message = $validate->getError();
                $message = '表单输入有误.';
            } else if (!captcha_check($captcha)) {
                //验证码验证
                $message = '验证码输入有误';
                $data['captchaError'] = 1;
            } else {
                $adminModel = model('admin');

                //密码md5处理
                $param['password'] = md5($param['password']);
                
                //数据库查询参数
                $admin = $adminModel->getRow($param);

                if ($admin) {
                    //登陆成功
                    $success = TRUE;
                    $message = '登陆成功';

                    //配置session
                    session('adminId', $admin['id']);
                    session('adminUsername', $admin['username']);
                    $adminPermissions = $admin['admin_permission'] ? explode('|', $admin['admin_permission']) : array();
                    session('adminPermissions', $adminPermissions);
                    
                } else {
                    
                    $message = '用户密码有误';
                }
            }

            if ($success) {
                tc_to_link('/admin/index/');
            } else {
                //输出模板登陆页
                $data['message'] = $message;
                return $this->fetch('login/index', $data);
            }
        } else {
            tc_to_link('/admin/login');
        }
    }

    public function logout() {
        session('adminId', null);
        session('adminUsername', null);
        //unset($_SESSION['adminPermission']);

        tc_to_link('/admin/login');
    }
}
