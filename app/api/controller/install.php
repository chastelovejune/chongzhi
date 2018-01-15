<?php
namespace api\controller;
class install{ 
    //安装程序视图
    function start(){
        //  if (!file_exists('./config.php')) jp(__URL__."index.php/api/install/start", 0);
        if (file_exists('./config.php')) exit('当前程序已安装,如需重新安装,请删除根目录下的config.php');
        display('index.html');
    }
    
    //开始安装
    function dbtall(){
        //检测是不是已经安装过了
        if (file_exists('./config.php')) exit('当前程序已安装,如需重新安装,请删除根目录下的config.php');
        
        //分别是主机，用户名，密码，数据库名，数据库编码
        $server = post('mysql_server');
        $mysql_username = post('mysql_username');
        $mysql_password = post('mysql_password');
        $mysql_dbname  = post('mysql_dbname');
        $db = new \DBManage( $server, $mysql_username, $mysql_password, $mysql_dbname, "utf8");
        
        //执行MYSQL文件
	    $db->restore ('./god.sql');
	    
	    //删除SQL文件
	    unlink("./god.sql");
	    
	    //创建数据库文件
	    $config = <<<Eof
	    <?php
//分布式数据库 配置数据库信息
function DB_CONFIG(\$TBNAME){
    return array(
        "host" => "{$server}",//数据库服务器
        "user" => "{$mysql_username}",//用户名
        "pass" => "{$mysql_password}",//密码
        "dbname" => "{$mysql_dbname}",//数据库名称
        "charset" => "utf8",// 数据库编码
        "prefix" => "god_",//该项不能更改
        "table" => \$TBNAME //该项不能更改
    );
}
//分布式服务器创建
?>
Eof;
	    file_put_contents("config.php", $config);
	    //显示安装
	    $adminUrl = __URL__ . "index.php/admin/index/login";
	    $agentUrl = __URL__ . "index.php/agent/index/login";
	    echo "恭喜您安装完成。<br>用户名：admin<br>密码：123456<br>访问：<a target='_blank' href='{$adminUrl}'>管理员后台</a> <a target='_blank' href='{$agentUrl}'>分销商后台</a>";
    }
    
}

?>