<?php

/**
 * 代码生成
 */

namespace app\admin\controller;

use think\controller;

class Create extends controller {

    protected $_applicationDir = 'application';

    //代码生成特殊魔法字符
    protected $_magicStr = array('$id$', '$array$', '$max$');


    protected function _initialize()
     {
         parent::_initialize();
         
        //检查登陆情况
        tc_admin_login_jump();
        
        //权限检查
        tc_admin_permission_jump(1);
     }

    /**
     * 首页
     */
    public function index() {
        $data = array();
        
        $post = input('post.');
        
        if ($post) {
            $rule = array(
                'table|表格' => 'require',
            );
            //自定义错误提示信息
            $msg = array();

            //表单处理
            $param = array(
                'table' => input('post.table'),
            );
            
            $success = FALSE;
            $message = '';
            
            //创建自定义验证器
            $validate = tc_my_validate($rule, $msg);

            if (!$validate->check($param)) {
                //$message = $validate->getError();
                $message = '表单输入有误';
            } else {
                $tableName = $param['table'];
                
                //查询数据
                $result = $this->_getTableFields($tableName);

                if ($result) {
                    $message = '提交成功';
                    $success = true;
                    $data['result'] = $result;
                    $data['tableName'] = $tableName;
                } else {
                    $message = '表格和字段结果为空.';
                }
            }

            if ($success) {
                //需要生成对应的文件数组
                $files = $this->_getFiles($tableName);

                $data['files'] = $files;
                $data['tableName'] = $tableName;
                return $this->fetch('create/next', $data);
            } else {
                $data['message'] = $message;
                return $this->fetch('create/index', $data);
            }
        } else {
            //显示表单
            return $this->fetch('create/index');
        }
    }

    /**
     * 准备生成代码
     */
    public function next() {
        $data = array();
        
        $post = input('post.');
        
        if ($post) {
            //执行表单操作
            $rule = array(
                'table|表格' => 'require',
            );
            //自定义错误提示信息
            $msg = array();

            //表单处理
            $param = array(
                'table' => input('post.table'),
            );
            
            $success = FALSE;
            $message = '';
            
           //创建自定义验证器
            $validate = tc_my_validate($rule, $msg);
            
            if (!$validate->check($param)) {
                //检查表单是否有误
                $message = $validate->getError();
            } else {
                $tableName = $param['table'];
                
                $result = $this->_getTableFields($tableName);

                if ($result) {
                    $message = '提交成功';
                    $success = TRUE;
                    $data['result'] = $result;
                    $data['tableName'] = $tableName;
                } else {
                    $message = '表格和字段结果为空.';
                }
            }

            if ($success) {
                //需要生成对应的文件数组
                $files = $this->_getFiles($tableName);

                //生成写入commom/model
                $modelBaseStr = $this->_getModelBaseStr($tableName, $result);
                touch($files['model_base']);
                file_put_contents($files['model_base'], $modelBaseStr);
                
                //生成写入model
                $modelStr = $this->_getModelStr($tableName, $result);
                touch($files['model']);
                file_put_contents($files['model'], $modelStr);

                //生成controller
                $controllerStr = $this->_getControllerStr($tableName, $result);
                touch($files['controller']);
                file_put_contents($files['controller'], $controllerStr);

                //生成目录
                $viewIndexDir = ROOT_PATH . $this->_applicationDir .'/'. MODULE_NAME.'/view/'. strtolower($tableName);
                if (!is_dir($viewIndexDir)) {
                    mkdir($viewIndexDir, 0777);
                }
                
                //生成views/index
                $viewIndexStr = $this->_getViewIndexStr($tableName, $result);               
                touch($files['view_index']);
                file_put_contents($files['view_index'], $viewIndexStr);
                
                //生成views/save
                $viewSaveStr = $this->_getViewSaveStr($tableName, $result);
                touch($files['view_save']);
                file_put_contents($files['view_save'], $viewSaveStr);
                
                $data['tableName'] = $tableName;
                return $this->fetch('create/complete', $data);
            } else {
                $data['message'] = $message;
                return $this->fetch('create/next', $data);
            }
        }
    }

    private function _getFiles($tableName) {
        //驼峰命名
        $tableClassName = $this->_getTableClassName($tableName);
        $tableTuoName = $this->_getTuoName($tableName);
        
        //模块路径
        $applicationDir = $this->_applicationDir;
        $modulePath = ROOT_PATH .$applicationDir.'/'. MODULE_NAME;
        
        $files = array(
            'model_base' => ROOT_PATH . $applicationDir.'/common/model/' .$tableClassName . 'Base.php'  ,
            'model' => $modulePath . '/model/' . $tableClassName . '.php',
            'controller' => $modulePath . '/controller/' . $tableClassName . '.php',
            'view_index' => $modulePath . '/view/'  . strtolower($tableName) . '/' . 'index.php',
            'view_save' => $modulePath . '/view/' . strtolower($tableName) . '/' . 'save.php',
        );
        return $files;
    }

