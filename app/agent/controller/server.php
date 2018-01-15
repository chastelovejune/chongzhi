<?php
namespace agent\controller;
class server
{
    
    //登录验证
    function login(){
        $username = post("username");//用户名
        $password = post("password");//密码
        // 进行token验证
        if ($_SESSION['tokenAgent'] != post("token")) json(6, "Token验证失败", null);
        //检测用户是否完整
        if (empty($username) || empty($password)) json(6, "用户信息不完整", null);
        $dblist = M("agent");
        $query = $dblist->select("agent_username='$username'")[0];
        if (!is_array($query)) json(6, "用户名未找到", null);
        $password = md5($password . $query['agent_rand']);
        if ($password != $query['agent_password']) json(6, "密码错误", null);
        if ($query['agent_freeze'] != 1)  json(6, "您的账号是冻结状态", null);
        setcookie("_u_agent", encrypt(post("username"), "hello", "mengi"), time() + 3600, "/"); // 设置用户cookie
        setcookie("_p_agent", encrypt(post("password"), "hello", $query['agent_rand']), time() + 3600, "/"); // 设置密码
        $oldip = $query['agent_loginip'];
        $dblist->update(array(
            "agent_loginip" => getip()
        )); // 改变登录IP
        json(1, '登录成功', array("oldip"=>$oldip,"ip"=>getip(),"oldarea"=>getallopatry($oldip),"area"=>getallopatry(getip())));
    }
    //安全退出
    function exitlogin(){
        setcookie("_u_agent", '', time(), "/"); // 设置用户cookie
        setcookie("_p_agent", '', time(), "/"); // 设置密码
        jp(__URL__ . "index.php/agent/index/login", 0, "注销成功!");
    }
    
    //找回密码
    function passwordreset() {
        $username = post("username");
        $email = post('email');
        // 进行token验证
        if ($_SESSION['tokenAgent_reset'] != post("token")) json(6, "Token验证失败", null);
        if (empty($email)) json(6, "请输入邮箱", null);
        $dblist = M("agent");
        $query = $dblist->select("agent_username='$username'")[0];
        if (!is_array($query)) json(6, "未找到该用户", null);
        if ($query['agent_email'] != $email) json(6, "邮箱不正确", null);
        $rand = get_rand(6);
        $newpassword = rand(10000000, 99999999);//新密码
        $agent_password = md5($newpassword . $rand);
        $times = date("Y-m-d H:i:s",time());
        $send = mailSend($query['agent_email'], "安全中心-密码重置档案!", "<meta charset='utf-8'><div id='contentDiv0' style='background: #fff; padding-bottom: 20px; zoom: 1; position: relative; z-index: 1;' class='qm_bigsize qm_converstaion_body body qmbox qqmail_webmail_only'>		<table width='100%' border='0' cellpadding='10' cellspacing='0'			style='font-family: 微软雅黑; line-height: 1.6; font-size: 12px'>			<tbody>				<tr>					<td style='background: #cd9c56; width: 180px; text-align: center'><span						style='color: #fff; text-decoration: none; font-size: 16px; font-weight: 800'>账户密码重置</span></td>					<td style='background: #eeece3'></td>				</tr>				<tr style='background: #fff; font-size: 14px'>					<td colspan='2'><p>							您好，<b>{$username}</b>：<br>您的密码已经重置成功，下面则是你的新密码:						</p>						<div style='margin: 10px 0; padding: 10px; background: #FFFADF; border-left: 5px solid #FFD324'>							{$newpassword}						</div></td>				</tr>				<tr>					<td colspan='2' style='background: #eeece3; color: #999'>发件时间：<span style='border-bottom: 1px dashed #ccc;'>{$times}</span>&nbsp;&nbsp;&nbsp;&nbsp;此邮件为系统自动发出，请勿直接回复。</td></tr></tbody></table><style type='text/css'>.qmbox style, .qmbox script, .qmbox head, .qmbox link, .qmbox meta {	display: none !important;}</style></div>");
        if ($send){
            $dblist->update(array("agent_password"=>$agent_password,"agent_rand"=>$rand),"id={$query['id']}");
            json(1, "重置密码邮件已经发送成功", null);
        }else{
            json(6, "邮件发送失败", null);
        }
    }
    
