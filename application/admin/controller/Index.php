<?php

/**
 * 后台首页
 */

namespace app\admin\controller;

use \think\Controller;

class Index extends Controller
{

    public function _initialize()
    {
        parent::_initialize();
        //检查是否登陆
        tc_admin_login_jump();
    }

    public function index()
    {
        //后台首页
        return $this->fetch('index');
    }

    public function showMessage()
    {
        $data = array();
        
        $url = base64_decode(input('get.url'));
        $message = input('get.message');
        $second = input('get.second');

        $data['url'] = $url ? $url : MODULE_URL.'index';
        $data['message'] = $message ? $message : '无效信息提示';
        $data['second'] = $second ? $second : 5;
        
        return $this->fetch('index/_show_message', $data);
    }
    
    public function maintain()
    {
        //维护页
        tc_admin_permission_jump(1);
        $data = array();

        return $this->fetch('index/maintain', $data);
    }
    
    public function clearCache()
    {
        tc_admin_permission_jump(1);
        
        //清除全部缓存
        cache(null);
        
        tc_admin_show_message(MODULE_URL.'index/maintain', '缓存已全部清空.');
    }
}
