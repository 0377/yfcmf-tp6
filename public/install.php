<?php
/**
 *  ============================================================================
 *  Created by PhpStorm.
 *  User: Ice
 *  邮箱: ice@sbing.vip
 *  网址: https://sbing.vip
 *  Date: 2019/11/27 下午6:12
 *  ============================================================================
 */

set_time_limit(0);   //设置运行时间
error_reporting(E_ALL & ~E_NOTICE);  //显示全部错误
define('ROOT_PATH', dirname(dirname(__FILE__)));  //定义根目录
define('DBCHARSET', 'utf8mb4');   //设置数据库默认编码
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Asia/Shanghai');
}
input($_GET);
input($_POST);
function input(&$data)
{
    foreach ((array)$data as $key => $value) {
        if (is_string($value)) {
            if (function_exists('get_magic_quotes_gpc') && PHP_VERSION < '7.4') {
                if (!get_magic_quotes_gpc()) {
                    $value = htmlentities($value, ENT_NOQUOTES);
                    $value = addslashes(trim($value));
                }
            } else {
                $value = htmlentities($value, ENT_NOQUOTES);
                $value = addslashes(trim($value));
            }

        } else {
            $data[$key] = input($value);
        }
    }
}

//判断是否安装过程序
if (is_file('../config/install.lock') && $_GET['step'] != 5) {
    @header("Content-type: text/html; charset=UTF-8");
    echo "系统已经安装过了，如果要重新安装，那么请删除config目录下的install.lock文件";
    exit;
}


$config = include "../config/fastadmin.php";
$site_name = 'YFCMF-TP6';
$site_url = 'https://www.iuok.cn';
$version = $config['version'] ?: '2.0.0';
$_date = date('Y');
$sql_url = '../yfcmf_tp6.sql';
// 后台默认地址
$admin_url = '/yfcmf';
$html_title = '程序安装向导';
$install_css = <<<EOF
<style>
@charset "utf-8";

* { word-wrap: break-word; outline: none;}
html, body, ul, li, p { padding: 0; margin: 0;}

