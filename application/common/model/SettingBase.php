<?php
/**
 * admin管理员表基础模型
 */

namespace app\common\model;

use app\common\model\BaseModel;

class SettingBase extends BaseModel
{
    protected $table = 'setting';
    
    //全局配置数组
    public $_setting = array();
    
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
    
    public function getSetting() {
        //处理全局配置数组
        if ($this->_setting == NULL) {
            $settings = $this->getResult(array('status' => 1), '', 0, 'id ASC');
            if ($settings) {
                $result = array();
                foreach ($settings as $setting) {
                    $result[$setting['key']] = $setting['value'];
                }

                if ($result) {
                    $this->_setting = $result;
                }
            }
        }

        return $this->_setting;
    }

    public function setSetting($key, $value) {
        //设置setting
        $setting = $this->getRow(array('key' => $key));
        if ($setting) {
            $param = array(
                'id' => $setting['id'],
                'value' => $value,
            );

            $this->save($param);
        }
    }
    
}