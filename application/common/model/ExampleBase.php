<?php

/**
 * example 公共模型
 */

namespace app\common\model;

use app\common\model;

class ExampleBase extends BaseModel
{
    protected $table = 'example';

    protected function initialize()
    {
        parent::initialize();
    }   
    public function getstatus($key='') 
    {
        $data = array(0 => '停用', 1 => '启用', );

        if ($key !== '') {
            return $data[$key];
        } else {
            return $data;
        }
    }
}
