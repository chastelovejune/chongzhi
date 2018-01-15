<?php

//无限极递归分销商  从小到大
function did($obj){
            static $idment = '';
            $idment .= "{$obj['id']},";
            // 当自己不是一级代理
            if ($obj['agent_levelid'] != 0) {
                $obj = M("agent")->select("id={$obj['agent_levelid']}")[0];
                // 递归开始
                did($obj);
            }  
            return explode(",", trim($idment,","));
}

//计算总代理商的购卡价格
function rate($magentid,$price,$profits){
    // 取出总代理商的规则
    $rankdll = M("agentrank")->select("id={$magentid}")[0]['agentrank_rule']; // 取出规则
    $rank = explode("\n", $rankdll); // 分割成数组
    for ($i = 0; $i < count($rank); $i++) {
        $ranker = explode(",", $rank[$i]);
        // 大于我们这个规则里面的值
        if ($price > $ranker[0]) {
            // 计算购卡价格
             $rel = $price * ($ranker[1] + $profits);
        }
    }
    return $rel;
}
       
//数据库实例化
function M($TBNAME)
{
     return new mysql_server(DB_CONFIG($TBNAME));
}

/**
 * 邮件发送器
 * @param unknown $smtpserver 邮件服务器
 * @param unknown $smtpserverport 端口
 * @param unknown $smtpusermail 发件人
 * @param unknown $smtpemailto 收件人
 * @param unknown $smtpuser 邮箱账号
 * @param unknown $smtppass 邮箱密码
 * @param unknown $mailsubject 邮件标题
 * @param unknown $mailbody 邮件内容  
 * @param unknown $mailtype 邮件类型
 */
function mailSend($smtpemailto,$mailsubject,$mailbody){
    $de = M("settings")->select("id=1")[0];
    $smtp = new smtp($de['settings_smtpServer'],$de['settings_smtpPort'],true,$de['settings_smtpUser'],$de['settings_smtpPass']);
    return $smtp->sendmail($smtpemailto, $de['settings_smtpUser'], $de['settings_title'].$mailsubject, $mailbody, "HTML");
}

/**
 * 数据解析
 * @param unknown $dataset
 * @return multitype:
 */
function resdata($dataset){
    return explode(",", $dataset);
}

/**
 * 返回json数据
 *
 * @param unknown $code            
 * @param unknown $msg            
 * @param unknown $array            
 */
function json($code, $msg, $array)
{
    header('Content-type: application/json'); // json
    exit(json_encode(array(
        "code" => $code,
        "msg" => $msg,
        "data" => $array
    )));
}

/**
 * 检测手机号
 * @param unknown $mobile            
 * @return boolean
 */
function is_mobile($mobile)
{
    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
}

/**
 * 获取客户端IP
 * @return Ambigous <string, unknown>
 */
function getip() {
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    }
    elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    }
    elseif (getenv('HTTP_X_FORWARDED')) {
        $ip = getenv('HTTP_X_FORWARDED');
    }
    elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ip = getenv('HTTP_FORWARDED_FOR');

    }
    elseif (getenv('HTTP_FORWARDED')) {
        $ip = getenv('HTTP_FORWARDED');
    }
    else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * 邮箱正则
 * @param unknown $email
 * @return boolean
 */
function is_email($email){
    $reg='/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i';
    if(preg_match($reg,$email)){
        return true;
    }else{
        return false;
    }

}
/**
 * QQ正则
 * @param string $qq
 * @return boolean
 */
function is_qq($qq=''){
    return preg_match("/^[1-9][0-9]{4,10}$/", $qq) ? true : false;
}

/**
 * 检测字符串是否足够长度
 *
 * @param unknown $str            
 * @param unknown $length            
 * @return boolean
 */
function lengthExc($str, $length)
{
    if (preg_match("/^[\x7f-\xff]+$/", $str)) { // 兼容gb2312,utf-8
                                                // 中文处理
        if (strlen($str) >= $length * 3) {
            return true;
        } else {
            return false;
        }
    } else {
        // 其他处理
        if (strlen($str) >= $length) {
            return true;
        } else {
            return false;
        }
    }
}

