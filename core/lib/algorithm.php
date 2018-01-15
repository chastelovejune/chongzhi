<?php
//程序计算库
//作者：心剑
//加密1   encrypt(要加密的字符串, 密钥, 二次计算密钥)
function encrypt($txt, $key='', $ikey='')
{
    if (empty($txt))
        return $txt;
    if (empty($key))
        $key = md5(MD5_KEY);
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
    $nh1 = rand(0, 64);
    $nh2 = rand(0, 64);
    $nh3 = rand(0, 64);
    $ch1 = $chars{$nh1};
    $ch2 = $chars{$nh2};
    $ch3 = $chars{$nh3};
    $nhnum = $nh1 + $nh2 + $nh3;
    $knum = 0;
    $i = 0;
    while (isset($key{$i}))
        $knum += ord($key{$i ++});
    $mdKey = substr(md5(md5(md5($key . $ch1) . $ch2 . $ikey) . $ch3), $nhnum % 8, $knum % 8 + 16);

    $txt = base64_encode(time() . '_' . $txt);
    $txt = str_replace(array(
        '+',
        '/',
        '='
    ), array(
        '-',
        '_',
        '.'
    ), $txt);
    $tmp = '';
    $j = 0;
    $k = 0;
    $tlen = strlen($txt);
    $klen = strlen($mdKey);
    for ($i = 0; $i < $tlen; $i ++) {
        $k = $k == $klen ? 0 : $k;
        $j = ($nhnum + strpos($chars, $txt{$i}) + ord($mdKey{$k ++})) % 64;
        $tmp .= $chars{$j};
    }
    $tmplen = strlen($tmp);
    $tmp = substr_replace($tmp, $ch3, $nh2 % ++ $tmplen, 0);
    $tmp = substr_replace($tmp, $ch2, $nh1 % ++ $tmplen, 0);
    $tmp = substr_replace($tmp, $ch1, $knum % ++ $tmplen, 0);
    return $tmp;
}

//解密1  decrypt(要加密的字符串, 密钥, 二次计算密钥)
function decrypt($txt, $key='', $ikey='')
{
    if (empty($txt))
        return $txt;
    if (empty($key))
        $key = md5(MD5_KEY);
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
    $knum = 0;
    $i = 0;
    $ttl = 0;
    $tlen = @strlen($txt);
    while (isset($key{$i}))
        $knum += ord($key{$i ++});
    $ch1 = @$txt{$knum % $tlen};
    $nh1 = strpos($chars, $ch1);
    $txt = @substr_replace($txt, '', $knum % $tlen --, 1);

    $ch2 = @$txt{$nh1 % $tlen};
    $nh2 = @strpos($chars, $ch2);
    $txt = @substr_replace($txt, '', $nh1 % $tlen --, 1);

    $ch3 = @$txt{$nh2 % $tlen};
    $nh3 = @strpos($chars, $ch3);
    $txt = @substr_replace($txt, '', $nh2 % $tlen --, 1);

    $nhnum = $nh1 + $nh2 + $nh3;

    $mdKey = substr(md5(md5(md5($key . $ch1) . $ch2 . $ikey) . $ch3), $nhnum % 8, $knum % 8 + 16);

    $tmp = '';
    $j = 0;
    $k = 0;
    $tlen = @strlen($txt);
    $klen = @strlen($mdKey);

    for ($i = 0; $i < $tlen; $i ++) {
        $k = $k == $klen ? 0 : $k;
        $j = strpos($chars, $txt{$i}) - $nhnum - ord($mdKey{$k ++});
        while ($j < 0)
            $j += 64;
        $tmp .= $chars{$j};
    }

    $tmp = str_replace(array(
        '-',
        '_',
        '.'
    ), array(
        '+',
        '/',
        '='
    ), $tmp);
    $tmp = trim(base64_decode($tmp));
    if (preg_match("/\d{10}_/s", substr($tmp, 0, 11))) {
        if ($ttl > 0 && (time() - substr($tmp, 0, 11) > $ttl)) {
            $tmp = null;
        } else {
            $tmp = substr($tmp, 11);
        }
    }
    return $tmp;
}


