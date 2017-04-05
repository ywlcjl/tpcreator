<?php tc_view(MODULE_VIEW.'public/header', array(
    'title' => 'example',
    'onView' => 'example',
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

            if(manageName == 'set_status') {
                $('#set_status').css('display', '');
            } else {
                $('#set_status').css('display', 'none');
            }
        });
    });
</script>

<div class="row">
    <div class="col-md-6">
        <p class="bd_title">example列表</p>
    </div>
    <div class="col-md-6">
        <p class="text-right"><a href="<?php echo MODULE_URL; ?>example/save">添加example</a></p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <form name="form1" id="form1" method="post" action="<?php echo MODULE_URL; ?>example/manage">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>id</th>
                            <th>名称</th>
                            <th>描述</th>
                            <th>price</th>
                            <th>文章ID</th>
                            <th>状态</th>
                            <th>发布时间</th>
                            <th>update_time</th>
                            <th>create_time</th>
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
                                    <td><?php echo $value['name']; ?></td>
                                    <td><?php echo $value['desc_txt']; ?></td>
                                    <td><?php echo $value['price']; ?></td>
                                    <td><?php echo $value['article_id']; ?></td>
                                    <td><?php echo $statuss[$value['status']]; ?></td>
                                    <td><?php echo $value['post_time']; ?></td>
                                    <td><?php echo $value['update_time']; ?></td>
                                    <td><?php echo $value['create_time']; ?></td>
                                    <td><a href="<?php echo MODULE_URL; ?>example/save/?id=<?php echo $value['id']; ?>">编辑</a></td>
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
                            <option value="set_article_id">设置文章ID</option>
                            <option value="set_status">设置状态</option>
                </select>
                    <select name="set_article_id" id="set_article_id" style="display: none;">
                        <option value="">请选择</option>
                        <?php if ($articles != null) : ?>
                            <?php foreach ($articles as $key=>$value) : ?>
                                <option value="<?php echo $value['id']; ?>"><?php echo $value['id']; ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <select name="set_status" id="set_status" style="display: none;">
                        <option value="">请选择</option>
                        <?php if ($statuss != null) : ?>
                            <?php foreach ($statuss as $key=>$value) : ?>
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
                <form action="<?php echo MODULE_URL; ?>example/index/" method="get">
                    <div class="form-group">
                        <label for="filter_id">id</label>
                        <input type="text" name="id" class="form-control" id="filter_id" value="<?php echo $id; ?>">
                    </div>

                    <div class="form-group">
                        <label for="filter_name">名称</label>
                        <input type="text" name="name" class="form-control" id="filter_name" value="<?php echo $name; ?>">
                    </div>

                    <div class="form-group">
                        <label for="filter_desc_txt">描述</label>
                        <input type="text" name="desc_txt" class="form-control" id="filter_desc_txt" value="<?php echo $desc_txt; ?>">
                    </div>

                    <div class="form-group">
                        <label>price范围</label>
                        <input name="priceMin" class="form-control" type="text" value="<?php echo $priceMin; ?>" placeholder=">= input"> - 
                        <input name="priceMax" class="form-control" type="text" value="<?php echo $priceMax; ?>" placeholder="< input">
                    </div>

                    <div class="form-group">
                        <label for="filter_article_id">文章ID</label>
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
                        <label>发布时间</label>
                        <input name="postTimeStart" data-provide="datepicker" class="form-control" type="text" value="<?php echo $postTimeStart; ?>" placeholder=">= 起始日期"> - 
                        <input name="postTimeEnd" data-provide="datepicker" class="form-control" type="text" value="<?php echo $postTimeEnd; ?>" placeholder="<= 结束日期">
                    </div>

                    <div class="form-group">
                        <label>update_time</label>
                        <input name="updateTimeStart" data-provide="datepicker" class="form-control" type="text" value="<?php echo $updateTimeStart; ?>" placeholder=">= 起始日期"> - 
                        <input name="updateTimeEnd" data-provide="datepicker" class="form-control" type="text" value="<?php echo $updateTimeEnd; ?>" placeholder="<= 结束日期">
                    </div>

                    <div class="form-group">
                        <label>create_time</label>
                        <input name="createTimeStart" data-provide="datepicker" class="form-control" type="text" value="<?php echo $createTimeStart; ?>" placeholder=">= 起始日期"> - 
                        <input name="createTimeEnd" data-provide="datepicker" class="form-control" type="text" value="<?php echo $createTimeEnd; ?>" placeholder="<= 结束日期">
                    </div>

                    <input name="filter" type="hidden" value="1" />
                    <input class="btn btn-primary" type="submit" value="筛选"> 
                    <a class="btn btn-default" href="<?php echo MODULE_URL; ?>example/index" role="button">清空条件</a>
                </form>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php tc_view(MODULE_VIEW.'public/footer'); ?>
