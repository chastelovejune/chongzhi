<?php
namespace CONFIG\GOD;
class IN
{
    static function UI($header_charsets)
    {
        ob_start();
        header("Content-type: text/html; charset={$header_charsets}");
        @include 'config.php';
        require_once 'app/config.php';
        date_default_timezone_set('PRC');
        session_start();
        @$path = explode('/', ltrim($_SERVER['PATH_INFO'], "/")); 
        @$Moduel = $path[0] ? $path[0] : $config['bind']['moduel']; 
        @$Controller = $path[1] ? $path[1] : $config['bind']['controller']; 
        @$DoAction = $path[2] ? $path[2] : $config['bind']['action'];
        @define(__ACTION__, $DoAction);
        for ($i = 3; $i < count($path); $i ++) {
            if ($i % 2 != 0) {
                @$_GET["$path[$i]"] = $path[$i + 1];
            }
        }
        self::directory($config);
        self::_global($config);
        self::requireLib($config);
        self::public_path($Moduel);
        self::sql_int($config);
        self::sql_check($config);
        require_once "app/{$Moduel}/controller/{$Controller}.php";
        $namespace = $Moduel . "\controller\\$Controller"; //
        $object = new $namespace();
        $object->$DoAction();

    }
    static function _global($config){
        if ($config['error_reporting']) error_reporting(0);
        if (!empty($config['close'])) exit($config['close']);
    }
    static function public_path($Moduel){
        define(__PUBLIC__, str_replace('index.php', '', $_SERVER['SCRIPT_NAME']). "app/{$Moduel}/view/public/");
        define(__TEMP__, "app/{$Moduel}/view/template/");
        $prot = $_SERVER['SERVER_PORT'] != 80 ? ":". $_SERVER['SERVER_PORT'] : '';
        define(__URL__, "http://" . $_SERVER['SERVER_NAME'].$prot.str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));
        define(__PLUG__, str_replace('index.php', '', $_SERVER['SCRIPT_NAME']). "plug/");
    }
    static function sql_int($config){
        define(SQL_HOST, $config['sql_int']['host']);
        define(SQL_DB_NAME, $config['sql_int']['dbname']);
        define(SQL_USER, $config['sql_int']['user']);
        define(SQL_PASS, $config['sql_int']['pass']);
        define(SQL_CHARSET, $config['sql_int']['charset']);
        define(SQL_PREFIX, $config['sql_int']['prefix']);
    }
    static function directory($config){
        for ($i=0;$i<count($config['directory']);$i++){
            set_include_path(get_include_path() . PATH_SEPARATOR . "{$config['directory'][$i]}");
        }
    }
    static function sql_check($config){
        if (!get_magic_quotes_gpc() && $config['sql_check']==true)
        {
            if (!empty($_GET))
            {
                $_GET  = addslashes_deep($_GET);
            }
            if (!empty($_POST))
            {
                $_POST = addslashes_deep($_POST);
            }
            $_COOKIE = addslashes_deep($_COOKIE);
            $_REQUEST = addslashes_deep($_REQUEST);
        }
    }
    static function requireLib($config){

        for ($i=0;$i<count($config['lib']);$i++){
            require_once ("{$config['lib'][$i]}");
        }

    }
}
?>