//Sina App_Key  短网址算法
define('SINA_APPKEY', '31641035');
function curlQuery($url) {
    //设置附加HTTP头
    $addHead = array(
        "Content-type: application/json"
    );
    //初始化curl，当然，你也可以用fsockopen代替
    $curl_obj = curl_init();
    //设置网址
    curl_setopt($curl_obj, CURLOPT_URL, $url);
    //附加Head内容
    curl_setopt($curl_obj, CURLOPT_HTTPHEADER, $addHead);
    //是否输出返回头信息
    curl_setopt($curl_obj, CURLOPT_HEADER, 0);
    //将curl_exec的结果返回
    curl_setopt($curl_obj, CURLOPT_RETURNTRANSFER, 1);
    //设置超时时间
    curl_setopt($curl_obj, CURLOPT_TIMEOUT, 15);
    //执行
    $result = curl_exec($curl_obj);
    //关闭curl回话
    curl_close($curl_obj);
    return $result;
}
//简单处理下url，sina对于没有协议(http://)开头的和不规范的地址会返回错误
function filterUrl($url = '') {
    $url = trim(strtolower($url));
    $url = trim(preg_replace('/^http:\//', '', $url));
    if ($url == '')
        return false;
    else
        return urlencode('http://' . $url);
}

//根据长网址获取短网址
function sinaShortenUrl($long_url) {
    
    //拼接请求地址，此地址你可以在官方的文档中查看到
    $url = 'http://api.t.sina.com.cn/short_url/shorten.json?source=' . SINA_APPKEY . '&url_long=' . filterUrl($long_url);
    //获取请求结果
    $result = curlQuery($url);
    //下面这行注释用于调试，
    //print_r($result);exit();
    //解析json
    $json = json_decode($result);
    //异常情况返回false
    if (isset($json->error) || !isset($json[0]->url_short) || $json[0]->url_short == '')
        return false;
    else
        return $json[0]->url_short;
}
//根据短网址获取长网址，此函数重用了不少sinaShortenUrl中的代码，以方便你阅读对比，你可以自行合并两个函数
function sinaExpandUrl($short_url) {
    //拼接请求地址，此地址你可以在官方的文档中查看到
    $url = 'http://api.t.sina.com.cn/short_url/expand.json?source=' . SINA_APPKEY . '&url_short=' . $short_url;
    //获取请求结果
    $result = curlQuery($url);
    //下面这行注释用于调试
    //print_r($result);exit();
    //解析json
    $json = json_decode($result);
    //异常情况返回false
    if (isset($json->error) || !isset($json[0]->url_long) || $json[0]->url_long == '')
        return false;
    else
        return $json[0]->url_long;
}