    protected function _getArrays($str) {
        $statuss = array();
        $start = stripos($str, '$array$');
        if ($start !== FALSE) {
            $commentStr = substr($str, $start+7);  //+7 $array$
            if ($commentStr) {
                $arrayT = explode('|', $commentStr);
                if ($arrayT) {
                    foreach ($arrayT as $value) {
                        $arrayV = explode(':', $value);
                        if ($arrayV) {
                            $statuss[$arrayV[0]] = $arrayV[1];
                        }
                    }
                }
            }
        }

        return $statuss;
    }
    
    protected function _getModelBaseStr($tableName, $columns) {
        $tableClassName = $this->_getTableClassName($tableName);
        $tableTuoName = $this->_getTuoName($tableName);
        
        $tTableName = ucfirst($tableName);
        $str = <<<model
<?php

/**
 * $tableName 公共模型
 */

namespace app\common\model;

use app\common\model;

class {$tableClassName}Base extends BaseModel
{
    protected \$table = '$tableName';

    protected function initialize()
    {
        parent::initialize();
    }
model;

        //是否存在定义的状态
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                if ($column['COLUMN_COMMENT']) {
                    //获取状态选项的的处理
                    $statuss = $this->_getArrays($column['COLUMN_COMMENT']);

                    $firstColumnName = $this->_getTableClassName($column['COLUMN_NAME']);

                    //生成代码
                    if ($statuss) {
                        $str .= <<<model
   
    public function get{$firstColumnName}(\$key='') 
    {
        \$data = array(
model;
                        foreach ($statuss as $sKey => $sValue) {
                            $str .= $sKey . " => '" . $sValue . "', ";
                        }

                        $str .= <<<model
);

        if (\$key !== '') {
            return \$data[\$key];
        } else {
            return \$data;
        }
    }
model;
                    }
                }
            }
        }


        //结尾
        $str .= <<<model

}

