<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 2017/10/18
 * Time: 14:33
 */

namespace text;
class ResponseTextModel
{
    //回复文本消息
    public static function index($xml_obj){
            switch ($xml_obj->Content){
                case '你好':
                        $content = "你也好啊!";
                        echo responseText($xml_obj,$content);
                    break;
                default :
                        $content = "你输入的信息有误";
                        echo responseText($xml_obj,$content);
                    break;
            }


    }
}
