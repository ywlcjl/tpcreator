<?php tc_view(MODULE_VIEW.'public/header', array(
    'title' => '附件列表',
    'onView' => 'attach',
)); ?>
<script type="text/javascript">
    $(document).ready(function() {
       //筛选
        filterForm();
        
        //全选
        checkAll();

        //日期控件
        bootstrapDate();
        
        //搜索框隐藏bug修正, 必须放在日期插件加载后隐藏
        <?php if (!$filter) : ?>
            $('#filterForm').css('display', 'none');
            $('#filterFormTitle').attr('class', 'btn btn-default');
        <?php endif; ?>

        //管理操作
        $('#manageName').change(function() {
            var manageName = $(this).val();
            if(manageName == 'set_article_id') {
                $('#set_article_id').css('display', '');
            } else {
                $('#set_article_id').css('display', 'none');
            }

        });
    });
</script>

<div class="row">
    <div class="col-md-6">
        <p class="bd_title">附件列表</p>
    </div>
    <div class="col-md-6">
        <p class="text-right"></p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <form name="form1" id="form1" method="post" action="<?php echo MODULE_URL; ?>attach/manage">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>原名称</th>
                            <th>路径</th>
                            <th>类型</th>
                            <th>文章ID</th>
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
                                    <td><a href="/<?php echo $value['path'] ?>" target="_blank" ><?php echo $value['orig_name']; ?></a></td>
                                    <td><?php echo $value['path']; ?></td>
                                    <td><?php echo $value['type']; ?></td>
                                    <td><?php echo $value['article_id']; ?></td>
                                    <td><?php echo $value['create_time']; ?></td>
                                    <td><a href="<?php echo MODULE_URL; ?>attach/save/?id=<?php echo $value['id']; ?>">编辑</a></td>
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
                            <option value="set_article_id">设置article_id</option>
                </select>
                    <select name="set_article_id" id="set_article_id" style="display: none;">
                        <option value="">请选择</option>
                        <?php if ($articles != null) : ?>
                            <?php foreach ($articles as $key=>$value) : ?>
                                <option value="<?php echo $value['id']; ?>"><?php echo $value['id']; ?></option>
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
                <form action="<?php echo MODULE_URL; ?>attach/index/" method="get">
                    <div class="form-group">
                        <label for="filter_id">id</label>
                        <input type="text" name="id" class="form-control" id="filter_id" value="<?php echo $id; ?>">
                    </div>

                    <div class="form-group">
                        <label for="filter_name">name</label>
                        <input type="text" name="name" class="form-control" id="filter_name" value="<?php echo $name; ?>">
                    </div>

                    <div class="form-group">
                        <label for="filter_orig_name">orig_name</label>
                        <input type="text" name="orig_name" class="form-control" id="filter_orig_name" value="<?php echo $orig_name; ?>">
                    </div>

                    <div class="form-group">
                        <label for="filter_type">type</label>
                        <input type="text" name="type" class="form-control" id="filter_type" value="<?php echo $type; ?>">
                    </div>

                    <div class="form-group">
                        <label for="filter_article_id">article_id</label>
                        <select name="article_id" class="form-control" id="filter_article_id">
                            <option value="">请选择</option>
                            <?php if ($articles != null) : ?>
                                <?php foreach ($articles as $value) : ?>
                                    <option value="<?php echo $value['id']; ?>" <?php if ($article_id === $value['id']) : ?>selected="selected"<?php endif; ?>><?php echo $value['id']; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>创建日期</label>
                        <input name="createTimeStart" data-provide="datepicker" class="form-control" type="text" value="<?php echo $createTimeStart; ?>" placeholder=">= 起始日期"> - 
                        <input name="createTimeEnd" data-provide="datepicker" class="form-control" type="text" value="<?php echo $createTimeEnd; ?>" placeholder="< 结束日期">
                    </div>

                    <input name="filter" type="hidden" value="1" />
                    <input class="btn btn-primary" type="submit" value="筛选"> 
                    <a class="btn btn-default" href="<?php echo MODULE_URL; ?>attach/index" role="button">清空条件</a>
                </form>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php tc_view(MODULE_VIEW.'public/footer'); ?>
