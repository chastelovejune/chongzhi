	    <?php
//分布式数据库 配置数据库信息
function DB_CONFIG($TBNAME){
    return array(
        "host" => "127.0.0.1",//数据库服务器
        "user" => "root",//用户名
        "pass" => "",//密码
//        "dbname" => "laliza_cn",//数据库名称
        "dbname" => "chongzhi",//数据库名称
        "charset" => "utf8",// 数据库编码
        "prefix" => "god_",//该项不能更改
        "table" => $TBNAME //该项不能更改
    );
}
//分布式服务器创建
?>