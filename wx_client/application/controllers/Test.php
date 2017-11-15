<?php

/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 2017/10/18
 * Time: 19:31
 */
class TestController extends Yaf_Controller_Abstract{
    public function indexAction(){
        $appId = "wx462e524f53293c42";
        $redirect_url = urlencode("http://yafwx.litaotaoa.com/test/code");
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appId."&redirect_uri=".$redirect_url."&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
        header("Location:".$url);
    }
    public function codeAction(){
        $code = $this->getRequest()->getQuery('code');
        $appId = "wx462e524f53293c42";
        $secret = "c58e5db3e4a806f59a8188444c802b9c";
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appId."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
        $info = file_get_contents($url);
        var_dump($info);
    }
}