//查詢地點
function getallopatry($ip){
    $data = mb_convert_encoding(file_get_contents("http://ip.ws.126.net/ipquery?ip=$ip"), 'UTF-8', 'UTF-8,GBK,GB2312,BIG5' ); ;
    preg_match("/lo=\"([\S\s]+?)\",/", $data, $d);
    preg_match("/lc=\"([\S\s]+?)\";/", $data, $b);
    return $d[1] . $b[1];
}

/**
 * SQL注入过滤字符串
 * @param unknown $value            
 * @return unknown|multitype:
 */
function addslashes_deep($value)
{
    if (empty($value)) {
        return $value;
    } else {
        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    }
}

/**
 * 加载视图文件
 * @param unknown $name           
 * view下的文件名
 * @param unknown $array
 *            模板变量传值 例子 array("变量名"=>"值","变量名2"=>"值2")
 */
function display($name, $array = null)
{
    $lan = new show($name, $array);
    $lan->display();
}

/**
 * 字符串安全过滤
 *
 * @param unknown $value            
 */
function filter(&$value)
{
    if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
        $value .= ' ';
    }
}

//過濾XSS
function SafeFilter (&$arr)
{
    $ra=Array('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/','/script/','/javascript/','/vbscript/','/expression/','/applet/','/meta/','/xml/','/blink/','/link/','/style/','/embed/','/object/','/frame/','/layer/','/title/','/bgsound/','/base/','/onload/','/onunload/','/onchange/','/onsubmit/','/onreset/','/onselect/','/onblur/','/onfocus/','/onabort/','/onkeydown/','/onkeypress/','/onkeyup/','/onclick/','/ondblclick/','/onmousedown/','/onmousemove/','/onmouseout/','/onmouseover/','/onmouseup/','/onunload/');
     
    if (is_array($arr))
    {
        foreach ($arr as $key => $value)
        {
            if (!is_array($value))
            {
                if (!get_magic_quotes_gpc())             //不对magic_quotes_gpc转义过的字符使用addslashes(),避免双重转义。
                {
                    $value  = addslashes($value);           //给单引号（'）、双引号（"）、反斜线（\）与 NUL（NULL 字符）加上反斜线转义
                }
                $value       = preg_replace($ra,'',$value);     //删除非打印字符，粗暴式过滤xss可疑字符串
                $arr[$key]     = htmlentities(strip_tags($value)); //去除 HTML 和 PHP 标记并转换为 HTML 实体
            }
            else
            {
                SafeFilter($arr[$key]);
            }
        }
    }
}


/**
 * URL跳转
 * @param unknown $url
 * @param unknown $time
 */
function jp($url,$time,$msg='加载中..'){
    echo <<<Eof
<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
        <meta name="format-detection" content="telephone=no">
        <meta charset="UTF-8">
        <meta http-equiv='Refresh' content='$time; url=$url' />
        <style>*{font-family:'微软雅黑';font-size:10px;}</style>
        <title>$msg..</title> 
        <!-- CSS -->
                 
    </head>
    <body>
                   <p>{$msg}</p>
                   <p>{$time}秒自动跳转..</p>
                   <p>等不及了,<a href="$url">点我跳转</a></p>
    </body>
</html>
Eof;
die;
}

/**
 * 获取当前页面的URL
 * @return string
 */
