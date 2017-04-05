<?php tc_view(MODULE_VIEW.'public/header', array(
    'title' => '后台首页',
    'onView' => 'maintain'
)); ?>
<div class="row">
    <div class="col-md-6">
        <p class="bd_title">维护</p>
    </div>
    <div class="col-md-6">
        <p class="text-right"></p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <p><a class="btn btn-default" role="button" href="<?php echo MODULE_URL; ?>index/clearCache" onclick="return confirmAction();">删除缓存</a></p>
    </div>
</div>

<?php tc_view(MODULE_VIEW.'public/footer'); ?>