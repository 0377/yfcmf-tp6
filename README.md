FastAdmin-TP6是一款基于ThinkPHP6+Bootstrap的极速后台开发框架。

## **升级注意事项**
* 多层控制器路径“/”改成“.”，例如：auth/admin/index改成auth.admin/index，js文件里面同样要这样调整
* auth_rule表增加route字段，为访问的url
* TP5使用Db类是数组和数据模型是对象，TP6使用Db类和数据模型查询出来都是collection对象
## **主要特性**

* 基于`Auth`验证的权限管理系统
    * 支持无限级父子级权限继承，父级的管理员可任意增删改子级管理员及权限设置
    * 支持单管理员多角色
    * 支持管理子级数据或个人数据
* 强大的一键生成功能
    * 一键生成CRUD,包括控制器、模型、视图、JS、语言包、菜单、回收站等
    * 一键压缩打包JS和CSS文件，一键CDN静态资源部署
    * 一键生成控制器菜单和规则
    * 一键生成API接口文档
* 完善的前端功能组件开发
    * 基于`AdminLTE`二次开发
    * 基于`Bootstrap`开发，自适应手机、平板、PC
    * 基于`RequireJS`进行JS模块管理，按需加载
    * 基于`Less`进行样式开发
    * 基于`Bower`进行前端组件包管理
* 强大的插件扩展功能，在线安装卸载升级插件
* 通用的会员模块和API模块
* 共用同一账号体系的Web端会员中心权限验证和API接口会员权限验证
* 二级域名部署支持，同时域名支持绑定到插件
* 多语言支持，服务端及客户端支持
* 强大的第三方模块支持
* 整合第三方短信接口(阿里云、腾讯云短信)
* 无缝整合第三方云存储(七牛、阿里云OSS、又拍云)功能
* 第三方富文本编辑器支持(Summernote、Kindeditor、百度编辑器)
* 第三方登录(QQ、微信、微博)整合
* 第三方支付(微信、支付宝)无缝整合，微信支持PC端扫码支付
* 丰富的插件应用市场

## **安装使用**

https://###

## **在线演示**

https://###

用户名：admin

密　码：123456

提　示：演示站数据无法进行修改，请下载源码安装体验全部功能

## **界面截图**
![控制台](https://gitee.com/uploads/images/2017/0411/113717_e99ff3e7_10933.png "控制台")

## **问题反馈**

在使用中有任何问题，请使用以下联系方式联系我们

交流社区: https://###

QQ群: [345183861](https://shang.qq.com/wpa/qunwpa?idkey=6a55d7fe157f1093fb2f28c0883e173d0bff31948fa2939d849846fd9db72a23)

Email: (ice#sbing.vip, 把#换成@)

Gitee: https://gitee.com/nymondo/fastadmin-tp6

## **特别鸣谢**

感谢以下的项目,排名不分先后

Fastadmin：http://www.fastadmin.net

ThinkPHP：http://www.thinkphp.cn

AdminLTE：https://adminlte.io

Bootstrap：http://getbootstrap.com

jQuery：http://jquery.com

Bootstrap-table：https://github.com/wenzhixin/bootstrap-table

Nice-validator: https://validator.niceue.com

SelectPage: https://github.com/TerryZ/SelectPage


## **版权信息**

FastAdmin-TP6遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2017-2019 by FastAdmin-TP6

All rights reserved。