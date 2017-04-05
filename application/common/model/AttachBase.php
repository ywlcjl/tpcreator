<?php
/**
 * admin管理员表基础模型
 */

namespace app\common\model;

use app\common\model;

class AttachBase extends BaseModel
{
    protected $table = 'attach';
    
    protected function initialize()
    {
        parent::initialize();
    }
    
}