model;


        return $str;
    }

    protected function _getModelStr($tableName, $columns) {
        $tableClassName = $this->_getTableClassName($tableName);
        $tableTuoName = $this->_getTuoName($tableName);
        
        $tTableName = ucfirst($tableName);
        $str = <<<model
<?php

/**
 * $tableName 模型
 */

namespace app\admin\model;

use app\common\model;
                
class {$tableClassName} extends model\\{$tableClassName}Base
{

    protected function initialize()
    {
        parent::initialize();
    }

model;

        //结尾
        $str .= <<<model

}

model;


        return $str;
    }

    /**
     * 生成控制器代码
     * @param type $tableName
     * @param type $columns
     * @return type
     */
    protected function _getControllerStr($tableName, $columns) {
        $tableClassName = $this->_getTableClassName($tableName);
        $tableTuoName = $this->_getTuoName($tableName);
        
        //类名拼接
        $controllerClassName = ucfirst($tableName);

        $modelName = $tableTuoName . 'Model';

        $str = <<<sss
<?php

/**
 * $tableName 控制器
 */

namespace app\admin\controller;

use think\controller;

class $tableClassName extends controller 
{

    protected function _initialize()
    {
        parent::_initialize();

        //检查登陆情况
        tc_admin_login_jump();

        //检查权限
        //tc_admin_permission_jump(1);
    }

sss;

        $str .= <<<sss
                
    public function index() 
    {
        \$data = array();
        \$param = array();
        
        \${$tableTuoName}Model = model('{$tableTuoName}');

sss;
        
        //是否存在关联id代码
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                $start = stripos($column['COLUMN_COMMENT'], '$id$');
                if ($column['COLUMN_COMMENT'] && $start !== FALSE) {
                    //获取关联id选项的的处理
                    $idStr = substr($column['COLUMN_COMMENT'], $start+4); //加4为$id$
                    $idStrTuo = $this->_getTuoName($idStr);
                    
                    //生成代码
                    $str .= <<<sss
        \${$idStrTuo}Model = model('{$idStrTuo}');

sss;
                }
            }
        }

        $str .= <<<sss


sss;
        //是否存在选项代码
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                $start = stripos($column['COLUMN_COMMENT'], '$id$');
                if ($column['COLUMN_COMMENT'] && $start !== FALSE) {
                    //关联id处理
                    $idStr = substr($column['COLUMN_COMMENT'], $start+4); //+4 $id$
                    $idStrTuo = $this->_getTuoName($idStr);
                    
                    //关联id
                    $str .= <<<sss
        \$data['{$idStrTuo}s'] = \${$idStrTuo}Model->getResult(array(), '', '', 'id DESC');

sss;
                } elseif ($column['COLUMN_COMMENT']) {
                    //获取状态选项的的处理
                    $statuss = $this->_getArrays($column['COLUMN_COMMENT']);

                    $firstColumnName = $this->_getTuoName($column['COLUMN_NAME']);
                    $firstColumnNameClassName = $this->_getTableClassName($column['COLUMN_NAME']);
                    
                    //生成代码
                    if ($statuss) {
                        $str .= <<<sss
        \$data['{$firstColumnName}s'] = \${$modelName}->get{$firstColumnNameClassName}();

sss;
                    }
                }
            }
        }

        //处理搜索筛选
        $str .= <<<sss

        //搜索筛选
        \$data['filter'] = input('get.filter');
        if(\$data['filter']) {


sss;

        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                $tuoName = $this->_getTuoName($column['COLUMN_NAME']);
                
                if (in_array($column['DATA_TYPE'], array('char', 'varchar', 'text'))) {
                    //普通字符串
                    $str .= <<<sss
            \$data['{$column['COLUMN_NAME']}'] = input('get.{$column['COLUMN_NAME']}');
            if(\$data['{$column['COLUMN_NAME']}']) {
                \$param['{$column['COLUMN_NAME']}'] = array('like', "%{$column['COLUMN_NAME']}%");
            }


sss;
                } elseif (in_array($column['DATA_TYPE'], array('datetime', 'timestamp', 'date'))) {
                    //日期时间
                    $str .= <<<sss
            \$data['{$tuoName}Start'] = input('get.{$tuoName}Start');
            \$data['{$tuoName}End'] = input('get.{$tuoName}End');
            if (\$data['{$tuoName}Start'] && \$data['{$tuoName}End']) {
                \$param['{$column['COLUMN_NAME']}'] = array('between time', array(
                    date('Y-m-d', strtotime(\$data['{$tuoName}Start'])),
                    date('Y-m-d', strtotime(\$data['{$tuoName}End']))
                ));
            }


sss;
                } else if (stripos($column['COLUMN_COMMENT'], '$max$') !== false && in_array($column['DATA_TYPE'], array('int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'float', 'double', 'decimal'))) {
                    //大小于号生成
                    $str .= <<<sss
            \$data['{$tuoName}Min'] = input('get.{$tuoName}Min');
            if (\$data['{$tuoName}Min'] !== '') {
                \$param['{$column['COLUMN_NAME']}'] = array('>=', \$data['{$tuoName}Min']);
            }
            \$data['{$tuoName}Max'] = input('get.{$tuoName}Max');
            if (\$data['{$tuoName}Max'] !== '' && \$data['{$tuoName}Min'] !== '') {
                \$param['{$column['COLUMN_NAME']}'] = array(array('>=', \$data['{$tuoName}Min']), array('<',  \$data['{$tuoName}Max']));
            } else if (\$data['{$tuoName}Max'] !== '') {
                \$param['{$column['COLUMN_NAME']}'] = array('<',  \$data['{$tuoName}Max']);
            }


sss;
                } else {
                    //普通的数字
                    $str .= <<<sss
            \$data['{$column['COLUMN_NAME']}'] = input('get.{$column['COLUMN_NAME']}');
            if(\$data['{$column['COLUMN_NAME']}'] !== '') {
                \$param['{$column['COLUMN_NAME']}'] = \$data['{$column['COLUMN_NAME']}'];
            }


sss;
                }
            }
        }



        $str .= <<<sss
        }

        //获取所有筛选get参数
        \$gets = input('get.');
               
        //每页显示数量
        \$pagePer = 20;
        
        //获取分页结果
        \${$tuoName}s = \${$modelName}->getPage(\$param, \$pagePer, \$gets, 'id DESC');
        \${$tuoName}Array = \${$tuoName}s->toArray();
        
        //结果集
        \$data['result'] = \${$tuoName}Array['data'];
        //分页生成
        \$data['page'] = \${$tuoName}s->render();

        return \$this->fetch('{$tableName}/index', \$data);
    }


sss;

        //----------------------- save 方法 ---------------------------
        $str .= <<<sss
    public function save() {
        \$data = array();

        \${$tableTuoName}Model = model('{$tableTuoName}');

sss;
        
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                $start = stripos($column['COLUMN_COMMENT'], '$id$');
                if ($column['COLUMN_COMMENT'] && $start !== FALSE) {
                    //获取关联id选项的的处理
                    $idStr = substr($column['COLUMN_COMMENT'], $start+4); //加4为$id$
                    $idStrTuo = $this->_getTuoName($idStr);
                    
                    //生成代码
                    $str .= <<<sss
        \${$idStrTuo}Model = model('{$idStrTuo}');

sss;
                }
            }
        }

        $str .= <<<sss


