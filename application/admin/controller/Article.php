<?php

/**
 * 文章控制器
 */

namespace app\admin\controller;

use think\controller;

class Article extends controller
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
        
        $articleModel = model('article');
        $articleCategoryModel = model('articleCategory');
        $adminModel = model('admin');
        
        //获取状态数组
        $data['statuss'] = $articleModel->getStatus();
        $data['tops'] = $articleModel->getTop();
        $data['admins'] = $adminModel->getResult(array(), '', '', 'id ASC');
        $data['categorys'] = $articleCategoryModel->getQueueCategory();
        
        //搜索筛选
        $data['filter'] = input('get.filter');
        if($data['filter']) {
            $data['id'] = input('get.id');
            if($data['id']) {
                $param['id'] = $data['id'];
            }
            
            $data['title'] = input('get.title');
            if($data['title']) {
                $param['title'] = array('like', "%{$data['title']}%");
            }
            
            $data['author'] = input('get.author');
            if($data['author']) {
                $param['author'] = $data['author'];
            }

            $data['source'] = input('get.source');
            if($data['source']) {
                $param['source'] = $data['source'];
            }
            
            $data['article_category_id'] = input('get.article_category_id');
            if($data['article_category_id']) {
                $param['article_category_id'] = $data['article_category_id'];
            }
            
            $data['admin_id'] = input('get.admin_id');
            if($data['admin_id']) {
                $param['admin_id'] = $data['admin_id'];
            }
            
            $data['top'] = input('get.top');
            if($data['top'] !== '') {
                $param['top'] = $data['top'];
            }
            
            $data['status'] = input('get.status');
            if($data['status'] !== '') {
                $param['status'] = $data['status'];
            }

            $data['postTimeStart'] = input('get.postTimeStart', TRUE);
            $data['postTimeEnd'] = input('get.postTimeEnd', TRUE);
            if($data['postTimeStart'] && $data['postTimeEnd']) {
                $param['post_time'] = array('between time', array(
                    date('Y-m-d', strtotime($data['postTimeStart'])),
                    date('Y-m-d', strtotime($data['postTimeEnd']))
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
        $articles = $articleModel->getPage($param, $pagePer, $gets, 'id DESC');
        
        //对象转换成数组
        $articleArray = $articles->toArray();
        $result = $articleArray['data'];
        
        if($result) {
            foreach($result as $key=>$value) {
                $category = $articleCategoryModel->getRow(array('id'=>$value['article_category_id']));
                $result[$key]['categoryName'] = $category ? $category['name'] : '未分类';
                
                $admin = $adminModel->getRow(array('id'=>$value['admin_id']));
                $result[$key]['adminName'] = $admin ? $admin['username'] : '暂无';
            }
        }
        
        //结果集
        $data['result'] = $result;
        //分页生成
        $data['page'] = $articles->render();

        return $this->fetch('article/index', $data);
    }

    public function save()
    {
        //编辑页
        $data = array();
        
        $articleModel = model('article');
        $articleCategoryModel = model('articleCategory');
        $adminModel = model('admin');
        $attachModel = model('attach');
        
        //获取状态数组
        $data['statuss'] = $articleModel->getStatus();
        $data['tops'] = $articleModel->getTop();
        $data['admins'] = $adminModel->getResult(array(), '', '', 'id ASC');
        $data['categorys'] = $articleCategoryModel->getQueueCategory();
        
        $post = input('post.');

        if ($post) {
            $rule = array(
                'title|标题' => 'require',
                'content|内容' => 'require',
                'top|推荐' => 'require',
                'article_category_id|分类' => 'require',
                'status|状态' => 'require'
            );
            //自定义错误提示信息
            $msg = array();

            //表单处理
            $param = array(
                'id' => intval(input('post.id')),
                'title' => input('post.title'),
                'author' => input('post.author'),
                'source' => input('post.source'),
                'cover_pic' => input('post.cover_pic'),
                'desc_txt' => input('post.desc_txt', '', 'htmlspecialchars'),
                'content' => input('post.content', '', 'htmlspecialchars'),
                'hop_link' => input('post.hop_link'),
                'top' => input('post.top'),
                'article_category_id' => input('post.article_category_id'),
                'status' => input('post.status'),
                'update_time' => date('Y-m-d H:i:s'),
            );

            if(!$param['id']) {
                $param['admin_id'] = session('adminId');
            }
            
            //更新发布时间
            $postTime = input('post.postTime');
            if($postTime) {
                $param['post_time'] = input('post.post_time');
            }
            
            //附件id
            $attachIds = tc_clean_ids($_POST['attachId']);
            
            $success = FALSE;
            $message = '';

            //创建自定义验证器
            $validate = tc_my_validate($rule, $msg);

            if (!$validate->check($param)) {
                //$message = $validate->getError();
                $message = '表单输入有误';
            } else {
                //数据插入或更新, 如果是插入数据,获取返回的自增id
                $save = $articleModel->toSave($param, true);

                if ($save) {
                    //获取insertid
                    $insertId = $save !== true ? $save : '';
                    $articleId = $param['id'] ? $param['id'] : $insertId;

                    //处理附件
                    if($articleId && $attachIds && is_array($attachIds)) {
                        foreach($attachIds as $attachId) {
                            $attachModel->toSave(array('id'=>$attachId, 'article_id'=>$articleId));
                        }
                    }
                    
                    $success = true;
                    $message = '保存成功';
                } else {
                    $message = '保存失败';
                }
            }

            if ($success) {
                tc_admin_show_message(MODULE_URL.'article/index/', $message);
            } else {
                //保留已上传的附件
                $attachs = array();
                //已存在的附件
                $attachsHad = array();
                if($param['id']) {
                    $attachsHad = $attachModel->getResult(array('article_id' => $param['id']));
                }
                //如果不为空则添加到附件数组
                if($attachsHad) {
                    $attachs = $attachsHad;
                }
                
                //已提交的附件
                if($attachIds && is_array($attachIds)) {
                    foreach($attachIds as $attachId) {
                        $attach = $attachModel->getRow(array('id'=>$attachId));
                        if($attach) {
                            //需要生成表单,作为二次提交
                            $attach['setInput'] = 1;
                            $attachs[] = $attach;
                        }
                    }
                }
                $data['attachs'] = $attachs;
                
                $data['message'] = $message;
                return $this->fetch('article/save', $data);
            }
        } else {
            //编辑页
            $id = intval(input('get.id'));
            if ($id) {
                $data['row'] = $articleModel->getRow(array('id' => $id));
                $data['attachs'] = $attachModel->getResult(array('article_id' => $id));
            }
            return $this->fetch('article/save', $data);
        }
    }

    public function manage()
    {
        //批量处理
        $data = array();
        $articleModel = model('article');
        
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
                                $articleModel->destroy($param);
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
                                $articleModel->toSave($param);
                            }
                            $message = '操作成功';
                        } else {
                            $message = '设置选项不能为空.';
                        }
                    } elseif ($manageName == 'setTop') {
                        $top = input('post.setTop');
                        if ($top !== '') {
                            foreach ($ids as $key => $id) {
                                $param = array(
                                    'id' => $id,
                                    'top' => $top,
                                );
                                $articleModel->toSave($param);
                            }
                            $message = '操作成功';
                        } else {
                            $message = '设置不能为空.';
                        }
                    } elseif ($manageName == 'setCategoryId') {
                        $setCategoryId = intval(input('post.setCategoryId'));

                        foreach ($ids as $key => $id) {
                            $param = array(
                                'id' => $id,
                                'article_category_id' => $setCategoryId,
                            );
                            $articleModel->toSave($param);
                        }
                        $message = '操作成功';
                    }
                }
            }
            tc_admin_show_message(MODULE_URL.'article/index', $message);
            
        } else {
            echo 'No Arguments!';
        }
        
    }

}
