<?php
namespace agent\controller;
class index{
    
    //登录
    function login(){
       //检测是否登录过
        if (cookie("_u_agent")){
            $dblist = M("agent");
            $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
            $query = $dblist->select("agent_username='{$user}'")[0];//获取数据库
            $pass = md5(decrypt(cookie("_p_agent"),"hello",$query['agent_rand']).$query['agent_rand']);//取出密码
            if ($pass == $query['agent_password'] && $query['agent_freeze'] == 1){
                jp(__URL__ . "index.php/agent/index/home", 1, "自动登录成功!登录地点:" . getallopatry(getip())) . "(" . getip() . ")";
            }
        }
        $token = get_rand(32);//生成32位token验证码
        $_SESSION['tokenAgent'] = $token;
        display("login.html",array("token"=>$token));
    } 
    
    //忘记密码,重置密码
    function passwordreset(){
        //检测是否登录过
        if (cookie("_u_agent")){
            $dblist = M("agent");
            $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
            $query = $dblist->select("agent_username='{$user}'")[0];//获取数据库
            $pass = md5(decrypt(cookie("_p_agent"),"hello",$query['agent_rand']).$query['agent_rand']);//取出密码
            if ($pass == $query['agent_password']){
                jp(__URL__ . "index.php/agent/index/home", 1, "自动登录成功!登录地点:" . getallopatry(getip())) . "(" . getip() . ")";
            }
        }
        $token = get_rand(32);//生成32位token验证码
        $_SESSION['tokenAgent_reset'] = $token;
        display("passwordreset.html",array("token"=>$token));
        
    }
    
    //首页
    function home(){
        $this->BS();
        $dblist = M("agent");
        $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
        $query = $dblist->select("agent_username='{$user}'")[0];//获取数据库
        $deposit = M("deposit")->select("deposit_userid={$query['id']}","","id","0,20");
        display("home.html",array("deposit"=>$deposit)); 
    }
    //我的盈利
    function myprofit(){
        $this->BS();
        $dblist = M("agent");
        $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
        $query = $dblist->select("agent_username='{$user}'")[0];//获取数据库
        if ($query['agent_power'] != 1) exit('无权限访问!');
        $where = "agent_levelid={$query['id']}";
        //翻页功能
        $page = get("page");
        $pageNum = 15;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_agent {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page);
        $query = $dblist->select($where,null,"id","$pageMy,$pageNum");
        display("myprofit.html",array("data"=>$query,"arrayPage"=>$arrayPage));;
    }
    
