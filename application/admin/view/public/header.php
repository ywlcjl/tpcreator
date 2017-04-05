<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $title ?> - TPCreator</title>

        <!-- Bootstrap -->
        <link href="/static/admin/bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js_lib/html5shiv.min.js"></script>
          <script src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js_lib/respond.min.js"></script>
        <![endif]-->
        
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="/static/js_lib/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="/static/admin/bootstrap/js/bootstrap.min.js"></script>
        
        <link href="/static/admin/bootstrap/datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <script src="/static/admin/bootstrap/datepicker/js/bootstrap-datepicker.min.js"></script>
        <script src="/static/admin/bootstrap/datepicker/locales/bootstrap-datepicker.zh-CN.min.js"></script>

        <link href="/static/admin/css/style.css" rel="stylesheet" type="text/css" />
        <script src="/static/admin/js/public.js" type="text/javascript"></script>
        
    </head>

    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h1>TPCreator<small> 网站后台</small></h1>
                </div>
                <div class="col-md-6">
                    <p class="text-right">
                    <?php if (session('adminId') > 0) : ?>
                        欢迎回来, <?php echo session('adminUsername'); ?>
                        &nbsp;&nbsp;<a href="<?php echo MODULE_URL; ?>/login/logout">登出</a>
                        &nbsp;&nbsp;<a href="/" target="_blank">查看网站</a>
                    <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <nav class="navbar navbar-default">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="<?php echo MODULE_URL; ?>">后台首页</a>
                    </div>

                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav">
                            <?php if (tc_admin_check_permission(1)) : ?>
                                <li class="dropdown <?php if (in_array($onView, array('admin', 'adminPermission', 'setting', 'maintain', 'create'))): ?> active<?php endif; ?>">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">系统设置 <span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li <?php if ($onView == 'admin'): ?>class="active"<?php endif; ?>><a href="<?php echo MODULE_URL; ?>admin">管理员</a></li>
                                        <li <?php if ($onView == 'adminPermission'): ?>class="active"<?php endif; ?>><a href="<?php echo MODULE_URL; ?>adminPermission">权限</a></li>
                                        <li role="separator" class="divider"></li>
                                        <li <?php if ($onView == 'setting'): ?>class="active"<?php endif; ?>><a href="<?php echo MODULE_URL; ?>setting">系统设置</a></li>
                                        <li <?php if ($onView == 'cronLog'): ?>class="active"<?php endif; ?>><a href="<?php echo MODULE_URL; ?>cronLog">日志</a></li>
                                        <li <?php if ($onView == 'maintain'): ?>class="active"<?php endif; ?>><a href="<?php echo MODULE_URL; ?>index/maintain">维护</a></li>
                                        <li <?php if ($onView == 'create'): ?>class="active"<?php endif; ?>><a href="<?php echo MODULE_URL; ?>create">代码生成</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                            <?php if (tc_admin_check_permission(2)) : ?>
                                <li class="dropdown<?php if (in_array($onView, array('articleCategory', 'article', 'attach'))): ?> active<?php endif; ?>">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">文章管理 <span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li <?php if ($onView == 'articleCategory'): ?>class="active"<?php endif; ?>><a href="<?php echo MODULE_URL; ?>article_category">分类</a></li>
                                        <li <?php if ($onView == 'article'): ?>class="active"<?php endif; ?>><a href="<?php echo MODULE_URL; ?>article">文章</a></li>
                                        <li <?php if ($onView == 'attach'): ?>class="active"<?php endif; ?>><a href="<?php echo MODULE_URL; ?>attach">附件</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                </div>
            </nav>

