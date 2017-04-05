<?php tc_view(MODULE_VIEW.'public/header', array(
    'title' => '编辑权限',
    'onView' => 'adminPermission',
)); ?>
<div class="row">
    <div class="col-md-6">
        <p class="bd_title">编辑权限</p>
    </div>
    <div class="col-md-6">
        <p class="text-right"><a href="<?php echo MODULE_URL ?>adminPermission/index">返回列表</a></p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <?php if (isset($message) && $message) : ?>
            <div class="bd_warning_bg">
                <p class="bg-warning"><?php echo $message; ?></p>
            </div>
        <?php endif; ?>

        <form action="<?php echo MODULE_URL ?>/adminPermission/save" method="post">
            <div class="form-group<?php if (tc_form_error('name')) : ?> has-error<?php endif; ?>">
                <label for="input_name" class="control-label">名称</label>
                <input type="text" name="name" id="input_name" class="form-control" aria-describedby="helpBlock" value="<?php echo tc_get_value(tc_set_value('name'), $row['name']); ?>">
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('name'); ?></span>
            </div>

            <div class="form-group<?php if (tc_form_error('desc_txt')) : ?> has-error<?php endif; ?>">
                <label for="input_desc_txt" class="control-label">描述</label>
                <input type="text" name="desc_txt" id="input_desc_txt" class="form-control" aria-describedby="helpBlock" value="<?php echo tc_get_value(tc_set_value('desc_txt'), $row['desc_txt']); ?>">
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('desc_txt'); ?></span>
            </div>
            
            <div class="form-group<?php if (tc_form_error('status')) : ?> has-error<?php endif; ?>">
                <label for="input_status" class="control-label">状态</label>
                <select name="status" class="form-control" id="input_status">
                    <?php if ($statuss) : ?>
                        <?php foreach ($statuss as $key => $value) : ?>
                            <option value="<?php echo $key; ?>" <?php if ($row['status'] === (string) $key || tc_set_value('status') === (string) $key) : ?>selected="selected"<?php endif; ?>><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('status'); ?></span>
            </div>

            <input name="id" type="hidden" value="<?php echo tc_get_value(tc_set_value('id'), $row['id']); ?>" />
            <input class="btn btn-primary" type="submit" value="保存">
        </form>
        <p>&nbsp;</p>
    </div>
</div>

<?php tc_view(MODULE_VIEW.'public/footer'); ?>