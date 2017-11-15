<?php
    //日志记录器
    //数据 data
    function log_write($data,$logFileName){
        //定义文件名规则
        $logFilePath = './log/'.date('Ymd').'/'.$logFileName.'.log';
        //定义文件目录规则
        $logDirPath = './log/'.date('Ymd').'/';
        //判断目录是否存在 不存在则创建目录
        if(!is_dir($logDirPath)) {
            mkdir($logDirPath, 777, true);
        }
        //分隔符
        $str = "===================================================================";
        $data = "\n".$str."\ntime:".date('Y-m-d H:i:s')."\n".$data;
        //FILE_APPEND 文件以追加的方式存储
        file_put_contents($logFilePath,print_r($data,true),FILE_APPEND);
    }
    //回复文本消息模板
    //$conent：要回复的内容
    function responseText($xml_obj,$content){
        $xml_str = '<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[%s]]></Content></xml>';

        $reponse_xml = sprintf($xml_str,$xml_obj->FromUserName,$xml_obj->ToUserName,time(),$content);
        return $reponse_xml;
    }
    //获取access_token
    function token(){
        //开启session
        session_start();
        //appKey and appSecret
        $appKey    = 'wx462e524f53293c42';
        $appSecret = 'c58e5db3e4a806f59a8188444c802b9c';
        //拼接url
        $url       = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appKey.'&secret='.$appSecret;
        if(time()-$_SESSION['token_time'] <= 7100)
        {
            return $_SESSION['token'];
        }
        $info = file_get_contents($url);
        //获取信息
        $accessToken = json_decode($info);
        $_SESSION['token'] = $accessToken->access_token;
        $_SESSION['token_time'] = time();
        return $_SESSION['token'];
    }
    function nonceStr(){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $nonceStr = "";
        for ($i = 0; $i < 16; $i++) {
            $nonceStr .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $nonceStr;
    }
    function jsapi_ticket(){
        $accessToken = token();
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
        if(time()-$_SESSION['ticket_time'] <= 7100)
        {
            return $_SESSION['ticket'];
        }
        $info = file_get_contents($url);
        //获取信息
        $accessToken = json_decode($info);
        $_SESSION['ticket'] = $accessToken->ticket;
        $_SESSION['ticket_time'] = time();
        return $_SESSION['ticket'];
    }