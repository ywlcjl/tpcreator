<?php tc_view(MODULE_VIEW.'public/header', array(
    'title' => '登录',
)); ?>
<script>
        function refreshVerify() {
            var ts = Date.parse(new Date())/1000;
            $('#verify_img').attr("src", "/captcha?id=" + ts);
            return false;
        }
</script>
    
<div class="row">
    <div class="col-md-6">
        <p class="bd_title">后台登录</p>
    </div>
    <div class="col-md-6">
        <p class="text-right"></p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <?php if (isset($message) && $message) : ?>
            <div class="bd_warning_bg">
                <p class="bg-warning"><?php echo $message; ?></p>
            </div>
        <?php endif; ?>

        <form action="<?php echo MODULE_URL; ?>login/signIn" method="post">
            <div class="form-group<?php if (tc_form_error('username')) : ?> has-error<?php endif; ?>">
                <label for="input_username" class="control-label">用户名</label>
                <input type="text" name="username" id="input_username" class="form-control" aria-describedby="helpBlock" value="<?php echo tc_get_value(tc_set_value('username'), $row['username']); ?>">
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('username'); ?></span>
            </div>

            <div class="form-group<?php if (tc_form_error('password')) : ?> has-error<?php endif; ?>">
                <label for="input_password" class="control-label">密码</label>
                <input type="password" name="password" id="input_password" class="form-control" aria-describedby="helpBlock" value="">
                <span id="helpBlock" class="help-block"><?php echo tc_form_error('password'); ?></span>
            </div>
                        
            <div class="form-group<?php if (tc_form_error('captcha') || $captchaError) : ?> has-error<?php endif; ?>">
                <label for="input_captcha" class="control-label">验证码</label>
                <input type="text" name="captcha" id="input_captcha" class="form-control" aria-describedby="helpBlock" maxlength="5" value="">
                <span id="helpBlock" class="help-block">
                    <?php echo tc_form_error('captcha'); ?>
                </span>
                <div><img src="<?php echo captcha_src();?>" alt="captcha" id="verify_img" onclick="refreshVerify()" /></div>
                <a href="#" onclick="return refreshVerify()">换一张?</a>
            </div>

            <input class="btn btn-default" type="submit" value="登入" />
        </form>
        <p>&nbsp;</p>
    </div>
</div>


<?php tc_view(MODULE_VIEW.'public/footer'); ?>