sss;
        
        //处理status
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                $start = stripos($column['COLUMN_COMMENT'], '$id$');
                if ($column['COLUMN_COMMENT'] && $start !== FALSE) {
                    //关联id处理
                    $idStr = substr($column['COLUMN_COMMENT'], $start+4); //+4 $id$
                    $idStrTuo = $this->_getTuoName($idStr);
                    
                    //关联id
                    $str .= <<<sss
        \$data['{$idStrTuo}s'] = \${$idStrTuo}Model->getResult(array(), '', '', 'id DESC');

sss;
                } elseif ($column['COLUMN_COMMENT']) {
                    //获取状态选项的的处理
                    $statuss = $this->_getArrays($column['COLUMN_COMMENT']);
                    $firstColumnName = $this->_getTuoName($column['COLUMN_NAME']);
                    $firstColumnNameClass = $this->_getTableClassName($column['COLUMN_NAME']);
                    
                    //生成代码
                    if ($statuss) {
                        $str .= <<<sss
        \$data['{$firstColumnName}s'] = \${$modelName}->get{$firstColumnNameClass}();

sss;
                    }
                }
            }
        }

        $str .= <<<sss
 
        \$post = input('post.');
        
        if (\$post) {
            \$rule = array(

sss;

        //处理表单
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                if (!in_array($column['COLUMN_NAME'], array('id', 'update_time', 'create_time'))) {
   
                    //获取字段自定义名称
                    $commentChineseName = $this->_getCommentStr($column['COLUMN_COMMENT']);
                
                    //生成代码
                    if ($commentChineseName) {
                        $str .= <<<sss
                '{$column['COLUMN_NAME']}|{$commentChineseName}' => 'require',

sss;
                    } else {
                        $str .= <<<sss
                '{$column['COLUMN_NAME']}' => 'require',

sss;
                    }
                }
            }
        }

        $str .= <<<sss
            );
            
            //自定义错误提示信息
            \$msg = array();

            \$param = array(

sss;

        //处理输入过滤
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                //初始时间不用输入
                if ($column['COLUMN_NAME'] == 'update_time') {
                    //对updatetime的特殊处理
                    $str .= <<<sss
                'update_time' => date('Y-m-d H:i:s'),

sss;
                } elseif ($column['COLUMN_NAME'] == 'id') {
                    //生成代码
                    $str .= <<<sss
                'id' => intval(input('post.id')),

sss;
                } elseif (!in_array($column['DATA_TYPE'], array('timestamp'))) {
                    //生成代码
                    $str .= <<<sss
                '{$column['COLUMN_NAME']}' => input('post.{$column['COLUMN_NAME']}'),

sss;
                }
            }
        }

        //后半部分
        $str .= <<<sss

        );
            \$success = FALSE;
            \$message = '';

            //创建自定义验证器
            \$validate = tc_my_validate(\$rule, \$msg);

            if (!\$validate->check(\$param)) {
                //\$message = \$validate->getError();
                \$message = '表单填写有误';
                
            } else {
                //数据插入或更新
                \$save = \${$modelName}->toSave(\$param);

                if (\$save) {
                    \$success = TRUE;
                    \$message = '保存成功';
                } else {
                    \$message = '保存失败';
                }
            }

            if (\$success) {
                tc_to_link(MODULE_URL.'{$tableTuoName}/index/');
            } else {
                //输出表单页
                \$data['message'] = \$message;
                return \$this->fetch('{$tableName}/save', \$data);
            }
        } else {
            //编辑页
            \$id = intval(input('get.id'));
            
            if (\$id) {
                \$data['row'] = \${$modelName}->getRow(array('id' => \$id));
            }
            return \$this->fetch('{$tableName}/save', \$data);
        }
    }


