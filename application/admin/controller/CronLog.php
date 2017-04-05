<?php

/**
 * cron_log 控制器
 */

namespace app\admin\controller;

use think\controller;

class CronLog extends controller 
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
        $data = array();
        $param = array();
        
        $cronLogModel = model('cronLog');
        $adminModel = model('admin');

        $data['types'] = $cronLogModel->getType();
        $data['admins'] = $adminModel->getResult(array(), '', '', 'id DESC');
        $data['statuss'] = $cronLogModel->getStatus();

        //搜索筛选
        $data['filter'] = input('get.filter');
        if($data['filter']) {

            $data['id'] = input('get.id');
            if($data['id'] !== '') {
                $param['id'] = $data['id'];
            }

            $data['type'] = input('get.type');
            if($data['type'] !== '') {
                $param['type'] = $data['type'];
            }

            $data['memo'] = input('get.memo');
            if($data['memo']) {
                $param['memo'] = array('like', "%memo%");
            }

            $data['admin_id'] = input('get.admin_id');
            if($data['admin_id'] !== '') {
                $param['admin_id'] = $data['admin_id'];
            }

            $data['status'] = input('get.status');
            if($data['status'] !== '') {
                $param['status'] = $data['status'];
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
        $createTimes = $cronLogModel->getPage($param, $pagePer, $gets, 'id DESC');
        $createTimeArray = $createTimes->toArray();
        
        //结果集
        $data['result'] = $createTimeArray['data'];
        //分页生成
        $data['page'] = $createTimes->render();

        return $this->fetch('cron_log/index', $data);
    }

    public function save() {
        $data = array();

        $cronLogModel = model('cronLog');
        $adminModel = model('admin');

        $data['types'] = $cronLogModel->getType();
        $data['admins'] = $adminModel->getResult(array(), '', '', 'id DESC');
        $data['statuss'] = $cronLogModel->getStatus();
 
        $post = input('post.');
        
        if ($post) {
            $rule = array(
                'type|类型' => 'require',
                'memo|日志' => 'require',
                'admin_id|管理员ID' => 'require',
                'status|状态' => 'require',
            );
            
            //自定义错误提示信息
            $msg = array();

            $param = array(
                'id' => intval(input('post.id')),
                'type' => input('post.type'),
                'memo' => input('post.memo'),
                'admin_id' => input('post.admin_id'),
                'status' => input('post.status'),
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
                $save = $cronLogModel->toSave($param);

                if ($save) {
                    $success = TRUE;
                    $message = '保存成功';
                } else {
                    $message = '保存失败';
                }
            }

            if ($success) {
                tc_to_link(MODULE_URL.'cronLog/index/');
            } else {
                //输出表单页
                $data['message'] = $message;
                return $this->fetch('cron_log/save', $data);
            }
        } else {
            //编辑页
            $id = intval(input('get.id'));
            
            if ($id) {
                $data['row'] = $cronLogModel->getRow(array('id' => $id));
            }
            return $this->fetch('cron_log/save', $data);
        }
    }

    public function manage() 
    {
        //批量处理
        $data = array();
        $cronLogModel = model('cronLog');
        
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
                                $cronLogModel->destroy($param);
                            }
                        }
                        $message = '删除成功';
                    } elseif ($manageName == 'set_type') {
                        $setValue = input('post.set_type');
                        if ($setValue !== '') {
                            foreach ($ids as $key => $id) {
                                $param = array(
                                    'id' => $id,
                                    'type' => $setValue,
                                );
                                $cronLogModel->toSave($param);
                            }
                            $message = '操作成功';
                        } else {
                            $message = '设置值不能为空.';
                        }
                    } elseif ($manageName == 'set_admin_id') {
                    
                        $setValue = input('post.set_admin_id');
                        if ($setValue !== '') {
                            foreach ($ids as $key => $id) {
                                $param = array(
                                    'id' => $id,
                                    'admin_id' => $setValue,
                                );
                                $cronLogModel->toSave($param);
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
                                $cronLogModel->toSave($param);
                            }
                            $message = '操作成功';
                        } else {
                            $message = '设置值不能为空.';
                        }
                    }
                }
            }
            tc_admin_show_message(MODULE_URL.'cronLog/index', $message);
        } else {
            echo 'No Arguments!';
        }
    }
}