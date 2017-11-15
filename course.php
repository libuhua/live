<?php
	/*
	 * 后台存储 视频流 往期视频进程
	 * 
	 */
	//激活websocket进程
	$server = new swoole_websocket_server('0.0.0.0',9502);
	$redis = new Redis();
	$redis->connect('127.0.0.1',6379);
	/*当连接时触发的事件
	 *$ser 对象
	 *$dom 传输过来的数据对象
	*/
	$server->on('open',function($ser,$dom){

	});
	$server->on('message',function($ser,$dom) use($redis){
		$data = json_decode($dom->data,true);
		// 将图片保存到临时文件
		if($data['token'] == 'voide'){
			$uid = $data['uid'];
			$img = $data['camera'];
			//创建临时目录
			$path = "/liveold/".$uid."tmpfile/";
			if(!is_dir($path)){
				mkdir($path, 777, true);
			}
			//保存图片
			$redis->incr($uid);
            $num = $redis->get($uid);
            $img = str_replace('data:image/jpeg;base64,', '', $img);
		    $img = str_replace(' ', '+', $img);
		    $data = base64_decode($img);
			// 使用redis进行判断
			$filename = $path."liveimg".sprintf("%09d", $num).".jpeg";
			// file_put_contents($path.$filename, base64_decode());
			file_put_contents($filename, $data);
		}elseif ($data['token'] == 'create'){
			/*
			 * 将临时文件中的image生成为视频
			 * uid filename 通过uid查找该主播的临时文件夹
			 * shell_exec 执行命令
			 * 重置redis队列
			*/
            echo "生成视频";
            $uid = $data['uid'];
            //视频名称
            $save = $data['filename'];
            //视频文件名称
            $file = time();
            // 将视频键值对存储到redis
            $val = $save.",".$file.".mp4";
            $key = $uid."voide";
            $redis->lPush($key,$val);
            //向集合中添加发布者的uid
            $redis->sAdd('videouid',$uid);
            
            $tmppath = "/liveold/".$uid."tmpfile/";
			$cmd1 = "ffmpeg -y -r 9 -i ".$tmppath."liveimg%09d.jpeg /mp4/".$file.".mp4";
			$cmd2 = "rm -rf ".$tmppath."*";
			shell_exec($cmd1);
			shell_exec($cmd2);
			$redis->del($uid);
		}
	});
	/*当有关闭时触发的事件
	 *$ser 对象
	 *$fd  要关闭的websocketId
	*/
	$server->on('close',function($ser,$fd){
		
	});
	/*
	* 启动服务
	*/
	$server->start();