sss;

        //----------------------- manage ------------------------
        $str .= <<<sss
    public function manage() 
    {
        //批量处理
        \$data = array();
        \${$modelName} = model('{$tableTuoName}');
        
        \$post = input('post.');
        
        if (\$post) {
            \$rule = array(
                'ids|IDs' => 'require',
                'manageName|操作选项' => 'require'
            );
            //自定义错误提示信息
            \$msg = array();
            
            \$manageName = input('post.manageName');
            \$ids = tc_clean_ids(\$_POST['ids']);
            
            \$param = array(
                'ids' => \$ids,
                'manageName' => \$manageName,
            );
            
            \$success = false;
            \$message = '';
            
            //创建自定义验证器
            \$validate = tc_my_validate(\$rule, \$msg);
            
            if (!\$validate->check(\$param)) {
                \$message = \$validate->getError();
            } else {
                if (\$ids != null) {
                    if (\$manageName == 'delete') {
                        //删除记录
                        foreach (\$ids as \$key => \$id) {
                            if (\$id) {
                                \$param = array(
                                    'id' => \$id,
                                );
                                //删除记录
                                \${$modelName}->destroy(\$param);
                            }
                        }
                        \$message = '删除成功';

sss;

        //处理状态
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                $start = stripos($column['COLUMN_COMMENT'], '$id$');
                
                if ($column['COLUMN_COMMENT'] && $start !== FALSE) {
//                    $idStr = substr($column['COLUMN_COMMENT'], $start+4); //+4 $id$
//                    $idStrTuo = $this->_getTuoName($idStr);
//                    $idStrModel = $idStrTuo.'Model';
                    
                    
                    //关联id
                    $str .= <<<sss
                    } elseif (\$manageName == 'set_{$column['COLUMN_NAME']}') {
                    
                        \$setValue = input('post.set_{$column['COLUMN_NAME']}');
                        if (\$setValue !== '') {
                            foreach (\$ids as \$key => \$id) {
                                \$param = array(
                                    'id' => \$id,
                                    '{$column['COLUMN_NAME']}' => \$setValue,
                                );
                                \${$modelName}->toSave(\$param);
                            }
                            \$message = '操作成功';
                        } else {
                            \$message = '设置值不能为空.';
                        }

sss;
                } elseif ($column['COLUMN_COMMENT']) {
                    //获取状态选项的的处理
                    $statuss = $this->_getArrays($column['COLUMN_COMMENT']);

                    //生成代码
                    if ($statuss) {
                        $str .= <<<sss
                    } elseif (\$manageName == 'set_{$column['COLUMN_NAME']}') {
                        \$setValue = input('post.set_{$column['COLUMN_NAME']}');
                        if (\$setValue !== '') {
                            foreach (\$ids as \$key => \$id) {
                                \$param = array(
                                    'id' => \$id,
                                    '{$column['COLUMN_NAME']}' => \$setValue,
                                );
                                \${$modelName}->toSave(\$param);
                            }
                            \$message = '操作成功';
                        } else {
                            \$message = '设置值不能为空.';
                        }

sss;
                    }
                }
                
            }
        }

        $str .= <<<sss
                    }
                }
            }
            tc_admin_show_message(MODULE_URL.'{$tableTuoName}/index', \$message);
        } else {
            echo 'No Arguments!';
        }
    }
}
sss;


        return $str;
    }
    
    protected function _getViewIndexStr($tableName, $columns) {
        //驼峰命名
        $tTableName = $this->_getTuoName($tableName);
                
        $str = <<<sss
<?php tc_view(MODULE_VIEW.'public/header', array(
    'title' => '{$tTableName}列表',
    'onView' => '{$tTableName}',
)); ?>

<script type="text/javascript">
    $(document).ready(function () {
       //筛选
        filterForm();
        
        //全选
        checkAll();

        //日期控件
        bootstrapDate();
        
        //搜索框隐藏bug修正, 必须放在日期插件加载后隐藏
        <?php if (!\$filter) : ?>
            $('#filterForm').css('display', 'none');
            $('#filterFormTitle').attr('class', 'btn btn-default');
        <?php endif; ?>

        //管理操作
        $('#manageName').change(function() {
            var manageName = $(this).val();

sss;
        
        //处理状态
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                $start = stripos($column['COLUMN_COMMENT'], '$id$');
                if ($column['COLUMN_COMMENT'] && $start !== FALSE) {

                    //关联id
                    $str .= <<<sss
            if(manageName == 'set_{$column['COLUMN_NAME']}') {
                $('#set_{$column['COLUMN_NAME']}').css('display', '');
            } else {
                $('#set_{$column['COLUMN_NAME']}').css('display', 'none');
            }


sss;
                } elseif ($column['COLUMN_COMMENT']) {
                    //获取状态选项的的处理
                    $statuss = $this->_getArrays($column['COLUMN_COMMENT']);

                    //生成代码
                    if ($statuss) {
                        $str .= <<<sss
            if(manageName == 'set_{$column['COLUMN_NAME']}') {
                $('#set_{$column['COLUMN_NAME']}').css('display', '');
            } else {
                $('#set_{$column['COLUMN_NAME']}').css('display', 'none');
            }

sss;
                    }
                }
                
            }
        }
        
        
        $str .= <<<sss
        });
    });
</script>

<div class="row">
    <div class="col-md-6">
        <p class="bd_title">{$tTableName}列表</p>
    </div>
    <div class="col-md-6">
        <p class="text-right"><a href="<?php echo MODULE_URL; ?>{$tTableName}/save">添加{$tTableName}</a></p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <form name="form1" id="form1" method="post" action="<?php echo MODULE_URL; ?>{$tTableName}/manage">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>

sss;
        

        //表格头部
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                //获取中文名称
                $columnName = $this->_getCommentStr($column['COLUMN_COMMENT']);
                if (!$columnName) {
                    $columnName = $column['COLUMN_NAME'];
                }
                
                $str .= <<<sss
                            <th>{$columnName}</th>

sss;
            }
        }
        
        $str .= <<<sss
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (\$result != null) : ?>
                            <?php foreach (\$result as \$value) : ?>
                                <tr class="bd_table_tr">

sss;
        
        //表格中部
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                if ($column['COLUMN_NAME'] == 'id') {
                    $str .= <<<sss
                                    <th scope="row">
                                        <label>
                                            <input type="checkbox" name="ids[]" value="<?php echo \$value['id']; ?>" /> <?php echo \$value['id']; ?>
                                        </label>
                                    </th>

sss;
                } elseif($column['COLUMN_COMMENT'] &&  stripos($column['COLUMN_COMMENT'], '$array$') !== FALSE) {
                    $columnNameTuo = $this->_getTuoName($column['COLUMN_NAME']);
                    //直接代入数组
                    $str .= <<<sss
                                    <td><?php echo \${$columnNameTuo}s[\$value['{$column['COLUMN_NAME']}']]; ?></td>

sss;
                } else {
                    $str .= <<<sss
                                    <td><?php echo \$value['{$column['COLUMN_NAME']}']; ?></td>

sss;
                }
            }
        }
        
        $str .= <<<sss
                                    <td><a href="<?php echo MODULE_URL; ?>$tTableName/save/?id=<?php echo \$value['id']; ?>">编辑</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="checkAll"> 全选
                </label>

                <select name="manageName" id="manageName">
                    <option value="">请选择</option>
                    <option value="delete">删除</option>