function Imgcode($length,$im_x,$im_y){
    header("Content-type: image/PNG");
    //先生成验证码文字内容
    $str="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $text="";
    for($i=0;$i<$length;$i++){
        $num[$i]=rand(0,25);
        $text.= $str[$num[$i]];
    }
    $text = strtolower($text);
    setcookie("_imgcode", encrypt($text), time() + 270, "/"); // 设置用户cookie 验证码 270秒后过期
    //生成验证码图片
    $im = imagecreatetruecolor($im_x,$im_y);
    $text_c = ImageColorAllocate($im, mt_rand(0,100),mt_rand(0,100),mt_rand(0,100));
    $tmpC0=mt_rand(100,255);
    $tmpC1=mt_rand(100,255);
    $tmpC2=mt_rand(100,255);
    $buttum_c = ImageColorAllocate($im,$tmpC0,$tmpC1,$tmpC2);
    imagefill($im, 16, 13, $buttum_c);
    $font = dirname(__FILE__) . DIRECTORY_SEPARATOR . 't1.ttf';
    for ($i=0;$i<strlen($text);$i++)
    {
        $tmp =substr($text,$i,1);
        $array = array(-1,1);
        $p = array_rand($array);
        $an = $array[$p]*mt_rand(1,10);//角度
        $size = 28;
        imagettftext($im, $size, $an, 15+$i*$size, 35, $text_c, $font, $tmp);
    }


    $distortion_im = imagecreatetruecolor ($im_x, $im_y);

    imagefill($distortion_im, 16, 13, $buttum_c);
    for ( $i=0; $i<$im_x; $i++) {
        for ( $j=0; $j<$im_y; $j++) {
            $rgb = imagecolorat($im, $i , $j);
            if( (int)($i+20+sin($j/$im_y*2*M_PI)*10) <= imagesx($distortion_im)&& (int)($i+20+sin($j/$im_y*2*M_PI)*10) >=0 ) {
                imagesetpixel ($distortion_im, (int)($i+10+sin($j/$im_y*2*M_PI-M_PI*0.1)*4) , $j , $rgb);
            }
        }
    }
    //加入干扰象素;
    $count = 160;//干扰像素的数量
    for($i=0; $i<$count; $i++){
        $randcolor = ImageColorallocate($distortion_im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
        imagesetpixel($distortion_im, mt_rand()%$im_x , mt_rand()%$im_y , $randcolor);
    }

    $rand = mt_rand(5,30);
    $rand1 = mt_rand(15,25);
    $rand2 = mt_rand(5,10);
    for ($yy=$rand; $yy<=+$rand+2; $yy++){
        for ($px=-80;$px<=80;$px=$px+0.1)
        {
            $x=$px/$rand1;
            if ($x!=0)
            {
                $y=sin($x);
            }
            $py=$y*$rand2;

            imagesetpixel($distortion_im, $px+80, $py+$yy, $text_c);
        }
    }
    //以PNG格式将图像输出到浏览器或文件;
    ImagePNG($distortion_im);
    //销毁一图像,释放与image关联的内存;
    ImageDestroy($distortion_im);
    ImageDestroy($im);
}

//加密算法
function moog($string, $operation = '1',$key='',$expiry = 0) {
    if($operation == '1'){
        $string = str_replace("@", "+", $string);
    }
    $key2 = md5($key); // md5混淆
    $ckey_length = 4;
    $key = md5 ( $key ? $key : 'behind' );
    $key2 = md5 ( $key2 ? $key2 . time () : time () . rand ( rand ( 0, 5000 ), rand ( 5000, 10000 ) ) );
    $keya = md5 ( substr ( $key, 0, 16 ) );
    $keyb = md5 ( substr ( $key, 16, 16 ) );
    $keyc = $ckey_length ? ($operation == '1' ? substr ( $string, 0, $ckey_length ) : substr ( md5 ( time () . $key2 ), 0,4 )) : '';
    $cryptkey = $keya . md5 ( $keya . $keyc );
    $key_length = strlen ( $cryptkey );
    $string = $operation == '1' ? base64_decode ( substr ( $string, $ckey_length ) ) : sprintf ( '%010d', $expiry ? $expiry + time () : 0 ) . substr ( md5 ( $string . $keyb ), 0, 16 ) . $string;
    $string_length = strlen ( $string );
    $result = '';
    $box = range ( 0, 255 );
    $rndkey = array ();
    for($i = 0; $i <= 255; $i ++) {
        $rndkey [$i] = ord ( $cryptkey [$i % $key_length] );
    }
    for($j = $i = 0; $i < 256; $i ++) {
        $j = ($j + $box [$i] + $rndkey [$i]) % 256;
        $tmp = $box [$i];
        $box [$i] = $box [$j];
        $box [$j] = $tmp;
    }
    for($a = $j = $i = 0; $i < $string_length; $i ++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box [$a]) % 256;
        $tmp = $box [$a];
        $box [$a] = $box [$j];
        $box [$j] = $tmp;
        $result .= chr ( ord ( $string [$i] ) ^ ($box [($box [$a] + $box [$j]) % 256]) );
    }
    if ($operation == '1') {
        if ((substr ( $result, 0, 10 ) == 0 || substr ( $result, 0, 10 ) - time () > 0) && substr ( $result, 10, 16 ) == substr ( md5 ( substr ( $result, 26 ) . $keyb ), 0, 16 )) {
            return substr ( $result, 26 );
        } else {
            return '';
        }
    } else {
        $ks =  $keyc . str_replace ( '=', '', base64_encode ( $result ) );
        return str_replace("+", "@", $ks);
    }
}

//全自动自动翻页 - v1.2 升级版 作者:心剑
function autopage_wkii($count, $page, $num,$url,$wkii)
{
    if ($count!=1){
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
        if ($wkii == 2){
            echo "<a href='{$url}index.php/writings/index/home/page/{$topPage}'>上一页</a>";
        }else{
            echo "<a href='{$url}p{$topPage}.html'>上一页</a>";
        }

        if ($page == $count || $page>1 && $page<$count){
            if ($wkii == 2){
                echo "<a href='{$url}index.php/writings/index/home/page/1'>首页</a>";
            }else{
                echo "<a href='{$url}p1.html'>首页</a>";
            }
        }
        for ($i = $start; $i <= $end; $i ++) { // 输出分页条，请自行添加链接样式
            if ($i == $page){
                //当前页HTML代码
                echo "<span>$i</span>";
        
            }else{
                //其他页HTML代码
                if ($wkii == 2){
                    echo "<a href='{$url}index.php/writings/index/home/page/{$i}'>$i</a>";
                }else{
                    echo "<a href='{$url}p{$i}.html'>$i</a>";
                }
            }
        }
        if ($page == 1 || $page>1 && $page<$count){
            //最后一页HTML代码
            if ($wkii == 2){
                echo "<a href='{$url}index.php/writings/index/home/page/{$count}'>尾页</a>";
            }else{
                echo "<a href='{$url}p{$count}.html'>尾页</a>";
            }
        }
        //下一页HTML代码
        if ($wkii == 2){
            echo "<a href='{$url}index.php/writings/index/home/page/{$downPage}'>下一页</a>";
        }else{
            echo "<a href='{$url}p{$downPage}.html'>下一页</a>";
        }
    }
}
?>