body { font-family: "microsoft yahei", "Microsoft YaHei", "Lucida Grande", "Lucida Sans Unicode", Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 20px; color: #7E8C8D; background-color: #FFFFFF;}
h1, h2, h4, h5, h6 { font-weight: normal; margin: 0;}
i, em { font-style: normal;}
ul, ol, li { list-style-type: none;}
a { color: #16A085; text-decoration: underline; transition: all 0.25s ease 0s;}
html { -webkit-text-size-adjust: none; min-height: 101%;}


/* Form Input
--------------------------------------*/
input[type="text"], input[type="password"] { font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; color: #7E8C8C; line-height: 20px; text-indent: 6px; height: 20px; padding: 8px 5px; border: 2px solid #BEC3C7; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-transition: border .25s linear, color .25s linear; -moz-transition: border .25s linear, color .25s linear; -o-transition: border .25s linear, color .25s linear; transition: border .25s linear, color .25s linear;}
input[type="text"]:-moz-placeholder, input[type="password"]:-moz-placeholder { color: #B2BCC5;}
input[type="text"]::-webkit-input-placeholder, input[type="password"]::-webkit-input-placeholder { color: #B2BCC5;}
input[type="text"].placeholder, input[type="password"].placeholder { color: #B2BCC5;}
input[type="text"]:focus, input[type="password"]:focus { border-color: #1ABC9C; -webkit-box-shadow: none; -moz-box-shadow: none; box-shadow: none;}
input[type="text"].flat, input[type="password"].flat { border-color: transparent;}
input[type="text"].flat:hover, input[type="password"].flat:hover { border-color: #BDC3C7;}
input[type="text"].flat:focus, input[type="password"].flat:focus { border-color: #1ABC9C;}
input[disabled], input[readonly], textarea[disabled], textarea[readonly] { color: #D5DBDB; background-color: #F4F6F6; border-color: #D5DBDB; cursor: default;}
input[type="text"], input[type="password"] { width: 290px;}
input.error { border-color: #F30;}

/* Button Style
-------------------------------------- */
.btn { font-size: 18px; line-height: 20px; color: #FFFFFF; background: #BDC3C7; display: inline-block; height: 20px; padding: 15px 30px; margin: 0 5px; border: none; text-decoration: none; text-shadow: none; -webkit-border-radius: 4px; -moz-border-radius: 4px/; border-radius: 4px; -webkit-box-shadow: none; -moz-box-shadow: none; box-shadow: none; -webkit-transition: 0.25s; -moz-transition: 0.25s; -o-transition: 0.25s; transition: 0.25s; -webkit-backface-visibility: hidden;}
.btn:hover, .btn:focus { color: #FFFFFF; background-color: #CACFD2; outline: none; -webkit-transition: 0.25s; -moz-transition: 0.25s; -o-transition: 0.25s; transition: 0.25s; -webkit-backface-visibility: hidden;}
.btn:active, .btn.active { color: rgba(255, 255, 255, 0.75); background-color: #A1A6A9; -webkit-box-shadow: none; -moz-box-shadow: none; box-shadow: none;}
.btn.disabled, .btn[disabled] { color: rgba(255, 255, 255, 0.75); background-color: #BDC3C7; opacity: 0.7; filter: alpha(opacity=70)/*IE*/; -webkit-box-shadow: none; -moz-box-shadow: none; box-shadow: none;}
.btn.btn-primary { background-color: #f50;}
.btn.btn-primary:hover, .btn.btn-primary:focus { background-color: #f40;}

/* Scrollbar jQuery Plugin
-------------------------------------- */
.ps-container .ps-scrollbar-x, .ps-container .ps-scrollbar-y { background-color: #AAA; height: 8px; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; position: absolute; z-index: auto; bottom: 3px; opacity: 0; filter: alpha(opacity=0); -webkit-transition: opacity.25s linear; -moz-transition: opacity .25s linear; transition: opacity .25s linear;}
.ps-container .ps-scrollbar-y { right: 3px; width: 8px; bottom: auto; }
.ps-container:hover .ps-scrollbar-x, .ps-container:hover .ps-scrollbar-y { opacity: .6; filter: alpha(opacity=60);}
.ps-container .ps-scrollbar-x:hover, .ps-container .ps-scrollbar-y:hover { opacity: .9; filter: alpha(opacity=90); cursor: default;}
.ps-container .ps-scrollbar-x.in-scrolling, .ps-container .ps-scrollbar-y.in-scrolling { opacity: .9; filter: alpha(opacity=90);}

/* iCheck jquery plugin
-------------------------------------- */
.icheckbox_flat-green, .iradio_flat-green { background: url(./assets/img/install_bg.png) no-repeat; display: block; width: 20px; height: 20px; float: left; margin: 0; padding: 0; border: none; cursor: pointer;}
.icheckbox_flat-green { background-position: 0 -280px;}
.icheckbox_flat-green.checked { background-position: -22px -280px;}
.icheckbox_flat-green.disabled { background-position: -44px -280px; cursor: default;}
.icheckbox_flat-green.checked.disabled { background-position: -66px -280px;}
.iradio_flat-green { background-position: -88px -280px;}
.iradio_flat-green.checked { background-position: -110px -280px;}
.iradio_flat-green.disabled { background-position: -132px -280px; cursor: default;}
.iradio_flat-green.checked.disabled { background-position: -154px -280px;}


/* Layout head
-------------------------------------- */
.header { width: 100%; height: 100px; border-bottom: solid 1px #ECF0F1;}
.header .layout { width: 960px; height: 100px; margin: 0 auto; position: relative; z-index: 1;}
.header .layout .title { height: 60px; position: absolute; z-index: 1; top: 20px; left: 0;}
.header .layout .title h2 { font-size: 36px; line-height: 40px; color: #f50; display: block; height: 40px;}
.header .layout .title h5 { font-size: 13px; font-weight: 600; line-height: 20px; color: #2C3E50; text-align: center; display: block; height: 20px;}
.header .layout .title h5 i { font-size: 11px; font-weight: normal; display: inline-block; margin: 0 0 0 5px;}
.header .layout .version { color: #7E8C8D; position: absolute; z-index: 1; bottom: 20px; right: 0;}

/* Layout Central
-------------------------------------- */
.main { width: 100%; min-height: 400px; padding: 30px 0;}

/* Layout Bottom - copyright information
-------------------------------------- */
.footer { text-align: center; width: 100%; height: 60px; padding: 10px 0 20px 0; border-top: solid 1px #ECF0F1;}
.footer h5 { font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; line-height: 24px; color: #7E8C8C;}
.footer h5 .blue { color: #2B81BA;}
.footer h5 .orange { color: #E77E23;}
.footer h5 .black { color: #2D3E50;}
.footer h5 sup { color: #34495E; margin-left: 2px;}
.footer h6 { font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 11px; line-height: 16px; color: #92A5A5;}
.footer h6 a { text-decoration: none; color: #7E8C8C;}
.footer h6 a:hover { text-decoration: blink;}

/* Content section
-------------------------------------- */
.text-box { width: 898px; height: 358px; margin: 0 auto; border: solid 1px #ECF0F1; position: relative; z-index: 1; overflow: hidden;}

.license { line-height: 24px; width: 858px; margin: 20px auto;}
.license h1 { font-size: 18px; line-height: 28px; color: #7E8C8D; text-align: center;}
.license p { font-size: 12px; text-indent: 2em;}

.btn-box { text-align: center; width: 900px; height: 50px; margin: 30px auto auto; overflow: hidden;}

.error { color: red; padding-left:5px;}
/* Installation step by step guide
-------------------------------------- */
.step-box { width: 900px; height: 200px; margin: 0 auto;}
.step-box .text-nav { width: 900px; height: 70px; position: relative; z-index: 1;}
.step-box .text-nav h1 { font-size: 60px; font-weight: 600; line-height: 68px; text-align: right; width: 49%; height: 68px; position: absolute; z-index: 1; top: 0; right: 51%;}
.step-box .text-nav h2 { font-size: 32px; font-weight: 600; line-height: 36px; text-align: left; width: 50%; height: 36px; position: absolute; z-index: 1; top: 4px; left: 51%;}
.step-box .text-nav h5 { font-size: 12px; line-height: 20px; color: #BEC3C6; text-align: left; width: 50%; height: 20px; position: absolute; z-index: 1; bottom: 5px; left: 51%;}
.procedure-nav { width: 900px; height: 100px; margin: 30px auto 0 auto; position: relative; z-index: 1;}
.schedule-ico { position: absolute; z-index: 1; top: 0; left: 0;}
.schedule-ico span { background: url(./assets/img/install_bg.png) no-repeat; display: block; width: 26px; height: 26px; float: left; margin-left: 230px;}
.schedule-ico span.a { background-position: 0 0; margin-left: 50px;}
.schedule-ico span.b { background-position: -30px 0;}
.schedule-ico span.c { background-position: -60px 0;}
.schedule-ico span.d { background-position: -90px 0;}
.schedule-line-bg { background-color: #ECF0F1; width: 900px; height: 8px; -webkit-border-radius: 4px; -moz-border-radius: 4px;  -o-border-radius: 4px; border-radius: 4px; position: absolute; z-index: 1; top: 40px; left: 0;}
.schedule-line-now { background-color: #f50; height: 8px; -webkit-border-radius: 4px;-moz-border-radius: 4px;-o-border-radius: 4px;border-radius: 4px; position: absolute; z-index: 2; top: 40px; left: 0;}
.schedule-line-now em { FILTER:progid:DXImageTransform.Microsoft.Gradient(gradientType=1, startColorStr='#f50', endColorStr='#ECF0F1')/*IE6-9*/; background-image: -ms-linear-gradient(right, #ECF0F1 0%, #f50 100%)/* IE10 Consumer Preview */; background-image: -moz-linear-gradient(right, #ECF0F1 0%, #f50 100%)/* Mozilla Firefox */; background-image: -o-linear-gradient(right, #ECF0F1 0%, #f50 100%)/* Opera */; background-image: -webkit-gradient(linear, right top, left top, color-stop(0, #ECF0F1), color-stop(1, #f50))/* Webkit (Safari/Chrome 10) */; background-image: -webkit-linear-gradient(right, #ECF0F1 0%, #f50 100%)/* Webkit (Chrome 11+) */; background-image: linear-gradient(to left, #ECF0F1 0%, #f50 100%)/* W3C Markup, IE10 Release Preview */; display: block; width: 60px; height: 8px; float: right;}
.schedule-point-bg { position: absolute; z-index: 3; top: 32px; left: 0;}
.schedule-point-bg span { background-color: #FFF; display: block; width: 24px; height: 24px; float: left; margin-left: 232px;-webkit-border-radius: 12px;-moz-border-radius: 12px; border-radius: 12px;}
.schedule-point-bg span.a { margin-left: 50px;}
.schedule-point-now { position: absolute; z-index: 4; top: 36px; left: 0;}
.schedule-point-now span { background-color: #ECF0F1; display: block; width: 16px; height: 16px; float: left; margin-left: 240px; -webkit-border-radius: 12px; -moz-border-radius: 12px; border-radius: 12px;}
.schedule-point-now span.a { margin-left: 54px;}
.schedule-text { width: 900px; height: 30px; position: absolute; z-index: 4; top: 66px; left: 0;}
.schedule-text span { font-size: 14px; color: #BEC3C7; text-align: center; display: block; width: 90px; float: left; margin-left: 167px;}
.schedule-text span.a { margin-left: 16px;}
#step1 .schedule-ico span.a { background-position: -120px 0;}
#step1 .schedule-line-now { width: 200px;}
#step1 .schedule-point-now span.a { background-color: #f50;}
#step1 .schedule-text span.a { font-weight: 600; color: #f50;}
#step2 .schedule-ico span.a { background-position: -120px 0;}
#step2 .schedule-ico span.b { background-position: -150px 0;}
#step2 .schedule-line-now { width: 440px;}
#step2 .schedule-point-now span.a, #step2 .schedule-point-now span.b { background-color: #f50;}
#step2 .schedule-text span.a, #step2 .schedule-text span.b { font-weight: 600; color: #f50;}
#step3 .schedule-ico span.a { background-position: -120px 0;}
#step3 .schedule-ico span.b { background-position: -150px 0;}
#step3 .schedule-ico span.c { background-position: -180px 0;}
#step3 .schedule-line-now { width: 680px;}
#step3 .schedule-point-now span.a, #step3 .schedule-point-now span.b, #step3 .schedule-point-now span.c { background-color: #f50;}
#step3 .schedule-text span.a, #step3 .schedule-text span.b, #step3 .schedule-text span.c { font-weight: 600; color: #f50;}
#step4 .schedule-ico span.a { background-position: -120px 0;}
#step4 .schedule-ico span.b { background-position: -150px 0;}
#step4 .schedule-ico span.c { background-position: -180px 0;}
#step4 .schedule-ico span.d { background-position: -210px 0;}
#step4 .schedule-line-now { width: 900px;}
#step4 .schedule-line-now em { display: none;}
#step4 .schedule-point-now span.a, #step4 .schedule-point-now span.b, #step4 .schedule-point-now span.c, #step4 .schedule-point-now span.d { background-color: #f50;}
#step4 .schedule-text span.a, #step4 .schedule-text span.b, #step4 .schedule-text span.c, #step4 .schedule-text span.d { font-weight: 600; color: #f50;}

/* Select Install Module
-------------------------------------- */
.select-install { width: 900px; margin: 20px auto 0 auto;}
.select-install label { display: block; height: 20px; clear: both; margin: 30px auto 0 100px;}
.select-install label h4 { font-size: 13px; font-weight: 600; float: left; margin-left: 6px;}
.select-install label h5 { font-size: 12px; float: left; margin-left: 6px;}
.select-module { background-color: #ECF0F1; width: 100%; height: 250px; margin: 30px auto; padding: 20px 0; position: relative; z-index: 2;}
.select-module .arrow { font-size: 0px; line-height: 0; width: 0px; height: 0px; margin-right: 200px; border-color: transparent transparent #ECF0F1 transparent; border-width: 10px; border-style: dashed dashed solid dashed; position: absolute; z-index: 1; top: -20px; right: 50%;}
.select-module ul { width: 984px; margin: 0 auto; overflow: hidden;}
.select-module ul li { background-color: #FFFFFF; width: 200px; height: 220px; float: left; padding: 15px; margin: 0 8px;}
.select-module ul li .ico { background: url(./assets/img/install_bg.png) no-repeat; width: 96px; height: 96px; margin: 30px auto 0 auto;}
.select-module ul li.shop .ico { background-position: -110px -60px;}
.select-module ul li.cms .ico { background-position: -210px -60px;}
.select-module ul li.circle .ico { background-position: -310px -60px;}
.select-module ul li.microshop .ico { background-position: -410px -60px;}
.select-module ul li h4 { font-size: 16px; font-weight: 600; line-height: 24px; color: #f50; text-align: center; margin-top: 10px;}
.select-module ul li p { font-size: 12px; line-height: 18px; margin: 10px 10px 0 10px;}

/* Test PHP configuration table
-------------------------------------- */
.content-box { width: 900px; margin: 0 auto;}
.content-box table { width: 100%; margin: 20px 0;}
.content-box table caption { font-size: 18px; line-height: 24px; color: #7E8C8D; text-align: left; padding: 5px 1%;}
.content-box table th[scope="col"] { font-size: 14px; line-height: 24px; color: #FFF; background-color: #f50; text-align: left; height: 20px; padding: 7px 1%;}
.content-box table th[scope="row"] { line-height: 20px; background-color: #ECF0F1; text-align: left; height: 20px; padding: 5px 1%;}
.content-box table th[scope="col"]:first-of-type { border-radius: 5px 0 0 0;}
.content-box table th[scope="col"]:last-of-type { border-radius: 0 5px 0 0;}
.content-box table tr:last-of-type th[scope="row"]:last-of-type { border-radius: 0 0 0 5px;}
.content-box table td { line-height: 20px; background-color: #F5F7F8; height: 20px; padding: 5px 1%;}
.content-box table tr:last-of-type td:last-of-type { border-radius: 0 0 5px 0;}
.content-box table tr:last-of-type td:nth-last-child(3) { border-radius: 0 0 0 5px;}
.content-box table td span { line-height: 20px; display: block; height: 20px;}
.content-box table td span i { background: url(./assets/img/install_bg.png) no-repeat; vertical-align: middle; display: inline-block; width: 16px; height: 16px; margin-right: 6px;}
.content-box table td span.yes i { background-position: 0 -30px;}
.content-box table td span.no i { background-position: -16px -30px;}
.content-box table td span.no { color: #F33;}
/* Fill the form
-------------------------------------- */
.form-box { width: 900px; margin: 0 auto;}
.form-box fieldset { border-width: 1px 0 0 0; border-style: solid; border-color: #ECF0F1;}
.form-box legend { font-size: 18px; line-height: 24px; color: #7E8C8D;}
.form-box div { height: 40px; margin: 10px 0 0 0; clear: both;}
.form-box div label { font-size: 12px; line-height: 40px; color: #94A5A5; text-indent: 80px; display: block; width: 220px; height: 40px; float: left;}
.form-box div span { vertical-align: middle; display: inline-block; width: 300px; height: 40px; position: relative; z-index: 1;}
.form-box div span input { position: absolute; z-index: 1; top: 0; left: 0;}
.form-box div span font { line-height: 20px; background: url(./assets/img/install_bg.png) no-repeat -540px 2px; height: 20px; padding-left: 20px; position: absolute; z-index: 9; top: 10px; right: 5px;}
.form-box div.icheckbox_flat-green { clear: none; }

.form-box div h4 { font-size: 14px; line-height: 40px; color: #94A5A5; float: left; height:40px; margin-left: 6px;}
.form-box div em { color: #BEC3C6; margin-left: 20px;}

/* Installation is complete
-------------------------------------- */
.final-succeed { width: 900px; height: 85px; margin: 10px auto 0 auto; position: relative; z-index: 1;}
.final-succeed span.ico { background: url(./assets/img/install_bg.png) no-repeat 0 -60px; width: 96px; height: 85px; position: absolute; z-index: 1; top: 0; right: 60%;}
.final-succeed h2 { font-size: 32px; font-weight: 600; line-height: 36px; text-align: left; width: 50%; height: 36px; position: absolute; z-index: 1; top: 10px; left: 42%;}
.final-succeed h5 { font-size: 14px; line-height: 20px; color: #BEC3C6; text-align: left; width: 50%; height: 20px; position: absolute; z-index: 1; bottom: 5px; left: 42%;}
.final-site-nav { background-color: #ECF0F1; width: 100%; margin: 50px auto; position: relative; z-index: 2;}
.final-site-nav .arrow { font-size: 0; line-height: 0; width: 0; height: 0;   border-style: dashed dashed solid dashed; border-width: 10px; border-color: transparent transparent #ECF0F1 transparent; position: absolute; z-index: 1; top: -20px; right: 50%;}
.final-site-nav ul { width: 400px; height: 210px; margin: 0 auto;}
.final-site-nav ul li { width: 200px; float: left;}
.final-site-nav ul li .ico { background: url(./assets/img/install_bg.png) no-repeat; width: 110px; height: 110px; margin: 20px auto 0 auto;}
.final-site-nav ul li.cms .ico { background-position: 0 -160px;}
.final-site-nav ul li.shop .ico { background-position: -110px -160px;}
.final-site-nav ul li.circle .ico { background-position: -220px -160px;}
.final-site-nav ul li.microshop .ico { background-position: -330px -160px;}
.final-site-nav ul li.admin .ico { background-position: -440px -160px;}
.final-site-nav ul li h5 { font-size: 14px; font-weight: 600; line-height: 24px; text-align: center; margin-top: 10px;}
.final-site-nav ul li h6 { font-size: 12px; line-height: 20px; text-align: center;}
.final-intro { width: 900px; margin: 0 auto;}
.final-intro p { font-size: 14px; margin-left: 300px;}
.final-intro p em { font-size: 12px; color: #BEC3C6;}
</style>
EOF;

$html_header = <<<EOF
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>$html_title</title>
$install_css

<link href="https://cdn.bootcss.com/jquery.perfect-scrollbar/0.5.6/perfect-scrollbar.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="https://cdn.bootcss.com/jquery/1.8.2/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.bootcss.com/jquery.perfect-scrollbar/0.5.6/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="https://cdn.bootcss.com/jquery-mousewheel/3.0.6/jquery.mousewheel.min.js"></script>

<script src="https://cdn.bootcss.com/iCheck/1.0.2/icheck.min.js"></script>

<script type="text/javascript" src="https://cdn.bootcdn.net/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js"></script>

</head>
<body>
<div class="header">
  <div class="layout">
    <div class="title">
      <h5></h5>
      <h2>{$site_name}安装向导</h2>
    </div>
    <div class="version">版本: $version</div>
  </div>
</div>
EOF;

$html_footer = <<<EOF
<div class="footer">
  <h5>Powered by <font class="blue">$site_name</font><font class="orange"></font></h5>
  <h6>版权所有 2019-$_date &copy; <a href="$site_url" target="_blank">$site_name</a></h6>
</div>
</body>
</html>
EOF;
if (!isset($_GET['step'])) {
    $_GET['step'] = 0;
    if (!in_array($_GET['step'], array(1, 2, 3, 4, 5))) {
        $_GET['step'] = 0;
    }
}

switch ($_GET['step']) {
    case 1:
        $env_items = array();
        $dirfile_items = array(
            array('type' => 'dir', 'path' => 'config')
        );
        $func_items = array(
            array('name' => 'PDO', 'type' => 'class'),
            array('name' => 'putenv', 'type' => 'func'),
            array('name' => 'proc_open', 'type' => 'func'),
            array('name' => 'proc_get_status', 'type' => 'func'),
            array('name' => 'fsockopen', 'type' => 'func'),
            array('name' => 'gethostbyname', 'type' => 'func'),
            array('name' => 'file_get_contents', 'type' => 'func'),
            array('name' => 'mb_convert_encoding', 'type' => 'func'),
            array('name' => 'json_encode', 'type' => 'func'),
            array('name' => 'curl_init', 'type' => 'func'),
        );
        env_check($env_items);
        dirfile_check($dirfile_items);
        function_check($func_items);

        $env_items_html = '';
        foreach ($env_items as $v) {
            $_status = $v['status'] ? 'yes' : 'no';
            $env_items_html .= "<tr><td scope='row'>{$v['name']}</td><td>{$v['min']}</td><td>{$v['good']}</td><td><span class='{$_status}'><i></i>{$v['cur']}</span></td></tr>";
        }
        $dirfile_items_html = '';
        foreach ($dirfile_items as $k => $v) {
            $_status = $v['status'] == 1 ? 'yes' : 'no';
            $_status2 = $v['status'] == 1 ? '可写' : '不可写';
            $dirfile_items_html .= "<tr>
                <td>{$v['path']}</td>
                <td><span>可写</span></td>
                <td><span class='{$_status}'><i></i>{$_status2}</span></td>
            </tr>";
        }
        $func_items_html = '';
        foreach ($func_items as $k => $v) {
            $_status = $v['status'] == 1 ? 'yes' : 'no';
            $_status2 = $v['status'] == 1 ? '支持' : '不支持';
            $func_items_html .= "<tr>
                <td>{$v['name']}</td>
                <td><span>支持</span></td>
                <td><span class='{$_status}'><i></i>{$_status2}</span></td>
             </tr>";
        }
        $step = <<<EOF
$html_header
<div class="main">
  <div class="step-box" id="step1">
    <div class="text-nav">
      <h1>Step.1</h1>
      <h2>开始安装</h2>
      <h5>检测服务器环境及文件目录权限</h5>
    </div>
    <div class="procedure-nav">
      <div class="schedule-ico"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-point-now"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-point-bg"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-line-now"><em></em></div>
      <div class="schedule-line-bg"></div>
      <div class="schedule-text"><span class="a">检查安装环境</span><span class="b">选择安装方式</span><span class="c">创建数据库</span><span class="d">安装</span></div>
    </div>
  </div>
  <div class="content-box">
    <table width="100%" border="0" cellspacing="2" cellpadding="0">
      <caption>
      环境检查
      </caption>
      <tr>
        <th scope="col">项目</th>
        <th width="25%" scope="col">程序所需</th>
        <th width="25%" scope="col">最佳配置推荐</th>
        <th width="25%" scope="col">当前服务器</th>
      </tr>
      $env_items_html
    </table>
    <table width="100%" border="0" cellspacing="2" cellpadding="0">
      <caption>
      目录、文件权限检查
      </caption>
      <tr>
        <th scope="col">目录文件</th>
        <th width="25%" scope="col">所需状态</th>
        <th width="25%" scope="col">当前状态</th>
      </tr>
      $dirfile_items_html
    </table>
    <table width="100%" border="0" cellspacing="2" cellpadding="0">
      <caption>
      函数检查
      </caption>
      <tr>
        <th scope="col">目录文件</th>
        <th width="25%" scope="col">所需状态</th>
        <th width="25%" scope="col">当前状态</th>
      </tr>
      $func_items_html
    </table>
  </div>
  <div class="btn-box"><a href="install.php" class="btn btn-primary">上一步</a><a href='###' id="next" class="btn btn-primary">下一步</a></div>
</div>
$html_footer
<script>
$(document).ready(function(){
    $('#next').on('click',function(){
        if (typeof($('.no').html()) == 'undefined'){
            $(this).attr('href','install.php?step=2');
        }else{
            alert($('.no').eq(0).parent().parent().find('td:first').html()+' 未通过检测!');
            $(this).attr('href','###');
        }
    });
});
</script>
EOF;
        echo $step;
        break;
    case 2:
        $step = <<<EOF
$html_header
<div class="main">
  <div class="step-box" id="step2">
    <div class="text-nav">
      <h1>Step.2</h1>
      <h2>选择安装方式</h2>
      <h5>根据需要选择系统模块完全或手动安装</h5>
    </div>
    <div class="procedure-nav">
      <div class="schedule-ico"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-point-now"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-point-bg"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-line-now"><em></em></div>
      <div class="schedule-line-bg"></div>
      <div class="schedule-text"><span class="a">检查安装环境</span><span class="b">选择安装方式</span><span class="c">创建数据库</span><span class="d">安装</span></div>
    </div>
  </div>
  <form method="get" id="install_form" action="install.php">
  <input type="hidden" value="3" name="step">
    <div class="select-install">
      <label>
      <input type="radio" name="iCheck" value="full" id="radio-1" class="green-radio" checked >
      <h4>完全安装 {$site_name}</h4>
      <h4>系统</h4>
      </label>
    </div>
    <div class="select-module" id="result" style="display:none">
      <div class="arrow"></div>
      <ul>
        <li class="cms">
          <input type="checkbox" name="cms" id="cms" value="1" checked="checked"  disabled="">
          <div class="ico"></div>
          <h4>商城</h4>
          <p>商城模块是一套功能完善的多用户商城系统，也是整套电商门户的核心程序...</p>
        </li>
        <li class="shop">
          <input type="checkbox" name="shop" id="shop" value="1" checked="checked" disabled="">
          <div class="ico"></div>
          <h4>CMS</h4>
          <p>CMS模块拥有文章、画报、专题发布等功能，自定义编辑模板,可在文章内容中关联商品...</p>
        </li>
        <li class="circle">
          <input type="checkbox" name="circle" value="1" checked="checked" disabled="">
          <div class="ico"></div>
          <h4>圈子</h4>
          <p>圈子模块是会员交流互动的理想环境，增强站点人气。主题帖中可与商品关联，特色鲜明...</p>
        </li>
        <li class="microshop">
          <input type="checkbox" name="microshop" value="1" checked="checked" disabled="">
          <div class="ico"></div>
          <h4>微商城</h4>
          <p>微商城模块以新颖的形式展示会员已购商品、实物秀图，提高商品浏览量，促进商城经营...</p>
        </li>
      </ul>
    </div>
    <div class="btn-box"><a href="install.php?step=1" class="btn btn-primary">上一步</a><a id="next" href="javascript:void(0);" class="btn btn-primary">下一步</a></div>
  </form>
</div>
$html_footer
<script>
$(document).ready(function(){
  $('input[type="radio"]').on('ifChecked', function(event){
    if(this.id == 'radio-0'){
            $('.select-module').show();
        }else{
            $('.select-module').hide();
        }
  }).iCheck({
    checkboxClass: 'icheckbox_flat-green',
    radioClass: 'iradio_flat-green'
  });
  $('input[type="checkbox"]').iCheck({
    checkboxClass: 'icheckbox_flat-green',
    radioClass: 'iradio_flat-green'
  });
    $('#next').click(function(){
        if ($('#cms').attr('checked') && $('#shop').attr('checked')){
            $('#install_form').submit();
        }else{
            alert('商城与CMS必须安装');
        }
    });
});
</script>
EOF;

        echo $step;
        break;
    case 3:
        $install_error = '';
        $install_recover = '';

        step3($install_error, $install_recover);


        $db_host = $_POST['db_host'] ?? '127.0.0.1';
        $db_name = $_POST['db_name'] ?? 'yfcmf_tp6';
        $db_user = $_POST['db_user'] ?? '';
        $db_pwd = $_POST['db_pwd'] ?? '';
        $db_prefix = $_POST['db_prefix'] ?? 'fa_';
        $db_port = $_POST['db_port'] ?? '3306';

        $install_error_html = '';
        if ($install_error != '') {
            $install_error_html = "<div>
          <label></label>
          <font class='error'>{$install_error}</font></div>";
        }
        $_POST['admin'] = $_POST['admin'] ?? '';
        $_POST['password'] = $_POST['password'] ?? '';
        $_POST['rpassword'] = $_POST['rpassword'] ?? '';
        $step = <<<EOF
 $html_header
<div class="main">
  <div class="step-box" id="step3">
    <div class="text-nav">
      <h1>Step.3</h1>
      <h2>创建数据库</h2>
      <h5>填写数据库及站点相关信息</h5>
    </div>
    <div class="procedure-nav">
      <div class="schedule-ico"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-point-now"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-point-bg"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-line-now"><em></em></div>
      <div class="schedule-line-bg"></div>
      <div class="schedule-text"><span class="a">检查安装环境</span><span class="b">选择安装方式</span><span class="c">创建数据库</span><span class="d">安装</span></div>
    </div>
  </div>
  <form action="" id="install_form" method="post">
    <input type="hidden" value="submit" name="submitform">
    <input type="hidden" value="$install_recover" name="install_recover">
    <div class="form-box control-group">
      <fieldset>
        <legend>数据库信息</legend>
        <div>
          <label>数据库服务器</label>
          <span>
          <input type="text" name="db_host" maxlength="20" value="$db_host">
          </span> <em>数据库服务器地址，一般为localhost</em></div>
        <div>
          <label>数据库名</label>
          <span>
          <input type="text" name="db_name" maxlength="40" value="$db_name">
          </span> <em></em></div>
        <div>
          <label>数据库用户名</label>
          <span>
          <input type="text" name="db_user" maxlength="20" value="$db_user">
          </span> <em></em></div>
        <div>
          <label>数据库密码</label>
          <span>
          <input type="password" name="db_pwd" maxlength="20" value="$db_pwd">
          </span> <em></em></div>
        <div>
          <label>数据库表前缀</label>
          <span>
          <input type="text" name="db_prefix" maxlength="20" value="$db_prefix">
          </span> <em>同一数据库运行多个程序时，请修改前缀</em></div>
        <div>
          <label>数据库端口</label>
          <span>
          <input type="text" name="db_port" maxlength="20" value="$db_port">
          </span> <em>数据库默认端口一般为3306</em></div>
        $install_error_html
      </fieldset>
      <fieldset>
        <legend>网站信息</legend>
        <div>
          <label>管理员账号</label>
          <span>
          <input name="admin" value="{$_POST['admin']}" maxlength="20" type="text">
          </span> <em></em></div>
        <div>
          <label>管理员密码</label>
          <span>
          <input name="password" id="password" maxlength="20" value="{$_POST['password']}" type="password">
          </span> <em>管理员密码不少于6个字符</em></div>
        <div>
          <label>重复密码</label>
          <span>
          <input name="rpassword" value="{$_POST['rpassword']}" maxlength="20" type="password">
          </span> <em>确保两次输入的密码一致</em></div>
      </fieldset>
    </div>
    <div class="btn-box"><a href="index.php?step=2" class="btn btn-primary">上一步</a><a id="next" href="javascript:void(0);" class="btn btn-primary">下一步</a></div>
  </form>
</div>
$html_footer
<script>
$(document).ready(function(){
    $('input[type="checkbox"]').iCheck({
    checkboxClass: 'icheckbox_flat-green',
    radioClass: 'iradio_flat-green'
  });
});

$(function(){
    jQuery.validator.addMethod("lettersonly", function(value, element) {
        return this.optional(element) || /^[^:%,'\*\"\s\<\>\&]+$/i.test(value);
    }, "不得含有特殊字符");
    $("#install_form").validate({
        errorElement: "font",
    rules : {
        db_host : {required : true},
        db_name : {required : true},
        db_user : {required : true},
        db_port : {required : true,digits : true},
        admin : {required : true,lettersonly : true},
        password : {required : true, minlength : 6},
        rpassword : {required : true,equalTo : '#password'},
      }
    });

    jQuery.extend(jQuery.validator.messages, {
      required: "未输入",
      digits: "格式错误",
      lettersonly: "不得含有特殊字符",
      equalTo: "两次密码不一致",
      minlength: "密码至少6位"
    });

    $('#next').click(function(){
        $('#install_form').submit();
    });

});
</script>

EOF;

        echo $step;

        break;
    case 4:

        break;
    case 5:
        $sitepath = strtolower(substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
        $sitepath = str_replace('install', "", $sitepath);
        $auto_site_url = strtolower('http://' . $_SERVER['HTTP_HOST'] . $sitepath);

        $step = <<<EOF
$html_header
<div class="main">
  <div class="final-succeed"> <span class="ico"></span>
    <h2>程序已成功安装</h2>
    <h5>选择您要进入的页面</h5>
  </div>
  <div class="final-site-nav">
    <div class="arrow"></div>
    <ul>
      <li class="shop">
        <div class="ico"></div>
        <h5><a href="$auto_site_url" target="_blank">首页</a></h5>
        <h6>系统首页</h6>
      </li>
      
      <li class="admin">
        <div class="ico"></div>
        <h5><a href="{$auto_site_url}{$admin_url}" target="_blank">系统管理</a></h5>
        <h6>系统后台</h6>
      </li>
    </ul>
  </div>
  <div class="final-intro">
    <p><strong>系统管理默认地址:&nbsp;</strong><a href="{$auto_site_url}{$admin_url}" target="_blank">{$auto_site_url}{$admin_url}</a></p>
    <p><strong>网站首页默认地址:&nbsp;</strong><a href="$auto_site_url" target="_blank">$auto_site_url</a></p>
  </div>
</div>
$html_footer
<script type="text/javascript">
$(document).ready(function(){
    //自定义滚定条
    $('#text-box').perfectScrollbar();
});
</script>
EOF;

        echo $step;
        break;
    default:
        # code...
        $site_url = 'https://iuok.cn';
        $step = <<<EOF
$html_header
<div class="main">
  <div class="text-box" id="text-box">
    <div class="license">
      <h1>系统安装协议</h1>
      <p>感谢您选择本系统。本系统是自主开发、集资讯、商城、讨论组、微商城、在线IM、客户管理于一体的门户型解决方案。官方网址为 {$site_url}。</p>
      <p>用户须知：本协议是您与{$site_name}交流中心之间关于您安装使用{$site_name}公司提供的{$site_name}系统及服务的法律协议。无论您是个人或组织、盈利与否、用途如何（包括以学习和研究为目的），均需仔细阅读本协议。请您审阅并接受或不接受本协议条款。如您不同意本协议条款或{$site_name}公司随时对其的修改，您应不使用或主动取消{$site_name}公司提供的{$site_name}系统。否则，您的任何对{$site_name}电商系统使用的行为将被视为您对本服务条款全部的完全接受，包括接受{$site_name}对服务条款随时所做的任何修改。</p>
      <p>本服务条款一旦发生变更，{$site_name}公司将在网页上公布修改内容。修改后的服务条款一旦在网页上公布即有效代替原来的服务条款。如果您选择接受本条款，即表示您同意接受协议各项条件的约束。如果您不同意本服务条款，则不能获得使用本服务的权利。您若有违反本条款规定，{$site_name}公司有权随时中止或终止您对{$site_name}系统的使用资格并保留追究相关法律责任的权利。</p>
      <p>在理解、同意、并遵守本协议的全部条款后，方可开始使用{$site_name}系统。您可能与{$site_name}公司直接签订另一书面协议，以补充或者取代本协议的全部或者任何部分。</p>
      <p>{$site_name}拥有本软件的全部知识产权。本软件只供许可协议，并非出售。{$site_name}只允许您在遵守本协议各项条款的情况下复制、下载、安装、使用或者以其他方式受益于本软件的功能或者知识产权。</p>
      <h3>I. 协议许可的权利</h3>
      <ol>
        <li>您可以在完全遵守本许可协议的基础上，将本软件应用于非商业用途，而不必支付软件版权许可费用。</li>
        <li>您可以在协议规定的约束和限制范围内修改{$site_name}系统源代码(如果被提供的话)或界面风格以适应您的网站要求。</li>
        <li>您拥有使用本软件构建的网站中全部会员资料、文章、商品及相关信息的所有权，并独立承担与使用本软件构建的网站内容的审核、注意义务，确保其不侵犯任何人的合法权益，独立承担因使用{$site_name}系统和服务带来的全部责任，若造成{$site_name}或用户损失的，您应予以全部赔偿。</li>
        <li>本协议是您与{$site_name}公司之间关于您安装使用{$site_name}公司提供的{$site_name}系统及服务的法律协议，若您需将{$site_name}系统或服务用于商业用途，必须另行获得{$site_name}的授权许可，您在获得商业授权之后，您可以将本软件应用于商业用途，同时依据所购买的授权类型中确定的技术支持期限、技术支持方式和技术支持内容，自购买时刻起，在技术支持期限内拥有通过指定的方式获得指定范围内的技术支持服务。商业授权用户享有反映和提出意见的权利，相关意见将被作为首要考虑，但没有一定被采纳的承诺或保证。</li>
      </ol>
      <p></p>
      <h3>II. 协议规定的约束和限制</h3>
      <ol>
        <li>未获{$site_name}公司商业授权之前，不得将本软件用于商业用途（包括但不限于企业网站、经营性网站、以营利为目或实现盈利的网站）。购买商业授权请登录http://www.{$site_name}.com参考相关说明，也可以致电+86-023-12345678了解详情。</li>
        <li>不得对本软件或与之关联的商业授权进行出租、出售、抵押或发放子许可证。</li>
        <li>无论用途如何、是否经过修改或美化、修改程度如何，只要使用{$site_name}系统的整体或任何部分，未经授权许可，页面页脚处的{$site_name}系统的版权信息都必须保留，而不能清除或修改。</li>
        <li>禁止在{$site_name}系统的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。</li>
        <li>如果您未能遵守本协议的条款，您的授权将被终止，所许可的权利将被收回，同时您应承担相应法律责任。</li>
      </ol>
      <p></p>
      <h3>III. 有限担保和免责声明</h3>
      <ol>
        <li>本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。</li>
        <li>用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任。</li>
        <li>{$site_name}公司不对使用本软件构建的平台中的会员、商品或文章信息承担责任，全部责任由您自行承担。</li>
        <li>{$site_name}公司对提供的软件和服务之及时性、安全性、准确性不作担保，由于不可抗力因素、{$site_name}公司无法控制的因素（包括黑客攻击、停断电等）等造成软件使用和服务中止或终止，而给您造成损失的，您同意放弃追究{$site_name}公司责任的全部权利。</li>
        <li>{$site_name}公司特别提请您注意，{$site_name}公司为了保障公司业务发展和调整的自主权，{$site_name}公司拥有随时经或未经事先通知而修改服务内容、中止或终止部分或全部软件使用和服务的权利，修改会公布于{$site_name}公司网站相关页面上，一经公布视为通知。{$site_name}公司行使修改或中止、终止部分或全部软件使用和服务的权利而造成损失的，{$site_name}公司不需对您或任何第三方负责。</li>
      </ol>
      <p></p>
      <p>有关{$site_name}系统最终用户授权协议、商业授权与技术服务的详细内容，均由{$site_name}公司独家提供。{$site_name}公司拥有在不事先通知的情况下，修改授权协议和服务价目表的权利，修改后的协议或价目表对自改变之日起的新授权用户生效。</p>
      <p>一旦您开始安{$site_name}系统，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权利的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权利。</p>
      <p></p>
      <p align="right">{$site_name}有限责任公司</p>
    </div>
  </div>
  <div class="btn-box"><a href="install.php?step=1" class="btn btn-primary">同意协议进入安装</a><a href="javascript:window.close()" class="btn">不同意</a></div>
</div>
$html_footer
<script type="text/javascript">
$(document).ready(function(){
    //自定义滚定条
    $('#text-box').perfectScrollbar();
});
</script>


EOF;
        echo $step;
        break;
}

function step3(&$install_error, &$install_recover)
{
    global $html_title, $html_header, $html_footer, $sql_url;
    if (!isset($_POST['submitform']) || $_POST['submitform'] != 'submit') {
        return;
    }
    $db_host = $_POST['db_host'];
    $db_port = $_POST['db_port'];
    $db_user = $_POST['db_user'];
    $db_pwd = $_POST['db_pwd'];
    $db_name = $_POST['db_name'];
    $db_prefix = $_POST['db_prefix'];
    $admin = $_POST['admin'];
    $password = $_POST['password'];
    $rpassword = $_POST['rpassword'];
    if (!$db_host || !$db_port || !$db_user || !$db_pwd || !$db_name || !$db_prefix || !$admin || !$password) {
        $install_error = '输入不完整，请检查';
    }
    if (strpos($db_prefix, '.') !== false) {
        $install_error .= '数据表前缀为空，或者格式错误，请检查';
    }

    if (strlen($admin) > 15 || preg_match("/^$|^c:\\con\\con$|　|[,\"\s\t\<\>&]|^游客|^Guest/is", $admin)) {
        //$install_error .= '非法用户名，用户名长度不应当超过 15 个英文字符，且不能包含特殊字符，一般是中文，字母或者数字';
    }

    if (!preg_match("/^\w{3,12}$/", $admin)) {
        $install_error .= "用户名只能由3-12位数字、字母、下划线组合";
    }
    if (!preg_match("/^[\S]{6,16}$/", $password)) {
        $install_error .= "密码长度必须在6-16位之间，不能包含空格";
    }
    if ($password !== $rpassword) {
        $install_error .= "两次输入的密码不一致";
    }
    if ($install_error != '') {
        return;
    }

    //检测能否读取安装文件
    $sql = file_get_contents($sql_url);
    if (!$sql) {
        $install_error = "数据库文件无法打开";
        return;
    }
    $sql = str_replace("`__PREFIX__", "`{$db_prefix}", $sql);
    try {
        $pdo = new PDO("mysql:host={$db_host};port={$db_port}", $db_user, $db_pwd, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ));
    } catch (PDOException $e) {
        $install_error = '数据库连接失败';
        return;
    }

    //检测是否支持innodb存储引擎
    $pdoStatement = $pdo->query("SHOW VARIABLES LIKE 'innodb_version'");
    $result = $pdoStatement->fetch();
    if (!$result) {
        $install_error = "当前数据库不支持innodb存储引擎，请开启后再重新尝试安装";
        return;
    }

    try {
        $pdo->query("USE `{$db_name}`");
        if ($_POST['install_recover'] != 'yes') {
            $install_error = '数据表已存在，继续安装将会覆盖已有数据';
            $install_recover = 'yes';
            return;
        }
    } catch (PDOException $e) {

    }


    $pdo->query("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
    $pdo->query("USE `{$db_name}`");

    //$pdo->exec($sql);


    $step = <<<EOF
$html_header
<div class="main">
  <div class="step-box" id="step4">
    <div class="text-nav">
      <h1>Step.4</h1>
      <h2>安装数据库</h2>
      <h5>正在执行数据库安装</h5>
    </div>
    <div class="procedure-nav">
      <div class="schedule-ico"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-point-now"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-point-bg"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-line-now"><em></em></div>
      <div class="schedule-line-bg"></div>
      <div class="schedule-text"><span class="a">检查安装环境</span><span class="b">选择安装方式</span><span class="c">创建数据库</span><span class="d">安装</span></div>
    </div>
  </div>
  <div class="text-box" id="text-box">
    <div class="license" id="license"></div>
  </div>
  <div class="btn-box"><a href="javascript:void(0);" id="install_process" class="btn btn-primary">正在安装 ...</a></div>
</div>
$html_footer
<script type="text/javascript">
var scroll_height = 0;
function showmessage(message) {
    document.getElementById('license').innerHTML += message+"<br/>";
    document.getElementById("text-box").scrollTop = 500+scroll_height;
    scroll_height += 40;
}
$(document).ready(function(){
    //自定义滚定条
    $('#text-box').perfectScrollbar();
});
</script>
EOF;
    echo $step;


    runquery($sql, $db_prefix, $pdo);
    showjsmessage('初始化数据 ... 成功 ');

    $sitepath = strtolower(substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
    $sitepath = str_replace('install', "", $sitepath);
    $auto_site_url = strtolower('http://' . $_SERVER['HTTP_HOST'] . $sitepath);
    write_config($auto_site_url);


    //管理员账号密码
    $newSalt = substr(md5(uniqid(true)), 0, 6);
    $newPassword = md5(md5($password) . $newSalt);
    $pdo->query("UPDATE {$db_prefix}admin SET username = '{$admin}', email = 'admin@admin.com',password = '{$newPassword}', salt = '{$newSalt}' WHERE username = 'admin'");


    //新增一个标识文件，用来屏蔽重新安装
    $fp = @fopen('../config/install.lock', 'wb+');
    @fclose($fp);
    exit("<script type=\"text/javascript\">document.getElementById('install_process').innerHTML = '安装完成，下一步...';document.getElementById('install_process').href='install.php?step=5&username={$username}&password={$password}';</script>");
    exit();
}

//execute sql
function runquery($sql, $db_prefix, $pdo)
{

    if (!isset($sql) || empty($sql)) {
        return;
    }
    $sql = str_replace("\r", "\n", str_replace('#__', $db_prefix, $sql));
    $ret = array();
    $num = 0;
    foreach (explode(";\n", trim($sql)) as $query) {
        $ret[$num] = '';
        $queries = explode("\n", trim($query));
        foreach ($queries as $query) {
            $ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0] . $query[1] == '--') ? '' : $query;
        }
        $num++;
    }
    unset($sql);
    foreach ($ret as $query) {
        $query = trim($query);
        if ($query) {
            if (substr($query, 0, 12) == 'CREATE TABLE') {
                $line = explode('`', $query);
                $data_name = $line[1];
                showjsmessage('数据表  ' . $data_name . ' ... 创建成功');
                $pdo->exec($query);
                unset($line, $data_name);
            } else {
                $pdo->exec($query);
            }
        }
    }
}

//抛出JS信息
function showjsmessage($message)
{
    echo '<script type="text/javascript">showmessage(\'' . addslashes($message) . ' \');</script>' . "\r\r";
    flush();
    ob_flush();
}

//写入config文件
function write_config($url)
{
    $charset = 'utf8mb4';
    $db_host = $_POST['db_host'];
    $db_port = $_POST['db_port'];
    $db_user = $_POST['db_user'];
    $db_pwd = $_POST['db_pwd'];
    $db_name = $_POST['db_name'];
    $db_prefix = $_POST['db_prefix'];

    $_env =
        "APP_DEBUG = true
[APP]
DEFAULT_TIMEZONE = Asia/Shanghai

[DATABASE]
TYPE = mysql
HOSTNAME = {$db_host}
DATABASE = {$db_name}
PREFIX = {$db_prefix}
USERNAME = {$db_user}
PASSWORD = {$db_pwd}
HOSTPORT = {$db_port}
CHARSET = {$charset}
DEBUG = true

[LANG]
default_lang = zh-cn";
    @file_put_contents('../.env', $_env);

}


/**************function**************/
/**
 * environmental check
 */
function env_check(&$env_items)
{
    $env_items[] = array('name' => '操作系统', 'min' => '无限制', 'good' => 'linux', 'cur' => PHP_OS, 'status' => 1);
    $env_items[] = array(
        'name' => 'PHP版本',
        'min' => '7.0',
        'good' => '7.2',
        'cur' => PHP_VERSION,
        'status' => (PHP_VERSION < 7.0 ? 0 : 1)
    );
    $tmp = function_exists('gd_info') ? gd_info() : array();
    preg_match("/[\d.]+/", $tmp['GD Version'], $match);
    unset($tmp);
    $env_items[] = array(
        'name' => 'GD库',
        'min' => '2.0',
        'good' => '2.0',
        'cur' => $match[0],
        'status' => ($match[0] < 2 ? 0 : 1)
    );
    $env_items[] = array(
        'name' => '附件上传',
        'min' => '未限制',
        'good' => '2M',
        'cur' => ini_get('upload_max_filesize'),
        'status' => 1
    );
    $disk_place = function_exists('disk_free_space') ? floor(disk_free_space(ROOT_PATH) / (1024 * 1024)) : 0;
    $env_items[] = array(
        'name' => '磁盘空间',
        'min' => '100M',
        'good' => '>100M',
        'cur' => empty($disk_place) ? '未知' : $disk_place . 'M',
        'status' => $disk_place < 100 ? 0 : 1
    );
}

/**
 * file check
 */
function dirfile_check(&$dirfile_items)
{
    foreach ($dirfile_items as $key => $item) {
        $item_path = '/' . $item['path'];
        if ($item['type'] == 'dir') {
            if (!dir_writeable(ROOT_PATH . $item_path)) {
                if (is_dir(ROOT_PATH . $item_path)) {
                    $dirfile_items[$key]['status'] = 0;
                    $dirfile_items[$key]['current'] = '+r';
                } else {
                    $dirfile_items[$key]['status'] = -1;
                    $dirfile_items[$key]['current'] = 'nodir';
                }
            } else {
                $dirfile_items[$key]['status'] = 1;
                $dirfile_items[$key]['current'] = '+r+w';
            }
        } else {
            if (file_exists(ROOT_PATH . $item_path)) {
                if (is_writable(ROOT_PATH . $item_path)) {
                    $dirfile_items[$key]['status'] = 1;
                    $dirfile_items[$key]['current'] = '+r+w';
                } else {
                    $dirfile_items[$key]['status'] = 0;
                    $dirfile_items[$key]['current'] = '+r';
                }
            } else {
                if ($fp = @fopen(ROOT_PATH . $item_path, 'wb+')) {
                    $dirfile_items[$key]['status'] = 1;
                    $dirfile_items[$key]['current'] = '+r+w';
                    @fclose($fp);
                    @unlink(ROOT_PATH . $item_path);
                } else {
                    $dirfile_items[$key]['status'] = -1;
                    $dirfile_items[$key]['current'] = 'nofile';
                }
            }
        }
    }
}

/**
 * dir is writeable
 *
 * @return number
 */
function dir_writeable($dir)
{
    $writeable = 0;
    if (!is_dir($dir)) {
        @mkdir($dir, 0755);
    } else {
        @chmod($dir, 0755);
    }
    if (is_dir($dir)) {
        if ($fp = @fopen("$dir/test.txt", 'w')) {
            @fclose($fp);
            @unlink("$dir/test.txt");
            $writeable = 1;
        } else {
            $writeable = 0;
        }
    }
    return $writeable;
}

/**
 * function is exist
 */
function function_check(&$func_items)
{
    $func = array();
    foreach ($func_items as $key => $item) {

        if ($item['type'] == 'class') {

            $func_items[$key]['status'] = class_exists($item['name']) ? 1 : 0;
        } else {

            $func_items[$key]['status'] = function_exists($item['name']) ? 1 : 0;
        }
    }

}


function show_msg($msg)
{
    global $html_title, $html_header, $html_footer;
    include 'step_msg.php';
    exit();
}

//make rand
function random($length, $numeric = 0)
{
    $seed = base_convert(md5(print_r($_SERVER, 1) . microtime()), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    $hash = '';
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed[mt_rand(0, $max)];
    }
    return $hash;
}

/**
 * drop table
 */
function droptable($table_name)
{
    return "DROP TABLE IF EXISTS `" . $table_name . "`;";
}
/**************function**************/