sss;
        
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                //中文名称
                $columnCnName = $this->_getCommentStr($column['COLUMN_COMMENT']);
                if (!$columnCnName) {
                    $columnCnName = $column['COLUMN_NAME'];
                }
                
                if ($column['COLUMN_COMMENT'] && stripos($column['COLUMN_COMMENT'], '$id$') !== FALSE) {
                    //关联id
                    $str .= <<<sss
                            <option value="set_{$column['COLUMN_NAME']}">设置{$columnCnName}</option>

sss;
                } elseif ($column['COLUMN_COMMENT']) {
                    //获取状态选项的的处理
                    $statuss = $this->_getArrays($column['COLUMN_COMMENT']);

                    //生成代码
                    if ($statuss) {
                        $str .= <<<sss
                            <option value="set_{$column['COLUMN_NAME']}">设置{$columnCnName}</option>

sss;
                    }
                }
                
            }
        }
        
        $str .= <<<sss
                </select>

sss;
        
        //隐藏筛选框
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                $start = stripos($column['COLUMN_COMMENT'], '$id$');
                if ($column['COLUMN_COMMENT'] && $start !== FALSE) {
                    $idStr = substr($column['COLUMN_COMMENT'], $start+4); //+4 $id$
                    
                    //关联id
                    $str .= <<<sss
                    <select name="set_{$column['COLUMN_NAME']}" id="set_{$column['COLUMN_NAME']}" style="display: none;">
                        <option value="">请选择</option>
                        <?php if (\${$idStr}s != null) : ?>
                            <?php foreach (\${$idStr}s as \$key=>\$value) : ?>
                                <option value="<?php echo \$value['id']; ?>"><?php echo \$value['id']; ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

sss;
                } elseif ($column['COLUMN_COMMENT']) {
                    //获取状态选项的的处理
                    $statuss = $this->_getArrays($column['COLUMN_COMMENT']);

                    //生成代码
                    if ($statuss) {
                        $str .= <<<sss
                    <select name="set_{$column['COLUMN_NAME']}" id="set_{$column['COLUMN_NAME']}" style="display: none;">
                        <option value="">请选择</option>
                        <?php if (\${$column['COLUMN_NAME']}s != null) : ?>
                            <?php foreach (\${$column['COLUMN_NAME']}s as \$key=>\$value) : ?>
                                <option value="<?php echo \$key; ?>"><?php echo \$value; ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

sss;
                    }
                }
                
            }
        }
        
        $str .= <<<sss
                <input type="submit" id="manageButton"  value="提交" onclick="return confirmAction();" />
            </div>
        </form>

        <div class="bd_page">
            <?php echo \$page; ?>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><button class="btn btn-default active" type="submit" id="filterFormTitle">条件筛选+</button></div>
            <div class="panel-body" id="filterForm">
                <div class="col-md-8">
                <form action="<?php echo MODULE_URL; ?>{$tTableName}/index/" method="get">

