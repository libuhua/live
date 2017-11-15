<?php
//定义项目路径
define('APPLICATION_PATH', dirname(__FILE__));
//实例化框架
$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");
//运行bootstrop
$application->bootstrap()->run();
?>
