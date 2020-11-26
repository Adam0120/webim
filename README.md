# webim

layIM+workerman+thinkphp6的webIM即时通讯系统 v1.0正式版  

实现了功能:  
1、好友和群组即时聊天    
2、实现了好友,群组的查找,申请加群等待验证等  
3、实现了创建我的群组,删除我的群组,添加群组成员，移除群组成员,设置管理员  
4、实现了创建好友分组,好友移动分组,删除分组及好友 
5、实现了添加好友群组动态刷新列表  
6、实现了图片和文件的发送  
7、实现了单聊聊天记录和群聊聊天记录的查看
8、实现了离线用户登录后聊天记录推送

9、添加了背景鼠标滑动特效

10、添加了音乐播放功能,支持切换歌曲,刷新续听不间断

# 注意事项:  

sql文件夹下有数据库备份文件，请建立数据库，并导入。 
别忘了Workerman/App/Config/Db.php，workerman的数据库同步跟上。

copy  .env 配置自己的数据库账号密码 (讯飞语音是测试的demo,需要的也可以扒走)

代码中有部分无用代码,后续修改完善会逐渐修改替换

# 关于LayIM

因为layIM不开源，没有放入layim源码,要是商用的话，建议去http://layim.layui.com  这里，layUI的官网去授权吧  ,获取代码后将layim,js 放入public\static\layui\lay\modules\下即可

# 如何运行  

1、将代码下载到本地，并配置好虚拟域名，使 webim 可以运行。（基于tp6框架，只要按照tp6框架的配置方式即可）  

2、导入sql 文件夹下的 tp_layim.sql 表，数据库名 为 tp_layim （你可以自己改的，但是别忘了代码中更改)

3、执行composer install 加载依赖

4、启动 getwayworker，如果您想在linux下部署，请在Workerman下执行 php start.php start,如果您是win，请双击Workerman/start_for_win.bat,然后不要关闭窗口。此外，如果您更改了数据库连接，**请更改 Workerman/Applications/YourApp/start_businessworker.php** 的配置   

5、访问聊天系统，进入前台，使用前台用户的 用户名，密码登录即可聊天。 请用两个浏览器打开，登录不同的账户互相聊天

6、在win下一定要记得双击 Workerman/start_for_win.bat 启动 workerman，不要关闭！！！

7、别忘记开放7272端口,同时线上部署https需要自行部署wss

8、线上部署修改html文件里的127.0.0.1换成自己的域名

# 了解效果

http://www.xuyanzu.com

测试地址已经开放注册,欢迎注册

测试号 

zhangsan 12345

lisi 12345

wangwu 12345

# 本人精力及技术有限欢迎各位大神共同用完善

#### 后续可能加入

1 语音聊天功能,及语音转文字

2 列表右键点击修改备注头像之类

3 更多欢迎各位大神加入