sss;
                
        //处理筛选条件
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                
                //中文名称
                $columnCnName = $this->_getCommentStr($column['COLUMN_COMMENT']);
                if (!$columnCnName) {
                    $columnCnName = $column['COLUMN_NAME'];
                }
                
                if($column['COLUMN_COMMENT'] && stripos($column['COLUMN_COMMENT'], '$id$') !== FALSE){
                    $start = stripos($column['COLUMN_COMMENT'], '$id$');
                    //关联id
                    $idStr = substr($column['COLUMN_COMMENT'], $start+4); //+4 $id$

                    $str .= <<<sss
                    <div class="form-group">
                        <label for="filter_{$column['COLUMN_NAME']}">{$columnCnName}</label>
                        <select name="{$column['COLUMN_NAME']}" class="form-control" id="filter_{$column['COLUMN_NAME']}">
                            <option value="">请选择</option>
                            <?php if (\${$idStr}s != null) : ?>
                                <?php foreach (\${$idStr}s as \$value) : ?>
                                    <option value="<?php echo \$value['id']; ?>" <?php if (\${$column['COLUMN_NAME']} === \$value['id']) : ?>selected="selected"<?php endif; ?>><?php echo \$value['id']; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>


sss;
                    
                } elseif ($column['COLUMN_COMMENT'] && stripos($column['COLUMN_COMMENT'], '$array$') !== FALSE) {
                    //获取状态选项的的处理
                    $statuss = $this->_getArrays($column['COLUMN_COMMENT']);
                    
                    if ($statuss) {
                        $str .= <<<sss
                    <div class="form-group">
                        <label for="filter_{$column['COLUMN_NAME']}">{$columnCnName}</label>
                        <select name="{$column['COLUMN_NAME']}" class="form-control" id="filter_{$column['COLUMN_NAME']}">
                            <option value="">请选择</option>
                            <?php if (\${$column['COLUMN_NAME']}s != null) : ?>
                                <?php foreach (\${$column['COLUMN_NAME']}s as \$key => \$value) : ?>
                                    <option value="<?php echo \$key; ?>" <?php if (\${$column['COLUMN_NAME']} === (string) \$key) : ?>selected="selected"<?php endif; ?>><?php echo \$value; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>


sss;
                    }
                
                } elseif (in_array($column['DATA_TYPE'], array('datetime', 'timestamp', 'date'))) {
                    $columnNameTuo = $this->_getTuoName($column['COLUMN_NAME']);
                    
                    //日期时间
                    $str .= <<<sss
                    <div class="form-group">
                        <label>{$columnCnName}</label>
                        <input name="{$columnNameTuo}Start" data-provide="datepicker" class="form-control" type="text" value="<?php echo \${$columnNameTuo}Start; ?>" placeholder=">= 起始日期"> - 
                        <input name="{$columnNameTuo}End" data-provide="datepicker" class="form-control" type="text" value="<?php echo \${$columnNameTuo}End; ?>" placeholder="<= 结束日期">
                    </div>


sss;
                } else if ($column['COLUMN_COMMENT'] == '$max$' && in_array($column['DATA_TYPE'], array('int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'float', 'double', 'decimal'))) {
                    //大小于号生成
                    $columnNameTuo = $this->_getTuoName($column['COLUMN_NAME']);
                    
                    $str .= <<<sss
                    <div class="form-group">
                        <label>{$columnCnName}范围</label>
                        <input name="{$columnNameTuo}Min" class="form-control" type="text" value="<?php echo \${$columnNameTuo}Min; ?>" placeholder=">= input"> - 
                        <input name="{$columnNameTuo}Max" class="form-control" type="text" value="<?php echo \${$columnNameTuo}Max; ?>" placeholder="< input">
                    </div>


sss;
                } else {
                    //普通的数字
                    
                    $str .= <<<sss
                    <div class="form-group">
                        <label for="filter_{$column['COLUMN_NAME']}">{$columnCnName}</label>
                        <input type="text" name="{$column['COLUMN_NAME']}" class="form-control" id="filter_{$column['COLUMN_NAME']}" value="<?php echo \${$column['COLUMN_NAME']}; ?>">
                    </div>


sss;
                }
            }
        }
                

        //结尾
        $str .= <<<model
                    <input name="filter" type="hidden" value="1" />
                    <input class="btn btn-primary" type="submit" value="筛选"> 
                    <a class="btn btn-default" href="<?php echo MODULE_URL; ?>{$tableName}/index" role="button">清空条件</a>
                </form>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php tc_view(MODULE_VIEW.'public/footer'); ?>

