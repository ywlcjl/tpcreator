<?php

/**
 * 管理员控制器
 */

namespace app\admin\controller;

use think\controller;

class Setting extends controller
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
        
        $settingModel = model('setting');
        
        //获取状态数组
        $data['statuss'] = $settingModel->getStatus();
        
        //获取分页结果
        $settings = $settingModel->getResult($param, '', '', 'id ASC');
        
        //结果集
        $data['result'] = $settings;

        return $this->fetch('setting/index', $data);
    }

    public function save()
    {
        //编辑页
        $data = array();
        $settingModel = model('setting');
        $data['statuss'] = $settingModel->getStatus();
        
        $post = input('post.');

        if ($post) {
            $rule = array(
                'key|键' => 'require',
                'value|值' => 'require',
                'txt|描述' => 'require',
                'status|状态' => 'require',
            );
            //自定义错误提示信息
            $msg = array();

            //表单处理
            $param = array(
                'id' => intval(input('post.id')),
                'key' => input('post.key'),
                'value' => input('post.value'),
                'txt' => input('post.txt'),
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
                $save = $settingModel->toSave($param);

                if ($save) {
                    //登陆成功
                    $success = TRUE;
                    $message = '保存成功';
                } else {
                    $message = '保存失败';
                }
            }

            if ($success) {
                tc_to_link(MODULE_URL.'setting/index/');
            } else {
                //输出表单页
                $data['message'] = $message;
                return $this->fetch('setting/save', $data);
            }
        } else {
            //编辑页
            $id = intval(input('get.id'));
            if ($id) {
                $data['row'] = $settingModel->getRow(array('id' => $id));
            }
            return $this->fetch('setting/save', $data);
        }
    }

}
