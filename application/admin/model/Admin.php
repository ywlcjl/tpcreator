<?php

/**
 * admin模块的admin模型
 */

namespace app\admin\model;

use app\common\model;

class Admin extends model\AdminBase
{   
    protected function initialize()
    {
        parent::initialize();
    }
    
    public function getAdmin($per=20, $suffix=array()) 
    {
        $sql = "SELECT * FROM admin WHERE id > 0";
        $result = $this->getPageSqlQuery($sql, $per, $suffix);
        return $result;
    }
    
}
