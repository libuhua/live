@extends('layouts.app')

@section('content')
	<script src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script>
    <div style="width: 80%;margin-left: 10%">
    	<div>
    		<video autoplay id="sourcevid" style="width:320;height:240px"></video>

            <!-- <video autoplay id="sourcevids" style="width:320;height:240px"></video> -->

		    <canvas id="output" style="display:none"></canvas>
            <audio src=""></audio>
            <!-- <input type="text" name="" id="buffer"> -->
		</div>
		直播间标题： <input type="text" name="" id="roomtitle">
        <input type="hidden" name="" id="uid" value="{{ $uid }}">
		<br>
		<br>
		<div>
			<div style="float: left;">
				<button class="btn btn-info stop" id="begin">开启直播</button>
			</div>
			<div style="float: left;margin-left: 30px;">
				<a class="btn btn-info stop" href="javascript:void(0)" title="注:录制时间将会在20分钟之内" id="save">开启录制</a>
			</div>
		</div>
    </div>
    <script type="text/javascript">
        var socket = new WebSocket("ws://47.94.222.95:9502");
        var savelive = new WebSocket("ws://47.94.252.238:9502");

        var back = document.getElementById('output');
        var backcontext = back.getContext('2d');
        var video = document.getElementsByTagName('video')[0];
        var video2 = document.getElementsByTagName('video')[1];

    	$(document).on("click","#begin",function(){
    		roomtitle = $("#roomtitle").val();

    		if($(this).hasClass("stop")){
    			if(roomtitle == ""){
    				alert("直播间标题不能为空");
	    		}else{
                    $(this).html("停止直播");
                    $(this).removeClass("stop");
	    		}	
    		}else{
                if($("#save").hasClass("stop")){
        			alert("已停止当前直播");
        			$(this).html("开始直播");
        			$(this).addClass("stop");
                    //连接后台进行关闭操作
                    uid = $("#uid").val();
                    //关闭操作
                    socket.send('{"token":"close","uid":"'+uid+'"}');
                    
                }else{
                    alert("请先停止视频录制!然后下播");
                }
    		}
    	});

        $(document).on("click","#save",function(){

            if($(this).hasClass("stop")){
                    if($("#begin").hasClass("stop")){
                        alert("请先开启直播!");
                    }else{
                        $(this).html("暂停录制");
                        $(this).removeClass("stop");
                    }
            }else{
                alert("已停止当前录制");
                //如果主播点击了停止录制则进行保存操作  进行设置视频名称的操作(进行操作=>通知后台进行视频生成)
                filename = prompt("请输入录制视频的名称!将会发布到平台上~可前往往期视频查看!");
                // alert(filename);
                savelive.send('{"token":"create","uid":"'+uid+'","filename":"'+filename+'"}');

                $(this).html("开始录制");
                $(this).addClass("stop");
            }
        });
        
        //视频流
        var success = function(stream){
            // console.log(stream);
            video.src = window.URL.createObjectURL(stream);
            //音频流处理
            audioCtx = new (window.AudioContext || window.webkitAudioContext);
            let source = audioCtx.createMediaStreamSource(stream);
            recorder = audioCtx.createScriptProcessor(2048, 1, 1);
            source.connect(recorder);
            recorder.connect(audioCtx.destination);
            recorder.onaudioprocess = function(ev){
                let inputBuffer = ev.inputBuffer.getChannelData(0);
                info = inputBuffer.buffer;

                // socket.send(info);
                // data = new Float32Array(info);
                window.yuyin = arrayBufferToBase64(info);
                // console.log(data);
//                 socket.send(info);
                // // $("#buffer").val(data);
                // socket.send('{"token":"zhubo","uid":"'+data+'"}');
                // console.log(data);
            };
        }
        //转码
        function arrayBufferToBase64( buffer ) {
                    var binary = '';
                    var bytes = new Uint8Array( buffer );
                    var len = bytes.byteLength;
                    for (var i = 0; i < len; i++) {
                        binary += String.fromCharCode( bytes[ i ] );
                    }
                    return window.btoa( binary );
                }
        //音频流处理
        var audio = function(stream){
            // 
            // audioCtx = new (window.AudioContext || window.webkitAudioContext);
             
            // source = audioCtx.createMediaStreamSource(stream);

            // // source = window.URL.createObjectURL(stream);
            // source.connect(audioCtx.destination);
        }
        var error = function(){
            // console.log();
            alert("您的浏览器可能不支持当前直播环境!请更换火狐浏览器进行直播");
        }

        socket.onopen = function(){
            draw();
        }

        var draw = function(){
            try{
                backcontext.drawImage(video,0,0, back.width, back.height);
            }catch(e){
                if (e.name == "NS_ERROR_NOT_AVAILABLE") {
                    return setTimeout(draw, 100);
                } else {
                    throw e;
                }
            }
            if(video.src){
            	if(!$("#begin").hasClass("stop")){
                    audio = window.yuyin;
                    uid = $("#uid").val();
                    roomtitle = $("#roomtitle").val();
                    //主播标题
                	socket.send('{"token":"zhubo","uid":"'+uid+'","roomtitle":"'+roomtitle+'","audio":"'+audio+'","camera":"'+back.toDataURL("image/jpeg", 0.5)+'"}');
            	}else{
            		// console.log("暂未直播");
            	}
                //如果主播开始录制则触发此功能
                if(!$("#save").hasClass("stop")){
                    savelive.send('{"token":"voide","uid":"'+uid+'","camera":"'+back.toDataURL("image/jpeg", 0.5)+'"}');
                }else{
                    // console.log("暂未录制");
                }
            }
            setTimeout(draw, 100);
        }
        navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia ||
        navigator.mozGetUserMedia || navigator.msGetUserMedia;
        navigator.getUserMedia({video:true, audio:true}, success, error);
    </script>
@endsection