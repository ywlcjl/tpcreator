<?php

/**
 * 模块公共文件
 */
//模块定义  ~tc
define('MODULE_NAME', 'admin');
//模块目录路径 用于模板加载路径 ~tc
define('MODULE_VIEW', APP_PATH . MODULE_NAME . '/view/');
//模块链接前缀 用户模板链接前缀路径 ~tc
define('MODULE_URL', '/' . MODULE_NAME . '/');


//模块内公共函数

/**
 * 检查后台用户登陆情况
 */
function tc_admin_login_jump()
{
    if (!session('adminId')) {
        //跳转到登陆页
        tc_to_link('/admin/login/index');
    }
}

/**
 * 检查是否登陆
 * @return boolean
 */
function tc_admin_check_login()
{
    $adminId = session('adminId');
    if (isset($adminId) && $adminId > 0) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * 后台跳转信息提示
 * @param type $url
 * @param type $message
 * @param type $second
 */
function tc_admin_show_message($url, $message, $second = 5)
{
    //替换问好为^
    $url = base64_encode($url);

    $jumpUrl = MODULE_URL . "index/showMessage/?url=$url&message=$message&second=$second";
    tc_to_link($jumpUrl);
}

/**
 * 检查后台用户权限并跳转
 * @param type $permission
 */
function tc_admin_permission_jump($permission)
{
    if (!tc_admin_check_permission($permission)) {
        $message = '权限不足';
        tc_admin_show_message(MODULE_URL . '/index', $message);
    }
}

/**
 * 检查后台用户权限
 * @param type $permission
 * @return boolean
 */
function tc_admin_check_permission($permission)
{
    if (tc_admin_check_login() && $permission > 0) {
        
        $adminPermissions = session('adminPermissions');
        
        if ($adminPermissions 
                && is_array($adminPermissions) 
                && in_array($permission, $adminPermissions)) {
            
            return TRUE;
        }
    }
    return FALSE;
}
