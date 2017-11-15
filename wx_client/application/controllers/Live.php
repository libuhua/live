<?php

/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 2017/10/19
 * Time: 12:03
 */
class LiveController extends Yaf_Controller_Abstract
{
    //获取用户信息
    public function listAction(){
        $appId = "wx462e524f53293c42";
        $redirect_url = urlencode("http://yafwx.litaotaoa.com/live/code");
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appId."&redirect_uri=".$redirect_url."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
        header("Location:".$url);
    }
    public  function videoAction(){
        $redis = new Redis();
        $redis->connect('47.94.252.238',6379);
        //资源整合
        //获取所有主播id
        $allUid = $redis->sGetMembers('videouid');
        foreach ($allUid as $v){
            //通过用户id获取视频Id
            $key = $v.'voide';
            $video[] = $redis->lRange($key,0,-1);
        }
        $data = array();
        foreach ($video as $key=>$value){
            if(is_array($value)){
                foreach ($value as $k=>$v){
                    $data[] = explode(',',$v);
                }
            }else{
                $data[] = explode(',',$value);
            }
        }
        $this->getView()->assign('data',$data);
        $this->getView()->display('live/video.phtml');
    }
    public function codeAction(){
        $code = $this->getRequest()->getQuery('code');
        $appId = "wx462e524f53293c42";
        $secret = "c58e5db3e4a806f59a8188444c802b9c";
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appId."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
        $info = file_get_contents($url);
        //获取用户信息
        $info = json_decode($info,true);
        $access_token = $info['access_token'];
        $openid = $info['openid'];
//        var_dump($access_token);
        $urls = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $data = file_get_contents($urls);
        //将用户数据转化为数组
        $data = json_decode($data,true);
//        var_dump($data);
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        //将当前观看主播的人压入redis
        $redis->hSet($data['openid'],'nickname',$data['nickname']);
        //将当前用户存入cookie
        setcookie('openid',$data['openid']);
        //取出正在直播的人
        $list = $redis->lRange('showHome',0,-1);
//        var_dump($list);
        foreach ($list as $key=>$value){
//            echo $value;
            $lists[$key]['roomtitle'] = $redis->get("roomtitle".$value);
            $lists[$key]['uid'] = $value;
        }
        if(empty($lists)){
            $lists = array();
        }
        $this->getView()->assign('list',$lists);

        $this->getView()->display('live/list.phtml');
    }
    public function personAction(){
        $this->getView()->display('live/person.phtml');
    }
    public function liveAction(){
        $look = $this->getRequest()->getQuery("look");
        //处理jssdk需要的参数
        $jssdk = $this->jsSdk($look);

        $openid = $this->getRequest()->getCookie('openid');
        //当前用户的openid
        $this->getView()->assign('openid',$openid);
        //用户要观看的主播Id
        $this->getView()->assign('look',$look);
        //jssdk参数
        $this->getView()->assign('jssdk',$jssdk);

        $this->getView()->display('live/live.phtml');
    }
    public function jsSdk($look){
        $jsapiTicket = jsapi_ticket();
        $timestamp = time();
        $nonceStr = nonceStr();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=".$jsapiTicket."&noncestr=".$nonceStr."&timestamp=".$timestamp."&url=".$url;
        $signature = sha1( $string );
        $response = array(
            'timestamp'=>$timestamp,
            'nonceStr'=>$nonceStr,
            'signature'=>$signature,
        );
        return $response;
    }
}