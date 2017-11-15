<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 2017/10/19
 * Time: 12:03
 */
class RedissController extends Yaf_Controller_Abstract{
    public function getAction(){
        $openid = $this->getRequest()->getQuery('openid');
//        echo $openid;
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $nickname = $redis->hGet($openid,'nickname');
        echo $nickname;
    }
}