<?php tc_view(MODULE_VIEW.'public/header', array(
    'title' => '编辑附件',
    'onView' => 'attach',
)); ?>
<script type="text/javascript">
    $(document).ready(function () {

    });
</script>

<div class="row">
    <div class="col-md-6">
        <p class="bd_title">编辑附件</p>
    </div>
    <div class="col-md-6">
        <p class="text-right"><a href="<?php echo MODULE_URL; ?>attach/index">返回列表</a></p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <?php if (isset($message) && $message) : ?>
            <div class="bd_warning_bg">
                <p class="bg-warning"><?php echo $message; ?></p>
            </div>
        <?php endif; ?>

        <form action="<?php echo MODULE_URL; ?>attach/save" method="post">
            <div class="form-group<?php if (tc_form_error('name')) : ?> has-error<?php endif; ?>">
                <label for="input_name" class="control-label">name</label>
                <input type="text" name="name" id="input_name" class="form-control" aria-describedby="helpBlock" value="<?php echo tc_get_value(tc_set_value('name'), $row['name']); ?>">
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('name'); ?></span>
            </div>

            <div class="form-group<?php if (tc_form_error('orig_name')) : ?> has-error<?php endif; ?>">
                <label for="input_orig_name" class="control-label">orig_name</label>
                <input type="text" name="orig_name" id="input_orig_name" class="form-control" aria-describedby="helpBlock" value="<?php echo tc_get_value(tc_set_value('orig_name'), $row['orig_name']); ?>">
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('orig_name'); ?></span>
            </div>

            <div class="form-group<?php if (tc_form_error('path')) : ?> has-error<?php endif; ?>">
                <label for="input_path" class="control-label">path</label>
                <input type="text" name="path" id="input_path" class="form-control" aria-describedby="helpBlock" value="<?php echo tc_get_value(tc_set_value('path'), $row['path']); ?>">
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('path'); ?></span>
            </div>

            <div class="form-group<?php if (tc_form_error('type')) : ?> has-error<?php endif; ?>">
                <label for="input_type" class="control-label">type</label>
                <input type="text" name="type" id="input_type" class="form-control" aria-describedby="helpBlock" value="<?php echo tc_get_value(tc_set_value('type'), $row['type']); ?>">
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('type'); ?></span>
            </div>

           <div class="form-group<?php if (tc_form_error('article_id')) : ?> has-error<?php endif; ?>">
                <label for="input_article_id" class="control-label">article_id</label>
                <select name="article_id" class="form-control">
                    <option value="">请选择</option>
                    <?php if ($articles != null) : ?>
                        <?php foreach ($articles as $value) : ?>
                            <option value="<?php echo $value['id']; ?>" <?php if ($row['article_id'] === (string)$value['id'] || tc_set_value('article_id') === (string)$value['id']) : ?>selected="selected"<?php endif; ?>><?php echo $value['id']; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('article_id'); ?></span>
            </div>

            <input name="id" type="hidden" value="<?php echo tc_get_value(tc_set_value('id'), $row['id']); ?>" />
            <input class="btn btn-primary" type="submit" value="保存" />
        </form>
        <p>&nbsp;</p>
    </div>
</div>

<?php tc_view(MODULE_VIEW.'public/footer'); ?>

