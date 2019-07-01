# Wallpaper Monitor

一款为 Wallpaper Engine 等壁纸软件设计的服务器监控小插件，使用 PHP 编写

使用需要 Linux 服务器，并且安装了 Web 服务器 + PHP 5.6 以上，可以是 Nginx、Apache 等。

在线预览：https://cdn.tcotp.cn:4443/wallpaper/

__注意：默认的壁纸包含不适合未成年人浏览的内容，有需要请自行替换。__

壁纸替换方法：修改 `index.php` ，找到第 `174` 行，替换 url 中的链接为图片地址即可。

壁纸默认是设计给 1920x1080 分辨率的桌面使用的，过低或过高分辨率都可能会造成显示异常（图片）

## 功能和特性

Wallpaper Monitor 支持：

- 显示服务器主板、CPU、内存型号
- 显示 CPU、内存使用率
- 显示服务器 CPU 核心温度（平均值）
- 仪表模式、动态刷新数据
- 后端队列刷新模式，不会影响服务器性能

更多功能正在开发，敬请期待。

## 安装方法

首先，将本项目 clone 到您的网站根目录，命名为任意名字，例如 `wallpaper`

```shell
git clone https://github.com/kasuganosoras/wallpaper_monitor
mv wallpaper_monitor/ wallpaper/
```

然后，进入该文件夹，并在命令行下运行 Daemon 程序 `index.php` ，推荐使用 `screen` 命令来守护 Daemon 进程，以防 SSH 断开后程序停止运行。

```shell
cd wallpaper/
php index.php
```

现在，你可以访问你的网站并查看效果了：

```
http://yourdomain.com/wallpaper/
```

## 效果预览

这是命令行下的效果

![img](https://i.natfrp.org/3a2c682d7c8ecee2ec8b940e411096f3.png)

前端效果图就不放了，你懂得……

## 开源协议

本项目使用 GNU 通用公共许可协议 v3（GPL v3）开源

## 感谢

壁纸来自：https://www.pixiv.net/member_illust.php?mode=medium&illust_id=72479782

壁纸画师：[まふゆ������3日目西れ07a](https://www.pixiv.net/member.php?id=5229572)