function geturl()
{
    $pageURL = 'http';

    if ($_SERVER["HTTPS"] == "on")
    {
        $pageURL .= "s";
    }
    $pageURL .= "://";

    if ($_SERVER["SERVER_PORT"] != "80")
    {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    }
    else
    {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

/**
 * 随机大小写+数字
 * @param unknown $length
 * @return string
 */
function get_rand($length){
    $str = array_merge(range(0,9),range('a','z'),range('A','Z'));
    shuffle($str);
    $str = implode('',array_slice($str,0,$length));
    return $str;
}

//过滤XSS
function string_remove_xss($html) {
    preg_match_all("/\<([^\<]+)\>/is", $html, $ms);
 
    $searchs[] = '<';
    $replaces[] = '&lt;';
    $searchs[] = '>';
    $replaces[] = '&gt;';
 
    if ($ms[1]) {
        $allowtags = 'img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote';
        $ms[1] = array_unique($ms[1]);
        foreach ($ms[1] as $value) {
            $searchs[] = "&lt;".$value."&gt;";
 
            $value = str_replace('&amp;', '_uch_tmp_str_', $value);
            $value = string_htmlspecialchars($value);
            $value = str_replace('_uch_tmp_str_', '&amp;', $value);
 
            $value = str_replace(array('\\', '/*'), array('.', '/.'), $value);
            $skipkeys = array('onabort','onactivate','onafterprint','onafterupdate','onbeforeactivate','onbeforecopy','onbeforecut','onbeforedeactivate',
                    'onbeforeeditfocus','onbeforepaste','onbeforeprint','onbeforeunload','onbeforeupdate','onblur','onbounce','oncellchange','onchange',
                    'onclick','oncontextmenu','oncontrolselect','oncopy','oncut','ondataavailable','ondatasetchanged','ondatasetcomplete','ondblclick',
                    'ondeactivate','ondrag','ondragend','ondragenter','ondragleave','ondragover','ondragstart','ondrop','onerror','onerrorupdate',
                    'onfilterchange','onfinish','onfocus','onfocusin','onfocusout','onhelp','onkeydown','onkeypress','onkeyup','onlayoutcomplete',
                    'onload','onlosecapture','onmousedown','onmouseenter','onmouseleave','onmousemove','onmouseout','onmouseover','onmouseup','onmousewheel',
                    'onmove','onmoveend','onmovestart','onpaste','onpropertychange','onreadystatechange','onreset','onresize','onresizeend','onresizestart',
                    'onrowenter','onrowexit','onrowsdelete','onrowsinserted','onscroll','onselect','onselectionchange','onselectstart','onstart','onstop',
                    'onsubmit','onunload','javascript','script','eval','behaviour','expression','style','class');
            $skipstr = implode('|', $skipkeys);
            $value = preg_replace(array("/($skipstr)/i"), '.', $value);
            if (!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value)) {
                $value = '';
            }
            $replaces[] = empty($value) ? '' : "<" . str_replace('&quot;', '"', $value) . ">";
        }
    }
    $html = str_replace($searchs, $replaces, $html);
 
    return $html;
}
 
function string_htmlspecialchars($string, $flags = null) {
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = string_htmlspecialchars($val, $flags);
        }
    } else {
        if ($flags === null) {
            $string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
            if (strpos($string, '&amp;#') !== false) {
                $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
            }
        } else {
            if (PHP_VERSION < '5.4.0') {
                $string = htmlspecialchars($string, $flags);
            } else {
                if (!defined('CHARSET') || (strtolower(CHARSET) == 'utf-8')) {
                    $charset = 'UTF-8';
                } else {
                    $charset = 'ISO-8859-1';
                }
                $string = htmlspecialchars($string, $flags, $charset);
            }
        }
    }
 
    return $string;
}
//全自动自动翻页 - v1.2 升级版 作者:心剑
function autopage($count, $page, $num)
{
    if ($count!=1){
    $url = preg_replace("/\/page\/[\s\S]+/", "", geturl());//正则处理当前页面
    $num = min($count, $num); // 处理显示的页码数大于总页数的情况
    if ($page > $count || $page < 1)
        return; // 处理非法页号的情况
    $end = $page + floor($num / 2) <= $count ? $page + floor($num / 2) : $count; // 计算结束页号
    $start = $end - $num + 1; // 计算开始页号
    if ($start < 1) { // 处理开始页号小于1的情况
        $end -= $start - 1;
        $start = 1;
    }
    $topPage = $page-1;//上一页
    $downPage = $page+1;//下一页
    echo "<a href='{$url}/page/{$topPage}' class='layui-laypage-prev btn btn-white btn-sm'>上一页</a>"; //上一页HTML代码
    if ($page == $count || $page>1 && $page<$count){
        echo "<a href='{$url}/page/1' class='layui-laypage-last btn btn-white btn-sm'>首页</a>";//首页HTML代码
    }
    for ($i = $start; $i <= $end; $i ++) { // 输出分页条，请自行添加链接样式
        if ($i == $page){
            //当前页HTML代码
            echo "<span class='layui-laypage-curr btn btn-white btn-sm'><em class='layui-laypage-em'></em><em>$i</em></span>";
        }else{
            //其他页HTML代码
            echo "<a class='btn btn-white btn-sm' href='{$url}/page/$i'>$i</a>";
        }
    }
    if ($page == 1 || $page>1 && $page<$count){
        //最后一页HTML代码
        echo "<a href='{$url}/page/{$count}' class='layui-laypage-last btn btn-white btn-sm'>末页</a>";
    }
    //下一页HTML代码
    echo "<a href='{$url}/page/{$downPage}' class='layui-laypage-next btn btn-white btn-sm'>下一页</a>";
    }
}
//获取get
function get($val){
    return string_remove_xss($_GET[$val]);
}
//获取post
function post($val){
    return string_remove_xss($_POST[$val]);
}
//获取cookie
function cookie($val){
    return string_remove_xss($_COOKIE[$val]);
}
//url传值
function Sendurl(){
    return str_replace("/", "_", geturl());
}
//url接受
function Turl($Sendurl){
    return str_replace("_", "/", $Sendurl);
}
//強制下載文件導入文本
function DownText($filename,$content){
    Header("Content-type:application/octet-stream ");
    Header("Accept-Ranges:bytes ");
    header("Content-Disposition:attachment;filename={$filename}");
    header("Expires:0 ");
    header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
    header("Pragma:public");
    echo $content;
}