model;


        return $str;
    
    }
    
    protected function _getViewSaveStr($tableName, $columns) {
        //驼峰命名
        $tTableName = $this->_getTuoName($tableName);
        
        $str = <<<model
<?php tc_view(MODULE_VIEW.'public/header', array(
    'title' => '编辑{$tTableName}',
    'onView' => '{$tTableName}',
)); ?>

<script type="text/javascript">
    $(document).ready(function () {

    });
</script>

<div class="row">
    <div class="col-md-6">
        <p class="bd_title">编辑$tTableName</p>
    </div>
    <div class="col-md-6">
        <p class="text-right"><a href="<?php echo MODULE_URL; ?>$tTableName/index">返回列表</a></p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <?php if (isset(\$message) && \$message) : ?>
            <div class="bd_warning_bg">
                <p class="bg-warning"><?php echo \$message; ?></p>
            </div>
        <?php endif; ?>

        <form action="<?php echo MODULE_URL; ?>$tTableName/save" method="post">

model;

        //save条件
        if ($columns && is_array($columns)) {
            foreach ($columns as $column) {
                //中文名称
                $columnCnName = $this->_getCommentStr($column['COLUMN_COMMENT']);
                if (!$columnCnName) {
                    $columnCnName = $column['COLUMN_NAME'];
                }
                
                $start = stripos($column['COLUMN_COMMENT'], '$id$');
                if($column['COLUMN_COMMENT'] && $start !== FALSE){
                    //关联id
                    $idStr = substr($column['COLUMN_COMMENT'], $start+4); //+4 $id$
                
                    $str .= <<<sss
           <div class="form-group<?php if (tc_form_error('{$column['COLUMN_NAME']}')) : ?> has-error<?php endif; ?>">
                <label for="input_{$column['COLUMN_NAME']}" class="control-label">{$columnCnName}</label>
                <select name="{$column['COLUMN_NAME']}" class="form-control">
                    <option value="">请选择</option>
                    <?php if (\${$idStr}s != null) : ?>
                        <?php foreach (\${$idStr}s as \$value) : ?>
                            <option value="<?php echo \$value['id']; ?>" <?php if (\$row['{$column['COLUMN_NAME']}'] === (string)\$value['id'] || tc_set_value('{$column['COLUMN_NAME']}') === (string)\$value['id']) : ?>selected="selected"<?php endif; ?>><?php echo \$value['id']; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('{$column['COLUMN_NAME']}'); ?></span>
            </div>


sss;
                    
                } elseif ($column['COLUMN_COMMENT'] && stripos($column['COLUMN_COMMENT'], '$array$') !== FALSE) {
                    //获取状态选项的的处理
                    $statuss = $this->_getArrays($column['COLUMN_COMMENT']);

                    if ($statuss) {
                        $str .= <<<sss
            <div class="form-group<?php if (tc_form_error('{$column['COLUMN_NAME']}')) : ?> has-error<?php endif; ?>">
                <label for="input_{$column['COLUMN_NAME']}" class="control-label">{$columnCnName}</label>
                <select name="{$column['COLUMN_NAME']}" class="form-control" id="input_{$column['COLUMN_NAME']}">
                    <?php if (\${$column['COLUMN_NAME']}s) : ?>
                        <?php foreach (\${$column['COLUMN_NAME']}s as \$key => \$value) : ?>
                            <option value="<?php echo \$key; ?>" <?php if (\$row['{$column['COLUMN_NAME']}'] === (string) \$key || tc_set_value('{$column['COLUMN_NAME']}') === (string) \$key) : ?>selected="selected"<?php endif; ?>><?php echo \$value; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('{$column['COLUMN_NAME']}'); ?></span>
            </div>


sss;
                    }
                
                } else if (in_array($column['DATA_TYPE'], array('text'))) {
                    //文本框
                    $str .= <<<sss
            <div class="form-group<?php if (tc_form_error('{$column['COLUMN_NAME']}')) : ?> has-error<?php endif; ?>">
                <label for="input_{$column['COLUMN_NAME']}" class="control-label">{$columnCnName}</label>
                <textarea name="{$column['COLUMN_NAME']}" id="input_{$column['COLUMN_NAME']}" aria-describedby="helpBlock" class="form-control" rows="3"><?php echo tc_get_value(tc_set_value('{$column['COLUMN_NAME']}'), \$row['{$column['COLUMN_NAME']}']); ?></textarea>
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('{$column['COLUMN_NAME']}'); ?></span>
            </div>


sss;
                } else if(!in_array($column['COLUMN_NAME'], array('id', 'create_time', 'update_time'))) {
                    //普通
                    $str .= <<<sss
            <div class="form-group<?php if (tc_form_error('{$column['COLUMN_NAME']}')) : ?> has-error<?php endif; ?>">
                <label for="input_{$column['COLUMN_NAME']}" class="control-label">{$columnCnName}</label>
                <input type="text" name="{$column['COLUMN_NAME']}" id="input_{$column['COLUMN_NAME']}" class="form-control" aria-describedby="helpBlock" value="<?php echo tc_get_value(tc_set_value('{$column['COLUMN_NAME']}'), \$row['{$column['COLUMN_NAME']}']); ?>">
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('{$column['COLUMN_NAME']}'); ?></span>
            </div>


sss;
                }
            }
        }
        

        //结尾
        $str .= <<<model
            <input name="id" type="hidden" value="<?php echo tc_get_value(tc_set_value('id'), \$row['id']); ?>" />
            <input class="btn btn-primary" type="submit" value="保存" />
        </form>
        <p>&nbsp;</p>
    </div>
</div>

<?php tc_view(MODULE_VIEW.'public/footer'); ?>


model;


        return $str;
    }
    
    protected function _getTableFields($tableName)
    {
        //查询数据
        $dbInformation = 'information_schema';
        $infTable = 'COLUMNS';
        $dbName = config('database.database');

        //连接数据库
        $dbh = new \PDO(config('database.type') . ':host=' . config('database.hostname') . ';dbname=' . $dbInformation, config('database.username'), config('database.password'));
        $dbh->query("SET NAMES 'UTF8'");

        $sql = "SELECT `COLUMN_NAME`, `DATA_TYPE`,`CHARACTER_MAXIMUM_LENGTH`, `COLUMN_TYPE`, `COLUMN_COMMENT` FROM $infTable WHERE `TABLE_SCHEMA`='$dbName' AND `TABLE_NAME`='$tableName'";

        $sth = $dbh->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        
        return $result;
    }
    
    protected function _getTableClassName($tableName) 
    {
        $tableNames = explode('_', $tableName);
        $tableClassName = '';

        if ($tableNames) {
            foreach($tableNames as $key=>$value) {
                $tableClassName .= ucfirst(strtolower($value));
            }
        }
        
        return $tableClassName;
    }
    
    protected function _getTuoName($field)
    {
        $tuoName = '';
        $fields = explode('_', $field);
        if ($fields) {
            foreach($fields as $key=>$value) {
                if ($key > 0) {
                    $tuoName .= ucfirst(strtolower($value));
                } else {
                    $tuoName .= strtolower($value);
                }
            }
        }
        return $tuoName;
    }

    protected function _getCommentStr($comment)
    {
        //获取数据库字段中文自定义名称
        $magicStrs = $this->_magicStr;
        if ($magicStrs) {
            if ($comment) {
                //魔法符号循环查找
                foreach ($magicStrs as $key => $value) {

                    $start = stripos($comment, $value);
                    if ($start !== false) {
                        $commentArray = explode($value, $comment);

                        //返回第一个value  文章id$id$article 返回文章
                        return $commentArray[0];
                    }
                }
                return $comment;
            }
        }
        return '';
    }
    
}