    //ajax获取chat数据
    function agentchat(){
        $this->BS();
        //翻页功能
        $page = get("page");
        $pageNum = 10;//初始化每页显示数量15
        $dblist = M("agentchat");
        $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
        $queryCount = $dblist->sql("select count(id) as c from god_agentchat where agentchat_name='$user'")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount < $page) json(6, '没有更多的数据了..', null);//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $query = $dblist->select("agentchat_name='$user'","","id","$pageMy,$pageNum");
        json(1, $pageCount, $query);
    }
    //发表chat数据
    function sendchat(){
        $this->BS();
        $dblist = M("agentchat");
        $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
        $agentchat_content = post("agentchat_content");
        if (empty($agentchat_content)) json(6, "信息不允许为空..", null);
        $fa = $dblist->insert(array(
            "agentchat_name"=>$user,
            "agentchat_content"=>$agentchat_content,
            "agentchat_time"=>date("Y-m-d H:i:s",time()),
            "agentchat_return"=>'',
            "agentchat_retime"=>''
        ));
        if ($fa){
            json(1, "发送成功", null);
        }else{
            json(6, "发送失败", null);
        }
    }
    
    //发送邮箱验证码
    function sendcode(){
        $this->BS();
        if (cookie("_agent_vcode_time")){
            json(6, "请不要频繁请求", null);
        }
        $vcode = mt_rand(100000, 999999);//6位数字验证码
        $times = date("Y-m-d H:i:s",time());
        $dblist = M("agent");
        $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
        $query = $dblist->select("agent_username='{$user}'")[0];//获取数据库
        $flag = mailSend($query['agent_email'], "安全中心-操作验证码!", "<meta charset='utf-8'><div id='contentDiv0' style='background: #fff; padding-bottom: 20px; zoom: 1; position: relative; z-index: 1;' class='qm_bigsize qm_converstaion_body body qmbox qqmail_webmail_only'>		<table width='100%' border='0' cellpadding='10' cellspacing='0'			style='font-family: 微软雅黑; line-height: 1.6; font-size: 12px'>			<tbody>				<tr>					<td style='background: #cd9c56; width: 180px; text-align: center'><span						style='color: #fff; text-decoration: none; font-size: 16px; font-weight: 800'>敏感操作验证码</span></td>					<td style='background: #eeece3'></td>				</tr>				<tr style='background: #fff; font-size: 14px'>					<td colspan='2'><p>							您好，<b>{$query['agent_username']}</b>：<br>您的验证码为:						</p>						<div style='margin: 10px 0; padding: 10px; background: #FFFADF; border-left: 5px solid #FFD324'>							{$vcode}						</div></td>				</tr>				<tr>					<td colspan='2' style='background: #eeece3; color: #999'>发件时间：<span style='border-bottom: 1px dashed #ccc;'>{$times}</span>&nbsp;&nbsp;&nbsp;&nbsp;此邮件为系统自动发出，请勿直接回复。</td></tr></tbody></table><style type='text/css'>.qmbox style, .qmbox script, .qmbox head, .qmbox link, .qmbox meta {	display: none !important;}</style></div>");
        if ($flag){
            setcookie("_agent_vcode", encrypt($vcode), time() + 300, "/");
            setcookie("_agent_vcode_time", encrypt($vcode), time() + 60, "/");
            json(1, "邮件发送成功", null);
        }else {
            json(6, "邮件发送失败", null);
        }
    }
    
    //修改个人资料
    function editmy(){
        $this->BS();
        $vcode = decrypt(cookie("_agent_vcode"));
        $postcode = post("vcode");
        if ($postcode != $vcode || empty($postcode)) json(6, '验证码不正确', null);
        $dblist = M("agent");
        $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
        $query = $dblist->select("agent_username='{$user}'")[0];//获取数据库
        $agent_rand = get_rand(6);//6位随机码
        $agent_password = post('agent_password');
        if (!empty($agent_password)){
            $agent_password = md5($agent_password . $agent_rand);
        }else{
            $agent_password = $query['agent_password'];
            $agent_rand = $query['agent_rand'];
        }
        
        $agent_email = post("agent_email");
        
        if (!is_email($agent_email)) json(6, '邮箱不正确', null);
        
        $dblist->update(array(
            "agent_password"=>$agent_password,
            "agent_qq"=>post("agent_qq"),
            "agent_email"=>$agent_email,
            "agent_rand"=>$agent_rand,
            "agent_payment"=>post("agent_payment"),
            "agent_payname"=>post("agent_payname")
        ),"id={$query['id']}");
        setcookie("_agent_vcode",'', time(), "/");
        json(1, '修改成功', null);

    }
    
    //提现
    function deposit(){
        $this->BS();
        $vcode = decrypt(cookie("_agent_vcode"));
        $postcode = post("vcode");
        if ($postcode != $vcode || empty($postcode)) json(6, '验证码不正确', null);
        $dblist = M("agent");
        $set = M("settings")->select("id=1")[0];
        $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
        $query = $dblist->select("agent_username='{$user}'")[0];//获取数据库
        $moeny = post("agent_gain");//提现的金额
        $moeny = (float)$moeny;
        $moeny_fit = 0; //初始化最后确认的提现金额
        $moeny_counter = 0;//初始化手续费用
        $moeny_os = '';//初始化手续费比例
        $pay_type = '';//收款方式
        if($query['agent_payment'] == 1){
            $pay_type = '支付宝';
        }
        if($query['agent_payment'] == 2){
            $pay_type = '银行卡';
        }
        if($query['agent_payment'] == 3){
            $pay_type = 'Paypal';
        }
        
        if ($moeny == '' || $moeny < $set['settings_mixdep'] || $moeny == 0) json(6, "提现的金额低于{$set['settings_mixdep']}", null);
        
        $counter = explode("\n", $set['settings_counter']);//将比例分割
        
        if ($counter != ''){
            for ($i=0;$i<count($counter);$i++){
                $boot = explode(",", $counter[$i]);
                if (count($boot) == 2){
                    if ($moeny >= $boot[0]){
                        $moeny_counter = $moeny*$boot[1];//手续费
                        $moeny_fit = $moeny - $moeny_counter;//提现的金额
                        $moeny_os = ($boot[1]*100) . '%';//手续费的百分比
                    }
                }else{
                    $moeny_counter = 0;//手续费
                    $moeny_fit = $moeny;//提现的金额
                    $moeny_os = '0%';//手续费的百分比
                }
                
            }
        }else{
            $moeny_counter = 0;//手续费
            $moeny_fit = $moeny;//提现的金额
            $moeny_os = '0%';//手续费的百分比
        }
        
        $agent_new_gain = $query['agent_gain']-$moeny;
        
        if ($agent_new_gain < 0)  json(6, '账户盈利余额不足', null);
        
        $deposit_serial = date("YmdHis",time()) . rand(100000, 999999);//流水号生成
        
        $up = $dblist->update(array("agent_gain"=>$agent_new_gain),"id={$query['id']}");
        
        if ($up){
            $insert = M("deposit")->insert(array(
                "deposit_serial"=>$deposit_serial,
                "deposit_userid"=>$query['id'],
                "deposit_username"=>$query['agent_username'],
                "deposit_moeny"=>$moeny,
                "deposit_state"=>2,
                "deposit_send"=>$moeny_fit,
                "deposit_counter"=>$moeny_counter,
                "deposit_percent"=>$moeny_os,
                "deposit_ctime"=>time(),
                "deposit_dtime"=>0,
                "deposit_date"=>date("Y-m-d",time()),
                "deposit_cpname"=>post("deposit_cpname"),
                "deposit_paytype"=>$pay_type,
                "deposit_paym"=>$query['agent_payname']
            ));
        }
        
        if ($insert){
            setcookie("_agent_vcode",'', time(), "/");
            $times = date("Y-m-d H:i:s",$times);
            mailSend($query['agent_email'], "个人中心-盈利提现发起成功!", "<meta charset='utf-8'><div id='contentDiv0' style='background: #fff; padding-bottom: 20px; zoom: 1; position: relative; z-index: 1;' class='qm_bigsize qm_converstaion_body body qmbox qqmail_webmail_only'>		<table width='100%' border='0' cellpadding='10' cellspacing='0'			style='font-family: 微软雅黑; line-height: 1.6; font-size: 12px'>			<tbody>				<tr>					<td style='background: #cd9c56; width: 180px; text-align: center'><span						style='color: #fff; text-decoration: none; font-size: 16px; font-weight: 800'>盈利余额发起提现</span></td>					<td style='background: #eeece3'></td>				</tr>				<tr style='background: #fff; font-size: 14px'>					<td colspan='2'><p>							您好，<b>{$query['agent_username']}</b>：</p>						<div style='margin: 10px 0; padding: 10px; background: #FFFADF; border-left: 5px solid #FFD324'>							银行正在处理，一般为10分钟-72小时内到账，请耐心等待					</div></td>				</tr>				<tr>					<td colspan='2' style='background: #eeece3; color: #999'>发件时间：<span style='border-bottom: 1px dashed #ccc;'>{$times}</span>&nbsp;&nbsp;&nbsp;&nbsp;此邮件为系统自动发出，请勿直接回复。</td></tr></tbody></table><style type='text/css'>.qmbox style, .qmbox script, .qmbox head, .qmbox link, .qmbox meta {	display: none !important;}</style></div>");
            json(1, '发起提现成功', null);
        }else{
            setcookie("_agent_vcode",'', time(), "/");
            json(6, '发起提现失败', null);
        }
        
    }
    
    //添加下级代理
    function addagent(){
        $this->BS();
        $dblist = M("agent");
        $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
        $query = $dblist->select("agent_username='{$user}'")[0];//获取数据库
        if ($query['agent_power'] != 1) exit('无权限访问!');
        $agent_rand = get_rand(6);
        if (post("agent_username") == '' || post("agent_qq") == '' || post("agent_itemid") == '' || post("agent_email") == '') json(6, '创建失败,资料不详细', null);
        
        if (post("agent_password") != ''){
            $agent_password = md5(post("agent_password") . $agent_rand);
        }else {
            $agent_password = md5("123456" . $agent_rand);
        }
        $agent_itemid = implode(",", post("agent_itemid"));
        
        
        $agent_power = post("agent_power") != '' ? 1 : 2;
        
        $fa = $dblist->insert(array(
            "agent_username"=>post("agent_username"),
            "agent_password"=>$agent_password,
            "agent_itemid"=>$agent_itemid,
            "agent_moeny"=>0,
            "agent_gain"=>0,
            "agent_gainAll"=>0,
            "agent_qq"=>post("agent_qq"),
            "agent_freeze"=>1,
            "agent_loginip"=>"127.0.0.1",
            "agent_email"=>post("agent_email"),
            "agent_payment"=>1,
            "agent_payname"=>'无',
            "agent_levelid"=>$query['id'],
            "agent_profit"=>post("agent_profit"),
            "agent_power"=>$agent_power,
            "agent_rand"=>$agent_rand,
            "agent_idrank"=>$query['agent_idrank']
        ));
        
        if ($fa){
            json(1, '添加成功', null);
        }else{
            json(6, '添加失败', null);
        }
        
    }
    
    //分销商生成卡密
    function buycard(){
        $this->BS();
        //卡密类型
        $cardtypeid = post('recharge_paynum');
        $cardtype = M("cardtype")->select("id={$cardtypeid}")[0];
        if (!is_array($cardtype)) json(6, '未选择充值卡类型!', null);
        //数量
        $nums = intval(post("nums"));
        if ($nums < 1) json(6, '至少购买1张卡!', null);
        //读取个人信息
        $dblist = M("agent");
        $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
        $query = $dblist->select("agent_username='{$user}'")[0];//获取数据库
        $moeny = 0;
        //开始计算
        if (post("paytype") != 1 && post("paytype") != 2) json(6, '支付来路不明,请勿修改客户端数据!', null);
        if (post("paytype") == 1){
          //账户余额支付
          $moeny = $query['agent_moeny'];
        }else{
          //盈利余额支付  
          $moeny = $query['agent_gain'];
        }
        //取出上级分销商
        $agentdid = did($query);
        $countagentdid = count($agentdid);//长度
        //初始化分利
        $profits = 0;
        //初始化总代理商的数据
        $magentid = $countagentdid-1;
        $magentdata = $dblist->select("id={$agentdid[$magentid]}")[0];//总代理商的数据
        //初始化SQL语句
        $excl = array();
        //当自己是下级分销商的时候计算价格
         if ($countagentdid > 1){
            for ($i=0;$i<$countagentdid;$i++){
                if ($i < $countagentdid-1){
                    $prof = $dblist->select("id={$agentdid[$i]}")[0];
                    $profits = $profits+$prof['agent_profit'];//+分利
                    //计算当前分销商购卡的利润
                    $profit_ex = ($cardtype['cardtype_me']*$prof['agent_profit'])*$nums;//反给当前分销商的利润 
                    //计划任务
                    $excl[] = "update god_agent set agent_gain=agent_gain+{$profit_ex},agent_gainAll=agent_gainAll+{$profit_ex} where id={$prof['agent_levelid']}";
                    $excl[] = "update god_agent set agent_profitnum=agent_profitnum+{$profit_ex} where id={$prof['id']}";
                }
            }
        } 
        //计算自己购买的价格
        $price_bec = rate($magentdata['agent_idrank'], $cardtype['cardtype_me'], $profits);
        if ($price_bec*$nums > $moeny) json(6, '该账户余额不足', null);
        if (post("paytype") == 1){
            $agent_moeny = $query['agent_moeny']-($price_bec*$nums);
            $agent_gain = $query['agent_gain'];
        }else{
            $agent_moeny = $query['agent_moeny'];
            $agent_gain = $query['agent_gain']-($price_bec*$nums);
        }
        
        $buy = $dblist->update(array("agent_moeny"=>$agent_moeny,"agent_gain"=>$agent_gain),"id={$query['id']}");
        if ($buy){
            if (count($excl) > 0){
                for ($i=0;$i<count($excl);$i++){
                    $dblist->sql($excl[$i]);
                }
            }
            //开始生成充值卡
            $recharge = M("recharge");
            $item = M("item")->select("id={$_SESSION['itemID']}")[0];
            $recharge_rand = strtoupper(get_rand(8));//批卡代码
            for ($i=0;$i<$nums;$i++){
                $cami = get_rand(30);
                $recharge->insert(array(
                    "recharge_cami"=>$cami,
                    "recharge_paynum"=>$cardtype['cardtype_num'],
                    "recharge_frozen"=>1,
                    "recharge_logint"=>$item['item_loginType'],
                    "recharge_ctime"=>time(),
                    "recharge_credate"=>date("Y-m-d",time()),
                    "recharge_usetime"=>0,
                    "recharge_user"=>0,
                    "recharge_create"=>$query['agent_username'],
                    "recharge_itemid"=>$item['id'],
                    "recharge_useitem"=>0,
                    "recharge_rand"=>$recharge_rand
                ));
                $text .= "卡号：".$cami."----面值：".$cardtype['cardtype_num']. PHP_EOL;
                
            }
           json(1, "充值卡生成完毕", array("cami"=>$text));
        }else{
           json(6, "充值卡生成失败", null);
        }
    }
    
    
    //导出分类
    function Downrecharge(){
        $this->BS();
        $dblist = M("recharge");
        //分类
        $frozen = get("frozen");
        $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
        $fans = "and recharge_create='{$user}'";
        $where = "1=1 $fans";
        if (!empty($frozen)){
            //未冻结
            if ($frozen == 1){
                $where = "recharge_frozen=1 $fans";
            }
            //已冻结
            if ($frozen == 2){
                $where = "recharge_frozen=2 $fans";
            }
            //已使用
            if ($frozen == 3){
                $where = "recharge_useitem != 0 $fans";
            }
            //未使用
            if ($frozen == 4){
                $where = "recharge_useitem = 0 $fans";
            }
            //可使用
            if ($frozen == 5){
                $where = "recharge_frozen = 1 and recharge_useitem = 0 $fans";
            }
        }
    
        $dbdata = $dblist->select($where);//查询出来
        $cami = '';
        $filename = date("Y-m-d H:i:s",time()) . '导出充值卡.txt';
        foreach ($dbdata as $mi){
            $cami .= "卡号：".$mi['recharge_cami']."----面值：".$mi['recharge_paynum']."\r\n";
        }
        DownText($filename, $cami);
    }
    
    //获取单个用户的数据
    function getAgent(){
        $this->BS();
        $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
        $dblist = M("agent");
        $query = $dblist->select("agent_username='{$user}'")[0];//获取数据库
        if ($query['agent_power'] != 1) json(6, '无权限访问', null);
        $id = get("id");
        $data = $dblist->select("id={$id} and agent_levelid={$query['id']}",null,null,null,"id,agent_username,agent_profit,agent_profitnum")[0];
        if ($data){
            json(1, '获取数据成功', $data);
        }else {
            json(6, '获取数据失败', null);
        }
    }
    
    //监听订单
    function payment(){
        $this->BS();
        //要监听的订单号
        $alipay_serial = get("alipay_serial");
        $alipayDB = M("alipay");
        $query = $alipayDB->select("alipay_serial='{$alipay_serial}'")[0];
        $dime = M("dime");
        if ($query['alipay_state'] == 1){
            //订单还未支付
            $dime->update(array("dime_heartbeat"=>time()),"id={$query['alipay_dimeid']}");
            json(6, '等待付款中..', null);
        }else{
            $dime->update(array("dime_ctime"=>0,"dime_ways"=>2,"dime_heartbeat"=>0),"id={$query['alipay_dimeid']}");
            json(1, '付款成功', null);
        }
    }
    
    //修改利润点
    function editProfit(){
        $this->BS();
        $user = decrypt(cookie("_u_agent"),"hello","mengi");//取出用户名
        $dblist = M("agent");
        $query = $dblist->select("agent_username='{$user}'")[0];//获取数据库
        if ($query['agent_power'] != 1) json(6, '无权限访问', null);
        $id = post("agent_id_edit");
        $agent_profit_edit = post("agent_profit_edit");
        if(empty($agent_profit_edit)) json(6, '利润点禁止为空', null);
        $edit = $dblist->update(array("agent_profit"=>$agent_profit_edit),"id={$id} and agent_levelid={$query['id']}");
        if ($edit){
            json(1, '修改成功', null);
        }else {
            json(6, '修改失败', null);
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
