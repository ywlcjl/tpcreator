<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用公共文件
// 
//使用普通session ~tc
//session_start();
//定义错误警告级别  ~tc
error_reporting(E_ALL & ~E_NOTICE);

//产品号 ~tc
define('TC_VERSION', '1.0.1');

//应用公共函数
/**
 * 模板内部加载函数
 * @param type $path
 * @param type $vars
 */
function tc_view($path, $vars = array())
{
    if ($vars && is_array($vars)) {
        foreach ($vars as $key => $value) {
            //解析定义变量
            if ($key) {
                $$key = isset($vars[$key]) ? $vars[$key] : FALSE;
            }
        }
    }

    //加载模板文件
    include $path . '.php';
}

/**
 * url跳转 tc_url_jump
 * @param type $url
 */
function tc_to_link($url)
{
    //跳转
    header("Location: $url");
    //redirect('/admin/login/index/', 302);  //有bug
    exit();
}

/**
 * 调用自定义验证类
 * @param type $rules
 * @param type $message
 * @param type $field
 * @return type
 */
function tc_my_validate($rules = array(), $message = array())
{
    return my\MyValidate::getInstance($rules, $message);
}

/**
 * 验证表单具体错误字段
 * @param type $key
 */
function tc_form_error($key)
{
    $myValidate = tc_my_validate();

    if (is_object($myValidate)) {
        return $myValidate->formError($key);
    } else {
        return false;
    }
}

/**
 * 获取提交前的表单
 */
function tc_set_value($key)
{
    return $_POST[$key];
}

/**
 * 获取表单提交失败的输入值, 优先级别为上次提交, 查询值, 表单默认值
 * @param type $setValue 
 * @param type $rowValue
 * @param type $defaultValue
 */
function tc_get_value($setValue, $rowValue = '', $defaultValue = '')
{
    $result = $setValue;
    if ($rowValue !== '') {
        $result = $rowValue;
    } else if ($defaultValue !== '') {
        $result = $defaultValue;
    }

    return $result;
}

/**
 * 过滤id数组
 * @param type $ids
 * @return type
 */
function tc_clean_ids($ids = array())
{
    if ($ids && is_array($ids)) {
        foreach ($ids as $key => $value) {
            $ids[$key] = intval($value);
        }
    }

    return $ids;
}

function tc_get_img_path($path, $size = 'thumb')
{
    $newPath = '';

    if ($size) {
        $newPath = substr($path, 0, -(strlen($path) - strrpos($path, '.'))) . '_' . $size . substr($path, strrpos($path, '.'));
    } else {
        $newPath = $path;
    }

    return $newPath;
}

function tc_get_upload_dir($dirName)
{
    $year = date('Y');
    $month = date('m');
    $day = date('d');

    $path = "uploads/$dirName/$year/$month/$day/";
    $pathYear = "uploads/$dirName/$year/";
    $pathMonth = "uploads/$dirName/$year/$month/";

    if (!is_dir($pathYear)) {
        mkdir($pathYear, 0777);
    }

    if (!is_dir($pathMonth)) {
        mkdir($pathMonth, 0777);
    }

    if (!is_dir($path)) {
        mkdir($path, 0777);
    }

    return $path;
}

function tc_get_setting($key)
{
    $settingModel = model('settingBase');
    $settings = $settingModel->getSetting();
    
    return $settings[$key];
}

function tc_create_img($src, $width = 200, $height = 600, $thumb = '', $quality = 90)
{
    //真实目录路径
    $imgPath = ROOT_PATH . 'public/' . $src;
    //echo $imgPath;
    //exit();
    $image = \think\Image::open($imgPath);
    
    //保存最终图片路径
    $saveImage = $thumb ? tc_get_img_path($src, $thumb) : $src;
    
    //第二个参数 必须 null 作为空选项
    $image->thumb($width, $height)->save($saveImage, null, $quality);
}
