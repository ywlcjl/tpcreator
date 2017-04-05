<?php

/**
 * cron_log 公共模型
 */

namespace app\common\model;

use app\common\model;

class CronLogBase extends BaseModel
{
    protected $table = 'cron_log';

    protected function initialize()
    {
        parent::initialize();
    }   
    public function getType($key='') 
    {
        $data = array(0 => '未分类', 1 => '默认', );

        if ($key !== '') {
            return $data[$key];
        } else {
            return $data;
        }
    }   
    public function getStatus($key='') 
    {
        $data = array(0 => '待审核', 1 => '已启用', 2 => '已作废', );

        if ($key !== '') {
            return $data[$key];
        } else {
            return $data;
        }
    }
}
