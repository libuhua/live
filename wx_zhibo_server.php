<?php
	/*
	* 微信直播室 后台服务
	* redis队列说明:
	* zhuboList+主播Id    该主播的观看者列表
	* 
	*/
	//激活swoole websocket
	$server = new swoole_websocket_server('0.0.0.0',9502);
	//使用redis进行直播流的存储
	$redis = new Redis();
	$redis->connect('127.0.0.1',6379);
	/*当连接时触发的事件
	 *$ser 对象
	 *$dom 传输过来的数据对象
	*/
	$server->on('open',function($ser,$dom){

	});
	/*当有消息时触发的事件
	 *$ser 对象
	 *$dom 传输过来的数据对象
	*/
	$server->on('message',function($ser,$dom) use($redis){

		//前台传输过来的数据
		$data = json_decode($dom->data,true);
		// var_dump($data['camera']);
		//$dom->fd 
		// $fd = $dom->fd;
		if ($data['token'] == 'zhubo'){
			// var_dump($data['uid']);
			$uid = $data['uid'];
			$roomtitle = $data['roomtitle'];
			//主播业务逻辑   进行分流
			// var_dump($data['camera']);
			$testShowHaveUser = $redis->lGet('zhuboList'.$uid,0);
	        if(!empty($testShowHaveUser)){
	            // echo $testShowHaveUser;
	            //如果有观看者 则推流到观看者
	            $i = 1;
	            for(;;){
	            	//获取观看者的openid
	                $lookId = $redis->lGet('zhuboList'.$uid,$i);
	                if(!empty($lookId)){
	                	//通过观看者id获取websocketId
	                	$websocket = $redis->get($lookId);
	                	// $audio = base64_decode();
	                	if(!empty($websocket)){
		                	$info = array(
		                		'type'=>'camera',
		                		'data'=>$data['camera'],
		                		'audio'=>$data['audio'],
		                	);
		                	// echo "ok";
		                	$pushInfo = json_encode($info);
		                    $ser->push($websocket,$pushInfo);
	                	}else{
	                		break;
	                	}
	                    // break;
	                }else{
	                    break;
	                }
	                $i++;
	            }
	        }else{
	            //如果不存在观看者(将主播ID推入观看者列表)
	            $redis->rPush('zhuboList'.$uid,$dom->fd);
	            //将当前主播加入主播列表
	            $redis->rPush('showHome',$uid);
	            //将uid关联websocket
	            //设置直播间标题
	            $redis->set('roomtitle'.$uid,$roomtitle);
	        }

		}elseif ($data['token'] == 'look') {
			//openid websocketId
			//将openid 与 websocketId关联    $data->fd(websocketId)
			$redis->set("user".$data['openid'],$dom->fd);
			//用户流(传输的数据流:要观看的主播ID)
            //先将队列中的openid移除
			$redis->lRemove('zhuboList'.$data['lookid'],"user".$data['openid'],0);
            //将当前用户压入 该主播的队列
            $redis->rPush('zhuboList'.$data['lookid'],"user".$data['openid']);
        	// $redis->set('look'.$dom->fd,$data['lookid']);
		}elseif ($data['token'] == 'record') {
			//语音操作
			$uid = $data['uid'];
			$i = 1;
			for(;;){
            	//获取观看者的openid
                $lookId = $redis->lGet('zhuboList'.$uid,$i);
                if(!empty($lookId)){
                	//通过观看者id获取websocketId
                	$websocket = $redis->get($lookId);
                	if(!empty($websocket)){
	                	$info = array(
	                		'type'=>'voice',
	                		'data'=>$data['serverId'],
	                		'openid'=>$data['openid'],
	                	);
	                	$pushInfo = json_encode($info);
	                    $ser->push($websocket,$pushInfo);
                	}else{
                		break;
                	}
                    // break;
                }else{
                    break;
                }
                $i++;
            }
		}elseif ($data['token'] == 'map') {
			//地图操作
			$uid = $data['uid'];
			$i = 1;
			for(;;){
            	//获取观看者的openid
                $lookId = $redis->lGet('zhuboList'.$uid,$i);
                if(!empty($lookId)){
                	//通过观看者id获取websocketId
                	$websocket = $redis->get($lookId);
                	if(!empty($websocket)){
	                	$info = array(
	                		'type'=>'map',
	                		'latitude'=>$data['latitude'],
	                		'longitude'=>$data['longitude'],
	                		'openid'=>$data['openid'],
	                	);
	                	$pushInfo = json_encode($info);
	                    $ser->push($websocket,$pushInfo);
                	}else{
                		break;
                	}
                    // break;
                }else{
                    break;
                }
                $i++;
            }
		}elseif($data['token'] == 'close'){
			/*
			 *关闭主播通道
			 *参数说明:
			 *uid=>主播的唯一标识
			 *主要操作:(1)删除主播房间列表(2)移除主播列表(3)通知用户该主播已下播
			*/
			// var_dump($data);
			//通知用户主播关闭直播
            $uid = $data['uid'];
			$i = 1;
			for(;;){
            	//获取观看者的openid
                $lookId = $redis->lGet('zhuboList'.$uid,$i);
                if(!empty($lookId)){
                	//通过观看者id获取websocketId
                	$websocket = $redis->get($lookId);
                	if(!empty($websocket)){
	                	$info = array(
	                		'type'=>'close',
	                		'data'=>'您观看的主播已经下播~'
	                	);
	                	$pushInfo = json_encode($info);
	                    $ser->push($websocket,$pushInfo);
                	}else{
                		break;
                	}
                    // break;
                }else{
                    break;
                }
                $i++;
            }
            //处理redis数据
            $redis->del('zhuboList'.$uid);
            $redis->del('roomtitle'.$uid);
            //删除主播列表中的主播
            $redis->lRemove('showHome',$uid,0);
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