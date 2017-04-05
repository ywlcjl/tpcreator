<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        //return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
        $version = TC_VERSION;
        return <<<sss
	<h1>TpCreator v{$version}</h1>
	<div id="body">
            <p>TpCreator一个基于ThinkPHP 5和Bootstrap 3开发的通用网站后台系统. &nbsp;&nbsp;&nbsp;&nbsp;<a href="/admin/">进入后台</a> (用户admin,密码admin)</p>
            <p><b>基础功能:</b> CMS内容管理系统功能包括: 管理员, 权限, 文章分类, 文章, 文章附件, 系统设置等.</p>
            <p><b>特色功能:</b> 自动生成代码: 能够根据配置的数据表字段描述动生成对应的model, controller, view的等后台文件, 提高开发效率.</p>
            <p>&nbsp;</p>
            <p>安装说明: 请自行查看跟目录下readme.txt</p>
            <p>&nbsp;</p>
            <p>更多ThinkPHP的内容: <a href="http://www.thinkphp.cn/" target="_blank">ThinkPHP</a></p>
            <p>更多Bootstrap的内容: <a href="http://getbootstrap.com/" target="_blank">Bootstrap</a>, &nbsp;&nbsp;<a href="http://www.bootcss.com/" target="_blank">Bootstrap中文网</a>.</p>
	</div>
sss;
    }
}
