<?php tc_view(MODULE_VIEW.'public/header', array(
    'title' => '后台首页',
)); ?>

<div class="row">
    <div class="col-md-12">
        <p class="bd_title">控制台</p>
        <p>TPCreator v<?php echo TC_VERSION; ?></p>
        <p>Time <?php echo date('Y-m-d H:i:s'); ?>, 时区 <?php echo  date_default_timezone_get(); ?></p>
        <p>Support: <a href="https://github.com/ywlcjl/tpcreator" target="_blank">https://github.com/ywlcjl/tpcreator</a></p>
        <p>License : </p>
        <p>Licensed under the Apache License, Version 2.0</p>
        <p>and GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 (GPL)</p>
        <p></p>
    </div>
</div>

<?php tc_view(MODULE_VIEW.'public/footer'); ?>
