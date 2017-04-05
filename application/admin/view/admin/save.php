<?php tc_view(MODULE_VIEW.'public/header', array(
    'title' => '编辑管理员',
    'onView' => 'admin',
)); ?>
<div class="row">
    <div class="col-md-6">
        <p class="bd_title">编辑管理员</p>
    </div>
    <div class="col-md-6">
        <p class="text-right"><a href="<?php echo MODULE_URL ?>admin/index">返回列表</a></p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <?php if (isset($message) && $message) : ?>
            <div class="bd_warning_bg">
                <p class="bg-warning"><?php echo $message; ?></p>
            </div>
        <?php endif; ?>

        <form action="<?php echo MODULE_URL ?>/admin/save" method="post">
            <div class="form-group<?php if (tc_form_error('username')) : ?> has-error<?php endif; ?>">
                <label for="input_username" class="control-label">用户名</label>
                <input type="text" name="username" id="input_username" class="form-control" aria-describedby="helpBlock" value="<?php echo tc_get_value(tc_set_value('username'), $row['username']); ?>" placeholder="长度3~20个字符">
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('username'); ?></span>
            </div>

            <div class="form-group<?php if (tc_form_error('password') || $passwordError) : ?> has-error<?php endif; ?>">
                <label for="input_password" class="control-label">密码</label>
                <input type="password" name="password" id="input_password" class="form-control" placeholder="输入6~20位的密码" aria-describedby="helpBlock" value="">
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('password'); ?></span>
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