//递归商品分类
function shopTree($items,$id='id',$pid='shoprt_pid',$son = 'children'){
	$tree = array(); //格式化的树
	$tmpMap = array();  //临时扁平数据
	
	foreach ($items as $item) {
		$tmpMap[$item[$id]] = $item;
	}
	
	foreach ($items as $item) {
		if (isset($tmpMap[$item[$pid]])) {
			$tmpMap[$item[$pid]][$son][] = &$tmpMap[$item[$id]];
		} else {
			$tree[] = &$tmpMap[$item[$id]];
		}
	}
	return $tree;
}

// 验证用户名是否正确 用户名由6-24位字母、数字组成，首位不能是数字
function is_username($username=''){
    return preg_match("/^[a-zA-Z]{1}[0-9a-zA-Z]{4,22}$/", $username) ? true : false;
}
// 验证密码是否正确  密码由6-16位大小写字母、数字和下划线组成
function is_password($password=''){
    return preg_match("/^[0-9a-zA-Z_!@#%$]{6,16}$/", $password) ? true : false;
}
//发送手机短信
function mobilesend($mobile,$content){
    $Moog = M("settings")->select("id=1")[0];
    $url = str_replace("[content]", $content, str_replace("[mobile]", $mobile, str_replace("[pus]", "?", $Moog['settings_mobile'])));
    return file_get_contents($url);
}

//自动识别账号类型
function userkey($user){
    //判断是否为个性账号
    if (is_username($user)){
        $key =  'user_username';
    }
    //判断是否为邮箱号码
    if (is_email($user)){
        $key =  'user_email';
    }
/*     //判断是否为QQ号码
    if (is_qq($user)){
        $key =  'user_qqnum';
    } */
    //判断是否为手机号码
    if (is_mobile($user)){
        $key =  'user_mobile';
    }
    return $key;
}

//前端博客  type 1分类 2热门和推荐
function _w_url($url,$id,$type,$moog){
    
    //分类伪静态
    if ($type == 1){
        //开启伪静态
        if ($moog == 1){
            return $url . "c{$id}.html";
        }else{
            return $url . "index.php/writings/index/home/sort/{$id}";
        }
    }
    
    //热门推荐
    if ($type == 2){
        if ($moog == 1){
            return $url . "f{$id}.html";
        }else{
            return $url . "index.php/writings/index/home/frozen/{$id}";
        }
    }
    
    //文章查看
    if ($type == 3) {
        if ($moog == 1){
            return $url . "w{$id}.html";
        }else{
            return $url . "index.php/writings/index/view/id/{$id}";
        }
    }
    
}

//提取内容中的第一张图片
function subimgurl($str){
    preg_match ("<img.*src=[\"](.*?)[\"].*?>",$str,$c);
    if (empty($c[1])){
        return 0;
    }else{
        return $c[1];
    }
}


?>