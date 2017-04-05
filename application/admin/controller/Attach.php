<?php

/**
 * attach控制器
 */

namespace app\admin\controller;

use think\controller;

class Attach extends controller
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
        
        $attachModel = model('attach');
        $articleModel = model('article');
        
        $data['articles'] = $articleModel->getResult(array(), '', '', 'id DESC');
        
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
            
            $data['orig_name'] = input('get.orig_name');
            if($data['orig_name']) {
                $param['orig_name'] = array('like', "%{$data['orig_name']}%");
            }
            
            $data['type'] = input('get.type');
            if($data['type'] !== '') {
                $param['type'] = $data['type'];
            }
            
            $data['article_id'] = input('get.article_id');
            if($data['article_id'] !== '') {
                $param['article_id'] = $data['article_id'];
            }
            
//            $data['status'] = input('get.status');
//            if($data['status'] !== '') {
//                $param['status'] = $data['status'];
//            }
            
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
        $attachs = $attachModel->getPage($param, $pagePer, $gets, 'id DESC');
        $attachArray = $attachs->toArray();
        
        //结果集
        $data['result'] = $attachArray['data'];
        //分页生成
        $data['page'] = $attachs->render();

        return $this->fetch('attach/index', $data);
    }

    public function save()
    {
        //编辑页
        $data = array();
        $attachModel = model('attach');
        $articleModel = model('article');
        
        $data['articles'] = $articleModel->getResult(array(), '', '', 'id DESC');
        
        $post = input('post.');

        if ($post) {
            $rule = array(
                'name|名称' => 'require',
                'orig_name|原名称' => 'require',
                'path|路径' => 'require',
                'type|类型' => 'require',
                'article_id|文章ID' => 'require',
            );
            //自定义错误提示信息
            $msg = array();

            //表单处理
            $param = array(
                'id' => intval(input('post.id')),
                'name' => input('post.name'),
                'orig_name' => input('post.orig_name'),
                'path' => input('post.path'),
                'type' => input('post.type'),
                'article_id' => input('post.article_id'),
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
                $save = $attachModel->toSave($param);

                if ($save) {
                    $success = TRUE;
                    $message = '保存成功';
                } else {
                    $message = '保存失败';
                }
            }

            if ($success) {
                tc_to_link(MODULE_URL.'attach/index/');
            } else {
                //输出表单页
                $data['message'] = $message;
                return $this->fetch('attach/save', $data);
            }
        } else {
            //编辑页
            $id = intval(input('get.id'));
            if ($id) {
                $data['row'] = $attachModel->getRow(array('id' => $id));
            }
            return $this->fetch('attach/save', $data);
        }
    }

    public function manage()
    {
        //批量处理
        $data = array();
        $attachModel = model('attach');
        
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
                                //获取文件路径信息
                                $attach = $attachModel->getRow(array('id'=>$id));
                                
                                //删除记录
                                $delete = $attachModel->destroy($param);
                                
                                //物理删除图片
                                if ($delete) {
                                    //删除附件
                                    $imgPath = ROOT_PATH . 'public/' . $attach['path'];
                                    $imgPathThumb = ROOT_PATH . 'public/' . tc_get_img_path($attach['path'], 'thumb');
                                    if (is_file($imgPath)) {
                                        unlink($imgPath);
                                    }
                                    if (is_file($imgPathThumb)) {
                                        unlink($imgPathThumb);
                                    }
                                }
                                
                            }
                        }
                        $message = '删除成功';
                    } elseif ($manageName == 'set_article_id') {
                        $articleId = input('post.set_article_id');
                        if ($articleId !== '') {
                            foreach ($ids as $key => $id) {
                                $param = array(
                                    'id' => $id,
                                    'article_id' => $articleId,
                                );
                                $attachModel->toSave($param);
                            }
                            $message = '操作成功';
                        } else {
                            $message = '设置选项不能为空.';
                        }
                    }
                }
            }
            tc_admin_show_message(MODULE_URL.'attach/index', $message);
            
        } else {
            echo 'No Arguments!';
        }
        
    }
    
    public function ajaxDelete() {
        $data = array();
        
        $attachModel = model('attach');
        
        $id = input('post.id');

        $success = 0;
        $message = '';
        
        if ($id) {
            $attach = $attachModel->getRow(array('id'=>$id));
            $delete = false;
            if ($attach) {
                $delete = $attachModel->destroy(array('id' => $id));
            }
            
            if ($delete) {
                //删除附件
                $imgPath = ROOT_PATH . 'public/' . $attach['path'];
                $imgPathThumb = ROOT_PATH . 'public/' . tc_get_img_path($attach['path'], 'thumb');
                if(is_file($imgPath)) {
                    unlink($imgPath);
                }
                if(is_file($imgPathThumb)) {
                    unlink($imgPathThumb);
                }
                
                $success = 1;
                $message = '删除成功';
            } else {
                $message = '删除失败';
            }
        } else {
            $message = 'ID有误';
        }

        $data['success'] = $success;
        $data['message'] = $message;

        return json($data);
    }
    
    public function ajaxUpload() {
        $data = array();

        $attachModel = model('attach');
        
        $success = 0;
        $message = '';
        $data['result'] = array();
        
        $post = input('post.');
        
        if ($post) {
            //附件目录
            $pathDir = tc_get_upload_dir('attach');
            
            //thinkphp 默认上传类
            $file = request()->file('image');
            $info = $file->validate(['size'=>2097152,'ext'=>'jpg,png,gif,jpe,jpeg'])->rule('uniqid')->move(ROOT_PATH . 'public' . DS . $pathDir);
            
            if ($info) {
                $uploadData = $info->getInfo();
                
                //上传后文件
                $imgFile = $info->getSaveName();
                
                //完整路径图片路径
                $imgPath = $pathDir . $imgFile;
                
                //获取图片大小
                $imgSize = getimagesize($imgPath);
                //宽度
                $imgWidth = $imgSize[0];
                $imgHeight = $imgSize[1];
                
                $width = tc_get_setting('attach_thumb_width');
                $height = tc_get_setting('attach_thumb_height');
                $quality = tc_get_setting('attach_quality');

                //宽度最小
                if ($imgWidth < $width) {
                    $width = $imgWidth;
                }
                
                //生成缩略图
                tc_create_img($imgPath, $width, $height, 'thumb', $quality);
                
                //写入数据库
                $param = array(
                    'name' => $imgFile,
                    'orig_name' => $uploadData['name'],
                    'path' => $imgPath,
                    'type' => $info->getExtension(),
                );
                $save = $attachModel->toSave($param, true);

                $attachId = $save !== true ? $save : '';
                
                if ($attachId) {
                    $success = 1;
                    $message = '上传成功';
                    
                    $data['attachId'] = $attachId;
                    $data['orig_name'] = $uploadData['name'];
                    $data['picUrl'] = $pathDir . $imgFile;
                    $data['picUrlThumb'] = $pathDir . tc_get_img_path($imgFile, 'thumb');
                } else {
                    $message = '上传失败';
                }
            } else {
                $message = $file->getError();
            }

        } else {
            $message = 'none';
        }

        $data['success'] = $success;
        $data['message'] = $message;

        return json($data);
    }
    

}
