<?php
/**
 * admin管理员表基础模型
 */

namespace app\common\model;

use app\common\model;

class ArticleBase extends BaseModel
{
    protected $table = 'article';
    
    protected function initialize()
    {
        parent::initialize();
    }
    
    public function getStatus($key='') {
        $data = array(
            0 => '待审核',
            1 => '已发布',
            2 => '已作废',
            3 => '预发布',
        );

        if ($key !== '') {
            return $data[$key];
        } else {
            return $data;
        }
    }
    
    public function getTop($key='') {
        $data = array(
            0 => '否',
            1 => '是',
        );

        if ($key !== '') {
            return $data[$key];
        } else {
            return $data;
        }
    }

    
}