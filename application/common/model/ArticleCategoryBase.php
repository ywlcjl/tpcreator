<?php
/**
 * 文章分类基础模型
 */

namespace app\common\model;

use app\common\model;

class ArticleCategoryBase extends BaseModel
{
    protected $table = 'article_category';
    
    //无限分类队列
    protected $_queueCategory = array();

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
    
    public function setQueueCategory($parentId=0, $symbol='', $mark='--') {
        $categorys = $this->getResult(array('parent_id'=>$parentId), '', '', 'sort ASC');
        
        if($parentId) {
            $symbol .= $mark;
        }
        
        if($categorys != null) {
            foreach($categorys as $key=>$category) {
                $category['name'] = $symbol.$category['name'];
                $this->_queueCategory[] = $category;
                
                $this->setQueueCategory($category['id'], $symbol);
            }
        }
    }
    
    public function getQueueCategory($parentId=0, $symbol='', $mark='--') {
        if($this->_queueCategory == null) {
            $this->setQueueCategory($parentId, $symbol, $mark);
        }
        
        return $this->_queueCategory;
    }
    
}