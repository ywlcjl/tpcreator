<?php tc_view(MODULE_VIEW.'public/header', array(
    'title' => '管理员列表',
    'onView' => 'admin',
)); ?>

<script type="text/javascript">
    $(document).ready(function () {
        //筛选
        filterForm();
        
        //全选
        checkAll();

        //搜索框隐藏bug修正, 必须放在日期插件加载后隐藏
        <?php if (!$filter) : ?>
            $('#filterForm').css('display', 'none');
            $('#filterFormTitle').attr('class', 'btn btn-default');
        <?php endif; ?>

        //管理操作
        $('#manageName').change(function () {
            var manageName = $(this).val();
            if (manageName == 'setStatus') {
                $('#setStatus').css('display', '');
            } else {
                $('#setStatus').css('display', 'none');
            }
        });
    });
</script>

<div class="row">
    <div class="col-md-6">
        <p class="bd_title">管理员列表</p>
    </div>
    <div class="col-md-6">
        <p class="text-right"><a href="<?php echo MODULE_URL ?>admin/save">添加管理员</a></p>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <form name="form1" id="form1" method="post" action="<?php echo MODULE_URL ?>admin/manage">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>用户名</th>
                            <th>状态</th>
                            <th>最后登录</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result != null) : ?>
                            <?php foreach ($result as $value) : ?>
                                <tr class="bd_table_tr">
                                    <th scope="row">
                                        <label>
                                            <input type="checkbox" name="ids[]" value="<?php echo $value['id']; ?>" /> <?php echo $value['id']; ?>
                                        </label>
                                    </th>
                                    <td><?php echo $value['username']; ?></td>
                                    <td><?php echo $statuss[$value['status']]; ?></td>
                                    <td><?php echo $value['login_time']; ?></td>
                                    <td><?php echo $value['create_time']; ?></td>
                                    <td>
                                        <a href="<?php echo MODULE_URL ?>admin/save/?id=<?php echo $value['id']; ?>">编辑</a> 
                                        <a href="<?php echo MODULE_URL ?>admin/addPermission/?adminId=<?php echo $value['id']; ?>">权限</a>
                                    </td>
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
                    <option value="setStatus">设置状态</option>
                </select>

                <select name="setStatus" id="setStatus" style="display: none;">
                    <option value="">请选择</option>
                    <?php if ($statuss != null) : ?>
                        <?php foreach ($statuss as $key => $value) : ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <input type="submit" id="manageButton"  value="提交" onclick="return confirmAction();" />
            </div>
        </form>
        <div class="bd_page">
        <?php echo $page; ?>
        </div>
        
        <div class="panel panel-default">
            <div class="panel-heading"><button class="btn btn-default active" type="submit" id="filterFormTitle">条件筛选+</button></div>
            <div class="panel-body" id="filterForm">
                <div class="col-md-8">
                <form action="<?php echo MODULE_URL ?>admin/index/" method="get">
                    <div class="form-group">
                        <label for="filter_id">ID</label>
                        <input type="text" name="id" class="form-control" id="filter_id" value="<?php echo $id; ?>">
                    </div>

                    <div class="form-group">
                        <label for="filter_username">用户名</label>
                        <input type="text" name="username" class="form-control" id="filter_username" value="<?php echo $username; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="filter_status">状态</label>
                        <select name="status" class="form-control" id="filter_status">
                            <option value="">请选择</option>
                            <?php if ($statuss != null) : ?>
                                <?php foreach ($statuss as $key => $value) : ?>
                                    <option value="<?php echo $key; ?>" <?php if ($status === (string) $key) : ?>selected="selected"<?php endif; ?>><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>登陆时间</label>
                        <input name="loginTimeStart" data-provide="datepicker" class="form-control" type="text" value="<?php echo $loginTimeStart; ?>" placeholder=">= 起始日期"> - 
                        <input name="loginTimeEnd" data-provide="datepicker" class="form-control" type="text" value="<?php echo $loginTimeEnd; ?>" placeholder="< 结束日期">
                    </div>
                    
                    <div class="form-group">
                        <label>创建日期</label>
                        <input name="createTimeStart" data-provide="datepicker" class="form-control" type="text" value="<?php echo $createTimeStart; ?>" placeholder=">= 起始日期"> - 
                        <input name="createTimeEnd" data-provide="datepicker" class="form-control" type="text" value="<?php echo $createTimeEnd; ?>" placeholder="< 结束日期">
                    </div>
                    
                    <input name="filter" type="hidden" value="1" />
                    <input class="btn btn-primary" type="submit" value="筛选"> 
                    <a class="btn btn-default" href="<?php echo MODULE_URL ?>admin/index" role="button">清空条件</a>
                </form>
                </div>
            </div>
        </div>
        
    </div>
</div>
<?php tc_view(MODULE_VIEW.'public/footer'); ?>