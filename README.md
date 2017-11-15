### swoole websocket 实现html5直播
##### 直播的大潮下、我也赶上潮流、使用websocket swoole实现了直播功能、并将直播嵌套到了微信公众号当中
#### 实现思路
###### 1)视频流的获取(H5当中有一个、具有一个调用摄像头获取视频的功能)
###### 2)视频流的传输(使用swoole创建websocket服务器、进行传输)

### 文件说明:
###### 1：wx_live微信观看直播端，功能：微信语音聊天，微信定位，直播间中定位
###### 2：index.blade.php为直播端 使用的是laravel框架由于控制器、模型中并没有什么重要代码就没有上传!
###### 3: wx_zhibo_server.php 核心代码 swoole生成的websocket服务端进行视频的转发
