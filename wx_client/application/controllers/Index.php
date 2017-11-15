<?php
/**
 *  微信的入口文件
 */
use text\ResponseTextModel;
class IndexController extends Yaf_Controller_Abstract {
    //中央调度器(入口)
	public function indexAction() {
            $echostr = $this->getRequest()->getQuery('echostr');
	        //判断是否为验证
	        if(isset($echostr)){
                echo $echostr;
            }else{
                //调度业务处理
                $postStr = file_get_contents('PHP://input');
                //将xml数据转化为对象
                $xml_obj = simplexml_load_string( $postStr );
                //分流  调度
                switch ($xml_obj->MsgType){
                    //文本消息
                    case 'text':
                        ResponseTextModel::index($xml_obj);
                        break;
                }
            }
	}
	public function liveAction(){
        $token = token();
//        echo $token;
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".$token;
        $info = file_get_contents($url);
        $menuInfo = array (
            'button' =>
                array (
                    0 =>
                        array (
                            'type' => 'click',
                            'name' => '天气',
                            'key' => '北京',
                        ),
                    1 =>
                        array (
                            'name' => '网址',
                            'sub_button' =>
                                array (
                                    0 =>
                                        array (
                                            'type' => 'view',
                                            'name' => '腾讯官网',
                                            'url' => 'http://www.soso.com/',
                                        ),
                                    1 =>
                                        array (
                                            'type' => 'click',
                                            'name' => 'LOL',
                                            'key' => 'zan',
                                        ),
                                ),
                        ),
                ),
        );
        $menuInfo = json_encode($menuInfo,JSON_UNESCAPED_UNICODE);
        echo $menuInfo;
//        var_dump($info);
//        echo $token;
//        $this->getView()->display('index/live.phtml');
    }
}
