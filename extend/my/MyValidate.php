<?php

/**
 * 验证器扩展类
 */

namespace my;

use think\Validate;

class MyValidate extends Validate
{

    //单例静态成员变量
    private static $_instance;
    protected $_errorFields = array();

    public function __construct($rules = array(), $message = array())
    {
        parent::__construct($rules, $message);
    }

    //单例函数
    public static function getInstance($rules = array(), $message = array())
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($rules, $message);
        }
        return self::$_instance;
    }

    //重写验证方法
    protected function checkItem($field, $value, $rules, $data, $title = '', $msg = [])
    {
        // 支持多规则验证 require|in:a,b,c|... 或者 ['require','in'=>'a,b,c',...]
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }
        $i = 0;
        foreach ($rules as $key => $rule) {
            if ($rule instanceof \Closure) {
                $result = call_user_func_array($rule, [$value, $data]);
                $info = is_numeric($key) ? '' : $key;
            } else {
                // 判断验证类型
                if (is_numeric($key)) {
                    if (strpos($rule, ':')) {
                        list($type, $rule) = explode(':', $rule, 2);
                        if (isset($this->alias[$type])) {
                            // 判断别名
                            $type = $this->alias[$type];
                        }
                        $info = $type;
                    } elseif (method_exists($this, $rule)) {
                        $type = $rule;
                        $info = $rule;
                        $rule = '';
                    } else {
                        $type = 'is';
                        $info = $rule;
                    }
                } else {
                    $info = $type = $key;
                }

                // 如果不是require 有数据才会行验证
                if (0 === strpos($info, 'require') || (!is_null($value) && '' !== $value)) {
                    // 验证类型
                    $callback = isset(self::$type[$type]) ? self::$type[$type] : [$this, $type];
                    // 验证数据
                    $result = call_user_func_array($callback, [$value, $rule, $data, $field, $title]);
                } else {
                    $result = true;
                }
            }

            if (false === $result) {
                // 验证失败 返回错误信息
                if (isset($msg[$i])) {
                    $message = $msg[$i];
                    if (is_string($message) && strpos($message, '{%') === 0) {
                        $message = Lang::get(substr($message, 2, -1));
                    }
                } else {
                    $message = $this->getRuleMsg($field, $title, $info, $rule);
                }

                //验证返回具体错误的字段 ~tc
                $this->_errorFields[$field] = $message;
                return $message;
            } elseif (true !== $result) {
                // 返回自定义错误信息
                if (is_string($result) && false !== strpos($result, ':')) {
                    $result = str_replace([':attribute', ':rule'], [$title, (string) $rule], $result);
                }

                //验证返回具体错误的字段 ~tc
                $this->_errorFields[$field] = $result;
                return $result;
            }
            $i++;
        }
        return $result;
    }

    //获取产生错误的字段名称
    public function getErrorFields()
    {
        return $this->_errorFields;
    }

    //检查具体表单字段是否有误
    public function formError($key)
    {
        $fields = $this->getErrorFields();

        return $fields[$key];
    }
    

}
