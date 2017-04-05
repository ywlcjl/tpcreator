<?php

/**
 * 模型基类
 */

namespace app\common\model;

use think\Model;

class BaseModel extends Model
{

    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 模型基础单表查询类
     * @param type $param 使用tp5默认条数数组map表达式
     * @param type $limit
     * @param type $start
     * @param type $orderBy
     * @param type $paramOr 
     * @return type
     */
    public function getResult($param = array(), $limit = 0, $start = 0, $orderBy = 'id DESC', $paramOr = array())
    {
        $dbTable = db()->table($this->table);
        
        if ($param) {
            $dbTable->where($param);
        }
        
        if ($limit && $start) {
            $dbTable->limit($start, $limit);
        } else if($limit) {
            $dbTable->limit($limit);
        }
        
        if ($orderBy) {
            $dbTable->order($orderBy);
        }
        
        //where or条件查询
        if ($paramOr) {
            $dbTable->whereOr($paramOr);
        }
        
        $result = $dbTable->select();
        
        return $result;
    }
    
    /**
     * 获取一条数据
     * @param type $param
     * @return type
     */
    public function getRow($param = array()) 
    {
        $dbTable = db()->table($this->table);
        
        if ($param) {
            $dbTable->where($param);
        }
        
        $row = $dbTable->find();
        
        return $row;
    }

    /**
     * 获取分页查询结果集
     * @param type $param
     * @param type $per
     * @param type $suffix
     * @param type $orderBy
     * @param type $paramOr
     * @return type 返回分页结果集对象
     */
    public function getPage($param = array(), $per=20, $suffix=array(), $orderBy='id DESC', $paramOr = array())
    {   
        $dbTable = db()->table($this->table);
        
        if ($param) {
            $dbTable->where($param);
        }
        
        if ($orderBy) {
            $dbTable->order($orderBy);
        }
        
        //where or条件查询
        if ($paramOr) {
            $dbTable->whereOr($paramOr);
        }
        
        //分页生成 query是一个数组
        $result = $dbTable->paginate($per, false, array(
            'query' => $suffix,
        ));

        return $result;
    }
    

    /**
     * sql查询分页
     * @param type $sql
     * @param type $per
     * @param type $suffix
     * @return type
     */
    public function getPageSqlQuery($sql, $per=20, $suffix=array())
    {
        $db = db();
        
        $result = NULL;
        
        $countSql = $sql;
        
        //使用正则替换SQL, 生成count(*)统计查询分页总数
        $pattern = '/^SELECT.*FROM/';
        $queryCount = preg_replace($pattern,'SELECT COUNT(*) AS total FROM', $countSql); 

        //返回数组
        $rows = $db->query($queryCount);
        $totalCount = $rows[0]['total'];
        
        //当前分页页码
        $pageInput = intval(input('get.page'));
        $nowPage = $pageInput > 0 ? $pageInput : 1;
        
        //默认第一页
        $start = ($nowPage-1)*$per;
        
        if ($totalCount > 0) {            
            $sql .= " LIMIT $start, $per";
            $rows = $db->query($sql);
            
            //使用thinkphp的分页类
            $result = \think\paginator\driver\Bootstrap::make($rows, $per, $nowPage, $totalCount, false, array(
                'path' => '',
                'query' => $suffix,
            ));
        }
        
        return $result;
    }

    /**
     * 自定义模型新增或保存方法, 以id作为识别
     * @param type $data
     * @param type $returnInsertId 返回插入的id
     * @return boolean
     */
    public function toSave($data, $returnInsertId = false) {
        $dbTable = db()->table($this->table);
        
        $id = $data['id'];

        //删除id键值
        if($id) {
            unset($data['id']);
        }
        
        $success = true;
        $insertId = '';
        if ($id > 0) {
            //更新记录
            $success = $dbTable->where('id', $id)->update($data);
        } else {
            //新增记录
            $success = $dbTable->insert($data);
            
            //insertid
            if ($success && $returnInsertId) {
                $insertId = $dbTable->getLastInsID();
            }
        }

        if ($success !== false) {
            //非更新操作和需要返回插入id
            if (!$id && $returnInsertId) {
                return $insertId;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function getCount($param = array()) 
    {
        $dbTable = db()->table($this->table);
        
        if ($param) {
            $dbTable->where($param);
        }
        
        $count = $dbTable->count();
        
        return $count;
    }
    
    public function getMax($field, $param = array()) 
    {
        $dbTable = db()->table($this->table);
        
        if ($param) {
            $dbTable->where($param);
        }
        
        $max = $dbTable->max($field);
        
        return $max;
    }
    
    public function getMin($field, $param = array()) 
    {
        $dbTable = db()->table($this->table);
        
        if ($param) {
            $dbTable->where($param);
        }
        
        $min = $dbTable->min($field);
        
        return $min;
    }
    
    public function getAvg($field, $param = array()) 
    {
        $dbTable = db()->table($this->table);
        
        if ($param) {
            $dbTable->where($param);
        }
        
        $avg = $dbTable->avg($field);
        
        return $avg;
    }
    
    public function getSum($field, $param = array()) 
    {
        $dbTable = db()->table($this->table);
        
        if ($param) {
            $dbTable->where($param);
        }
        
        $sum = $dbTable->Sum($field);
        
        return $sum;
    }
    
}