    //我的充值卡
    function mycard(){
        $this->BS();
        $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
        $dblist = M("recharge");
        //搜索功能
        $search = get("search");
        //冻结分类
        $frozen = get("frozen");
        //软件ID查询
        $itemID = get("itemID");
        //用户查询
        $userID = get("userID");
        //时间查询
        $TimeID = get("TimeID");
        
        $where = "recharge_create='{$user}'";
        
        if (!empty($TimeID)){
            $where = "recharge_credate like '%{$TimeID}%' and recharge_create = '{$user}'";
        }        
        if (!empty($userID)){
            $where = "recharge_user like '%{$userID}%' and recharge_create = '{$user}'";
        }
        if (!empty($itemID)){
            $it = M("item")->select("id={$itemID}")[0];
            if (!is_array($it)) jp(__URL__ . "index.php/agent/index/mycard", 1,"请勿而已修改客户端数据..");
            $_SESSION['itemID'] = $itemID;
            $where = "recharge_itemid like '%{$itemID}%' and recharge_create = '{$user}'";
        }

        if (!empty($frozen)){
            //未冻结
            if ($frozen == 1){
                $where = "recharge_frozen=1 and $where";
            }
            //已冻结
            if ($frozen == 2){
                $where = "recharge_frozen=2 and $where";
            }
            //已使用
            if ($frozen == 3){
                $where = "recharge_useitem != 0 and $where";
            }
            //未使用
            if ($frozen == 4){
                $where = "recharge_useitem = 0 and $where";
            }
            //可使用
            if ($frozen == 5){
                $where = " recharge_frozen = 1 and recharge_useitem = 0 and $where";
            }
        
        }
        if (!empty($search)){
            $where = "recharge_cami like '%{$search}%'";
        }
        
        if (!empty($search) && !empty($frozen)){
            $where = "recharge_frozen={$frozen} and recharge_cami like '{$search}%' and recharge_create = '{$user}'";
        }
        //翻页功能
        $page = get("page");
        $pageNum = 20;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_recharge {$countwhere}")[0]['c'];//所有记录数量
        
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page,"camiNum"=>$queryCount);
        $query = $dblist->select($where,null,"id","$pageMy,$pageNum");
        display("rec.html",array("data"=>$query,"arrayPage"=>$arrayPage));
    }
    
    //充值
    function pay(){
        $this->BS();
        $cookies = cookie("AAPID_PAY_SESSIONID");
        if (!empty($cookies)) exit("当前交易还未完成,请稍等交易完成..");
        //取出用户名
        $user = decrypt(cookie("_u_agent"),"hello","mengi");
        //交易单号生成
        $alipay_serial = date("YmdHis",time()) . rand(100000000, 999999999);
        //充值金额
        $dime_name = post("dime_name");
        //判断伪造提交
        if (empty($dime_name)) exit("当前页面已被重置..");
        //支付方式
        $alipay_type = post("alipay_type");//1支付宝  2微信
        if ($alipay_type != 1 && $alipay_type != 2) exit("当前页面已被重置!..");
        //删除未支付的订单
        $alipayDB = M("alipay");
        $alipayDB->delete("alipay_memo='{$user}' and alipay_state=1");
        //二维码生成
        $dime = M("dime");
        //随机拉起二维码
        $sdk = $dime->select("dime_name={$dime_name} and dime_ways=2");
        if (count($sdk) == 0) exit("当前支付通道繁忙,请等待几秒钟再试!");
        $num = rand(0, (count($sdk)-1));
        $jdk = $sdk[$num];//拉起信息
        if ($alipay_type == 1){
            //支付宝
            $url = $jdk['dime_alipayurl'];
            $display = 'pay.html';
        }else{
            $url = $jdk['dime_wxurl'];
            $display = 'wechat.html';
            //exit('暂不支持微信');
        }
        //占用通道
        $dime->update(array("dime_ctime"=>time(),"dime_heartbeat"=>time(),"dime_ways"=>1),"id={$jdk['id']}");
        setcookie("AAPID_PAY_SESSIONID", encrypt($alipay_serial), time() + 60, "/"); // 设置cookie
        
        $alipay = $alipayDB->insert(array(
            "alipay_serial"=>$alipay_serial,
            "alipay_moeny"=>$dime_name,
            "alipay_ctime"=>time(),
            "alipay_time"=>0,
            "alipay_date"=>0,
            "alipay_memo"=>$user,
            "alipay_state"=>1,
            "alipay_frozen"=>1,
            "alipay_type"=>$alipay_type,
            "alipay_repx"=>1,
            "alipay_dimeid"=>$jdk['id']
        ));   
        if ($alipay){
            setcookie("AAPID_PAY_SESSIONID", '0', time(), "/"); // 设置cookie
            display($display,array("dime_name"=>$dime_name,"alipay_serial"=>$alipay_serial,"url"=>$url));
        }else{
            exit("支付通道创建失败,请稍等几秒再试!");
        }
    }
    
   
    
    //验证是否登录
    function BS(){
        if (cookie("_u_agent")){
            $dblist = M("agent");
            $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
            $query = $dblist->select("agent_username='{$user}'")[0];//获取数据库
            if (!is_array($query)){
                jp(__URL__ . "index.php/agent/index/login", 3, "会话已经过期,请重新登录!");
            }
            $pass = md5(decrypt(cookie("_p_agent"),"hello",$query['agent_rand']).$query['agent_rand']);//取出密码
            if ($pass != $query['agent_password']){
                jp(__URL__ . "index.php/agent/index/login", 3, "会话已经过期,请重新登录!");
            }
            if ($query['agent_freeze'] != 1){
                setcookie("_u_agent", '', time(), "/"); // 设置用户cookie
                setcookie("_p_agent", '', time(), "/"); // 设置密码
                jp(__URL__ . "index.php/agent/index/login", 3, "账户已经被冻结,强制退出!");
            }
            setcookie("_u_agent", encrypt($user, "hello", "mengi"), time() + 3600, "/"); // 设置用户cookie
            setcookie("_p_agent", encrypt(decrypt(cookie("_p_agent"),"hello",$query['agent_rand']), "hello", $query['agent_rand']), time() + 3600, "/"); // 设置密码
        }else{
            jp(__URL__ . "index.php/agent/index/login", 3, "会话已经过期,请重新登录!");
        }
    
    }



}


?>