<?php
/**
 * admin管理员表基础模型
 */

namespace app\common\model;

use app\common\model\BaseModel;

class AdminPermissionBase extends BaseModel
{
    protected $table = 'admin_permission';
    
    protected function initialize()
    {
        parent::initialize();
    }
    
    public function getStatus($key='') {
        $data = array(
            0 => '待审核',
            1 => '已启用',
            2 => '已作废',
        );

        if ($key !== '') {
            return $data[$key];
        } else {
            return $data;
        }
    }
    
}