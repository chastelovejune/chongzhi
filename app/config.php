<?php 
use index\controller\index;
// 配置文件
$config = array(
    //配置需要自动加载的目录路径
    "directory" => array(
        "core/lib/", //函数库
        "core/link/", //class库
        "app/api/model/", //模型(用户自定义)
    ),
    //自动加载文件，文件不可重复
    "lib" => array(
        "db.class.php",
        "mysql.class.php", //数据库操作类 该文件顺序不可改变
        "show.class.php", //模板引擎加载
        "functions.php", //公共函数
        "userModel.php", //user模型(例子,用户可自定义)
        "mail.class.php",//邮件运行库
        "algorithm.php", //算法库
    ),
    "error_reporting" => true, //是否关闭错误提示 false 不关闭   true 关闭
    "close" => false,  //是否停止站点运行，false 否， 定义一个提示语句为 停止
    //开启防御SQL注入功能
    "sql_check" => true, //true 开启  false关闭
    //绑定默认加载
    "bind" => array(
        "moduel" => "writings",  //默认模块
        "controller" => "index",  //默认控制器
        "action" => "home"  //默认方法
    )
);
    ?>