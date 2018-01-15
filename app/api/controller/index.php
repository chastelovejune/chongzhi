<?php
namespace api\controller;
class index{ 
    //获取验证码
    function imgcode(){
        Imgcode(4,160,40);
    }
    //发送邮件
    function mailsend(){
        //初始化软件
        $Moog = $this->BS('item');
        //data 0 软件key  1 邮箱地址
        //初始化user数据库
        if (!is_email($Moog['data'][1])) json(6, '邮箱输入不正确', null);
        $cookies = cookie("APP_SESSION_TIME");
        if (!empty($cookies)) json(6, '验证码发送过于频繁!', null);
        $user = M("user");
        $user_email = $Moog['item']['id'] . '-' .$Moog['data'][1];
        $user_query = $user->select("user_email = '{$user_email}' and user_itemid={$Moog['item']['id']}")[0];
        if (is_array($user_query)) json(6, '该邮箱已经注册过了', null);
        $regmailcode = rand(100000, 999999);
        $times = date("Y-m-d H:i:s",time());
        $falg = mailSend($Moog['data'][1], "欢迎您的注册!这是您的验证码", "<meta charset='utf-8'><div id='contentDiv0' style='background: #fff; padding-bottom: 20px; zoom: 1; position: relative; z-index: 1;' class='qm_bigsize qm_converstaion_body body qmbox qqmail_webmail_only'>		<table width='100%' border='0' cellpadding='10' cellspacing='0'			style='font-family: 微软雅黑; line-height: 1.6; font-size: 12px'>			<tbody>				<tr>					<td style='background: #cd9c56; width: 180px; text-align: center'><span						style='color: #fff; text-decoration: none; font-size: 16px; font-weight: 800'>邮箱注册验证码</span></td>					<td style='background: #eeece3'></td>				</tr>				<tr style='background: #fff; font-size: 14px'>					<td colspan='2'><p>							您好，<b>{$Moog['data'][1]}</b>：<br>					</p>						<div style='margin: 10px 0; padding: 10px; background: #FFFADF; border-left: 5px solid #FFD324'>							{$regmailcode}						</div></td>				</tr>				<tr>					<td colspan='2' style='background: #eeece3; color: #999'>发件时间：<span style='border-bottom: 1px dashed #ccc;'>{$times}</span>&nbsp;&nbsp;&nbsp;&nbsp;此邮件为系统自动发出，请勿直接回复。</td></tr></tbody></table><style type='text/css'>.qmbox style, .qmbox script, .qmbox head, .qmbox link, .qmbox meta {	display: none !important;}</style></div>");
        if($falg){
            setcookie("_regmailcode", encrypt($regmailcode), time() + 270, "/"); // 设置用户cookie 验证码 270秒后过期
            setcookie("APP_SESSION_TIME", '爱只能遇见，不能预见。 ', time() + 60, "/"); // 设置用户cookie 验证码 60秒后过期
            json(1, '邮箱验证码发送成功', null);
        }else{
            json(6, '邮箱验证码发送失败', null);
        }
    }
    //发送注册短信
    function phone(){
        //初始化软件
        $Moog = $this->BS('item');
        //data 0 软件key  1 手机
        //初始化user数据库
        if (!is_mobile($Moog['data'][1])) json(6, '手机输入不正确', null);
        $cookies = cookie("PHONE_SESSION_TIME");
        if (!empty($cookies)) json(6, '验证码发送过于频繁!', null);
        $user = M("user");
        $user_mobile = $Moog['item']['id'] . '-' .$Moog['data'][1];
        $user_query = $user->select("user_mobile = '{$user_mobile}' and user_itemid={$Moog['item']['id']}")[0];
        if (is_array($user_query)) json(6, '该手机已经注册过了', null);
        $regmobilecode = rand(100000, 999999);
        $falg = mobilesend($Moog['data'][1], "您的验证码是：{$regmobilecode}。请不要把验证码泄露给其他人。");
        if($falg){
            setcookie("_regmobilecode", encrypt($regmobilecode), time() + 270, "/"); // 设置用户cookie 验证码 270秒后过期
            setcookie("PHONE_SESSION_TIME", '爱只能遇见，不能预见。 ', time() + 60, "/"); // 设置用户cookie 验证码 60秒后过期
            json(1, '手机验证码发送成功', null);
        }else{
            json(6, '手机验证码发送失败', null);
        }
    }
    
    //用户注册
    function register(){
        //初始化软件
        $Moog = $this->BS('item');
        //data 0 软件key  1  注册类型(1个性账号 2邮箱账号 3手机账号) 2 图像验证码  3 充值卡
        if ($Moog['item']['item_regVeod'] == 1){
            $imgcode = decrypt(cookie('_imgcode'));//获取验证码
            if ($Moog['data'][2] != $imgcode || $Moog['data'][2]=='') json(6, '验证码输入错误', null);
        }
        //初始化user数据库
        $user = M("user");
        //初始化 user_account
        $user_account = 0;
        //初始化 user_rand
        $user_rand = get_rand(6);
        if ($Moog['item']['item_regipNum'] != 0){
            $ip = getip();
            $regip = $user->select("user_regip='{$ip}'");
            if (count($regip) > $Moog['item']['item_regipNum']) json(6, '您的IP注册数量达到最大', null);
        }
        //强制输入充值卡注册
        if ($Moog['item']['item_regCode'] == 1){
            $recharge = M("recharge");
            $recharge_query = $recharge->select("recharge_cami='{$Moog['data'][3]}' and recharge_itemid like '%{$Moog['item']['id']}%'")[0];
            if (!is_array($recharge_query)) json(6, '充值卡不存在', null);
            if ($recharge_query['recharge_user'] != 0) json(6, '充值卡已被使用', null);
            if ($recharge_query['recharge_frozen'] != 1) json(6, '充值卡已被冻结', null);
            if ($recharge_query['recharge_frozen'] != 1) json(6, '充值卡已被冻结', null);
            if ($recharge_query['recharge_logint'] != 1) json(6, '该充值卡不能被充值', null);
            if ($Moog['item']['item_doType'] == 1){
                //时间模式
                $user_account = date("Y-m-d H:i:s",time() + ($recharge_query['recharge_paynum']*3600));
            }else {
                //次数 金钱
                $user_account = $recharge_query['recharge_paynum'];
            }
        }
        //注册赠送
        if ($Moog['item']['item_regGive'] != 0){
            if ($Moog['item']['item_doType'] == 1){
                //时间模式
                if ($user_account != '0'){
                    //检测到充值卡赋值了
                    $user_account = date("Y-m-d H:i:s",intval(strtotime($user_account)) + ($Moog['item']['item_regGive']*60));
                }else{
                    $user_account = date("Y-m-d H:i:s",time()+($Moog['item']['item_regGive']*60));
                }
            }else {
                //次数 金钱
                if ($user_account != '0'){
                    $user_account = intval($user_account)+$Moog['item']['item_regGive'];
                }else{
                    $user_account = $Moog['item']['item_regGive'];
                }
            }
        }
        $type = intval($Moog['data'][1]);
        //检测类型
        if (!in_array($type, array(1,2,3))) json(6, '未找到该类型', null);
        //个性账号注册开始
        if (!is_password($Moog['data'][5])) json(6, '密码格式输入不正确', null);
        if ($type == 1){
            //检测开关
            //data  4 用户账号 5 密码  6 安全密码
            if ($Moog['item']['item_regGexi'] != 1) json(6, '个性账号注册未开启', null);
            if (!is_username($Moog['data'][4])) json(6, '账号由字母开头,且不得少于5位', null);
            if (!is_password($Moog['data'][6])) json(6, '安全密码格式输入不正确', null);
            $user_username = $Moog['item']['id'] . '-' .trim($Moog['data'][4]);//写入账号
            $recharge_user = $user_username;
            $user_mobile = $Moog['item']['id'] . '-' .get_rand(8);
            $user_email = $Moog['item']['id'] . '-' .get_rand(8);
            $user_passwordsec = md5($Moog['data'][6] . $user_rand);
            $user_query = $user->select("user_username = '{$user_username}' and user_itemid={$Moog['item']['id']}")[0];
            if (is_array($user_query)) json(6, '该用户名已被注册', null);
        }
        //邮箱注册开始
        if ($type == 2){
            //data  4 邮箱账号 5 密码  6 邮箱验证码
            $regmailcode = decrypt(cookie('_regmailcode'));//获取验证码
            if ($Moog['item']['item_regEmail'] != 1) json(6, '邮箱注册未开启', null);
            if (!is_email($Moog['data'][4])) json(6, '邮箱输入不正确', null);
            if ($Moog['data'][6] != $regmailcode) json(6, '邮箱验证码不正确', null);
            $user_username = $Moog['item']['id'] . '-' .rand(10000000, 99999999);//写入账号
            $user_mobile = $Moog['item']['id'] . '-' .get_rand(8);
            $user_email = $Moog['item']['id'] . '-' .$Moog['data'][4];
            $recharge_user = $user_email;
            $user_passwordsec = 0;
            $user_query = $user->select("user_email = '{$user_email}' and user_itemid={$Moog['item']['id']}")[0];
            if (is_array($user_query)) json(6, '该邮箱已经注册过了', null);
        }
        //手机注册开始
        if ($type == 3){
            //data  4 手机账号 5 密码  6 手机验证码
            $regmobilecode = decrypt(cookie('_regmobilecode'));//获取验证码
            if ($Moog['item']['item_regMobile'] != 1) json(6, '手机注册未开启', null);
            if (!is_mobile($Moog['data'][4])) json(6, '手机输入不正确', null);
            if ($Moog['data'][6] != $regmobilecode) json(6, '手机验证码不正确', null);
            $user_username = $Moog['item']['id'] . '-' .rand(10000000, 99999999);//写入账号
            $user_mobile = $Moog['item']['id'] . '-' .$Moog['data'][4];
            $recharge_user = $user_mobile;
            $user_email = $Moog['item']['id'] . '-' .get_rand(8);
            $user_passwordsec = 0;
            $user_query = $user->select("user_mobile = '{$user_mobile}' and user_itemid={$Moog['item']['id']}")[0];
            if (is_array($user_query)) json(6, '该手机号已,经注册过了', null);
        }
        //编译密码
        $user_password = md5($Moog['data'][5] . $user_rand);
        $user_needword = encrypt($Moog['data'][5]);
        //开始写入数据到数据库
        if ($Moog['item']['item_regCode'] == 1){
            $recharge_unset = $recharge->update(array("recharge_usetime"=>time(),"recharge_user"=>$recharge_user,"recharge_useitem"=>$Moog['item']['id']),"id={$recharge_query['id']}");
        }else{
            $recharge_unset = true;
        }
        //开始写入数据
        if ($recharge_unset){
            $falg = $user->insert(array(
                "user_username"=>$user_username,
                "user_password"=>$user_password,
                "user_needword"=>$user_needword,
                "user_passwordsec"=>$user_passwordsec,
                "user_mobile"=>$user_mobile,
                "user_email"=>$user_email,
                "user_device"=>0,
                "user_regip"=>getip(),
                "user_loginip"=>'0',
                "user_regTime"=>time(),
                "user_regDate"=>date("Y-m-d",time()),
                "user_loginTime"=>0,
                "user_loginNum"=>0,
                "user_account"=>$user_account,
                "user_itemid"=>$Moog['item']['id'],
                "user_dynamic"=>0,
                "user_frozen"=>1,
                "user_rand"=>$user_rand,
                "user_key"=>get_rand(10)
            ));
        }
        if ($falg){
            setcookie("_regmailcode", '', time(), "/"); // 设置用户cookie过期
            json(1, '注册成功,您的账户明细:'.$user_account, null);
        }else{
            json(2, '注册失败,请重试', null);
        }
        
    }
    
    //修改密码
    function passedit(){
        //初始化软件
        $Moog = $this->BS('item');
        //data 0 软件key  1  类型(1个性账号 2邮箱账号 3手机账号)
        $type = intval($Moog['data'][1]);
        //初始化user数据库
        $user = M("user");
        if (!in_array($type, array(1,2,3))) json(6, '未找到该类型', null);
        if ($type == 1){
            //检测开关
            //data  2 用户账号 3 安全密码  4 新密码
            if ($Moog['item']['item_regGexi'] != 1) json(6, '个性账号未开启', null);
            
            $user_username = $Moog['item']['id'] . '-' .trim($Moog['data'][2]);//写入账号
            
            $user_query = $user->select("user_username = '{$user_username}' and user_itemid={$Moog['item']['id']}")[0];
              
            if (!is_array($user_query)) json(6, '未找到该用户名', null);
            
            $user_passwordsec = md5(trim($Moog['data'][3]). $user_query['user_rand']);
            
            if ($user_passwordsec != $user_query['user_passwordsec']) json(6, '安全密码不正确', null);
            
            if (!is_password($Moog['data'][4])) json(6, '新密码格式不正确', null);
            
        }
        //邮箱
        if ($type == 2){
            //data  2 邮箱账号 3 邮箱验证码   4新密码
            $regmailcode = decrypt(cookie('_passmailcode'));//获取邮箱验证码cookie
            
            if ($Moog['item']['item_regEmail'] != 1) json(6, '邮箱未开启', null);
            
            $user_email = $Moog['item']['id'] . '-' .$Moog['data'][2];
            
            $user_query = $user->select("user_email = '{$user_email}' and user_itemid={$Moog['item']['id']}")[0];
            
            if (!is_array($user_query)) json(6, '该邮箱还未注册', null);
 
            if ($Moog['data'][3] != $regmailcode || empty($Moog['data'][3])) json(6, '邮箱验证码不正确', null);
            
            if (!is_password($Moog['data'][4])) json(6, '新密码格式不正确', null);
 
        }
        //手机
        if ($type == 3){
            //data  2 手机账号 3手机验证码  4新密码
            $regmobilecode = decrypt(cookie('_passmobilecode'));//获取验证码
            
            if ($Moog['item']['item_regMobile'] != 1) json(6, '手机未开启', null);
            
            $user_mobile = $Moog['item']['id'] . '-' .$Moog['data'][2];
            
            $user_query = $user->select("user_mobile = '{$user_mobile}' and user_itemid={$Moog['item']['id']}")[0];
           
            if (!is_array($user_query)) json(6, '该手机号还未注册', null);
            
            if ($Moog['data'][3] != $regmobilecode || empty($Moog['data'][3])) json(6, '手机验证码不正确', null);
            
            if (!is_password($Moog['data'][4])) json(6, '新密码格式不正确', null);  
        }
        
        $user_password = md5($Moog['data'][4] . $user_query['user_rand']);
        
        $user_needword = encrypt($Moog['data'][4]);
        
        $falg = $user->update(array("user_password"=>$user_password,"user_needword"=>$user_needword,"user_loginip"=>0),"id={$user_query['id']}");
        
        if ($falg){
            json(1, '密码修改成功', null);
        }else{
            json(6, '密码修改失败', null);
        }
    }
    
    //发送修改密码邮件
    function mailsend_pass(){
        //初始化软件
        $Moog = $this->BS('item');
        //data 0 软件key  1 邮箱地址
        //初始化user数据库
        if (!is_email($Moog['data'][1])) json(6, '邮箱输入不正确', null);
        $cookies = cookie("APP_SESSION_TIME");
        if (!empty($cookies)) json(6, '验证码发送过于频繁!', null);
        $user = M("user");
        $user_email = $Moog['item']['id'] . '-' .$Moog['data'][1];
        $user_query = $user->select("user_email = '{$user_email}' and user_itemid={$Moog['item']['id']}")[0];
        if (!is_array($user_query)) json(6, '未找到该邮箱', null);
        $regmailcode = rand(100000, 999999);
        $times = date("Y-m-d H:i:s",time());
        $falg = mailSend($Moog['data'][1], "修改密码!这是您的验证码", "<meta charset='utf-8'><div id='contentDiv0' style='background: #fff; padding-bottom: 20px; zoom: 1; position: relative; z-index: 1;' class='qm_bigsize qm_converstaion_body body qmbox qqmail_webmail_only'>		<table width='100%' border='0' cellpadding='10' cellspacing='0'			style='font-family: 微软雅黑; line-height: 1.6; font-size: 12px'>			<tbody>				<tr>					<td style='background: #cd9c56; width: 180px; text-align: center'><span						style='color: #fff; text-decoration: none; font-size: 16px; font-weight: 800'>邮箱密码修改验证码</span></td>					<td style='background: #eeece3'></td>				</tr>				<tr style='background: #fff; font-size: 14px'>					<td colspan='2'><p>							您好，<b>{$Moog['data'][1]}</b>：<br>					</p>						<div style='margin: 10px 0; padding: 10px; background: #FFFADF; border-left: 5px solid #FFD324'>							{$regmailcode}						</div></td>				</tr>				<tr>					<td colspan='2' style='background: #eeece3; color: #999'>发件时间：<span style='border-bottom: 1px dashed #ccc;'>{$times}</span>&nbsp;&nbsp;&nbsp;&nbsp;此邮件为系统自动发出，请勿直接回复。</td></tr></tbody></table><style type='text/css'>.qmbox style, .qmbox script, .qmbox head, .qmbox link, .qmbox meta {	display: none !important;}</style></div>");
        if($falg){
            setcookie("_passmailcode", encrypt($regmailcode), time() + 270, "/"); // 设置用户cookie 验证码 270秒后过期
            setcookie("APP_SESSION_TIME", '爱只能遇见，不能预见。 ', time() + 60, "/"); // 设置用户cookie 验证码 60秒后过期
            json(1, '邮箱验证码发送成功', null);
        }else{
            json(6, '邮箱验证码发送失败', null);
        }
    }
    //发送注册短信
    function phone_pass(){
        //初始化软件
        $Moog = $this->BS('item');
        //data 0 软件key  1 手机
        //初始化user数据库
        if (!is_mobile($Moog['data'][1])) json(6, '手机输入不正确', null);
        $cookies = cookie("PHONE_SESSION_TIME");
        if (!empty($cookies)) json(6, '验证码发送过于频繁!', null);
        $user = M("user");
        $user_mobile = $Moog['item']['id'] . '-' .$Moog['data'][1];
        $user_query = $user->select("user_mobile = '{$user_mobile}' and user_itemid={$Moog['item']['id']}")[0];
        if (!is_array($user_query)) json(6, '未找到该手机号码', null);
        $regmobilecode = rand(100000, 999999);
        $falg = mobilesend($Moog['data'][1], "您的验证码是：{$regmobilecode}。请不要把验证码泄露给其他人。");
        if($falg){
            setcookie("_passmobilecode", encrypt($regmobilecode), time() + 270, "/"); // 设置用户cookie 验证码 270秒后过期
            setcookie("PHONE_SESSION_TIME", '爱只能遇见，不能预见。 ', time() + 60, "/"); // 设置用户cookie 验证码 60秒后过期
            json(1, '手机验证码发送成功', null);
        }else{
            json(6, '手机验证码发送失败', null);
        }
    }
    
    //用户充值
    function pay(){
        //初始化软件  data : 0 key 1 账号  2 充值卡 
        $Moog = $this->BS('item');
        $recharge_user = $Moog['item']['id'] . '-' . $Moog['data'][1];
        $where = '';
        $Fnd = "and user_itemid={$Moog['item']['id']}";
        //进行账号判断
        $key = userkey($Moog['data'][1]);
        if (empty($key)) json(6, '账号输入不正确', null);
        $where = "{$key}='{$recharge_user}' $Fnd";
        //初始化user
        $user = M("user");
        //获取用户数据
        $user_query = $user->select($where)[0];
        if (!is_array($user_query)) json(6, '用户未找到', NULL);
        //初始化充值卡
        $recharge = M("recharge");
        //获取充值卡数据
        $recharge_query = $recharge->select("recharge_cami='{$Moog['data'][2]}'")[0];
        if (!is_array($recharge_query)) json(6, '充值卡不存在', NULL);
        //开始判断充值卡各种信息
        if ($recharge_query['recharge_frozen'] != 1) json(6, '该充值卡已被冻结', null);
        if ($recharge_query['recharge_logint'] != 1) json(6, '该充值卡无法对用户进行充值', null);
        if ($recharge_query['recharge_user'] != 0) json(6, '该充值卡已被使用', null);
        //开始写入数据
        if ($Moog['item']['item_doType'] == 1){
            //时间模式
            if (strtotime($user_query['user_account']) < time()){
                $user_account = date("Y-m-d H:i:s",time() + ($recharge_query['recharge_paynum']*3600));
            }else{
                $user_account = date("Y-m-d H:i:s",strtotime($user_query['user_account']) + ($recharge_query['recharge_paynum']*3600));
            }
        }else {
            //次数 金钱
            if (floatval($user_query['user_account']) <= 0){
                $user_account = $recharge_query['recharge_paynum'];
            }else{
                $user_account = floatval($user_query['user_account'])+$recharge_query['recharge_paynum'];
            }
            
        }
        $face =  $recharge->update(array("recharge_usetime"=>time(),"recharge_user"=>$recharge_user,"recharge_useitem"=>$Moog['item']['id']),"id={$recharge_query['id']}");

        if ($face){
            $falg = $user->update(array("user_loginNum"=>0,"user_account"=>$user_account,"user_frozen"=>1),"id={$user_query['id']}");
        }
        
        if ($falg){
            json(1, '充值完成,当前账户剩余:'.$user_account, null);
        }else{
            json(6, '充值失败', null);
        }
        
    }
    
    //解除机器绑定
    function relievebind(){
        //初始化软件  data : 0 key 1 账号  2 密码
        $Moog = $this->BS('item');
        //检测设备是否可以更换
        if ($Moog['item']['item_deviceEditus'] != 1) json(6, '不允许更换设备', null);
        //连接用户信息
        $_user = $Moog['item']['id'] . '-' . $Moog['data'][1];
        $where = '';
        $Fnd = "and user_itemid={$Moog['item']['id']}";
        $key = userkey($Moog['data'][1]);
        if (empty($key)) json(6, '账号输入不正确', null);
        $where = "{$key}='{$_user}' $Fnd";
        //初始化user
        $user = M("user");
        //获取用户数据
        $user_query = $user->select($where)[0];
        if (!is_array($user_query)) json(6, '用户未找到', NULL);
        $user_password = md5($Moog['data'][2] . $user_query['user_rand']);
        if ($user_password != $user_query['user_password']) json(6, '密码错误', null);
        if ($user_query['user_device'] == '0') json(6, '当前账号未绑定设备', NULL);
        //获取扣除
        if ($Moog['item']['item_doType'] == 1){
            //时间
            $user_account = date("Y-m-d H:i:s",strtotime($user_query['user_account'])-($Moog['item']['item_deviceEdit']*3600));
            if (strtotime($user_account) <= time()){
                json(6, '当前剩余时间不足解绑', null);
            }
        }else{
            $user_account = floatval($user_query['user_account'])-$Moog['item']['item_deviceEdit'];
            if ($user_account <= 0){
                json(6, '当前账户剩余不足解绑', null);
            }
        }
        $flag = $user->update(array("user_device"=>0,"user_account"=>$user_account),"id={$user_query['id']}");
        
        if ($flag){
            json(1, '解绑成功 ', null);
        }else{
            json(6, '解绑失败', null);
        }
    }
    
    //关联其他账户
    function bind(){
            // 初始化软件 data : 0 key 1 需要绑定的账号 2 验证码（手机和邮箱需要验证码）
        $Moog = $this->BS('item');
        if ($Moog['item']['item_loginType'] == 1) {
            $app_user = explode(",", cookie('APP_USERNAME'));
            if (empty($app_user))
                json(6, '未检测到用户信息,请重新登录', null);
            $user = M("user"); // 初始化user
                               // 初始化
            $hook = array(
                "user_username",
                "user_mobile",
                "user_email"
            );
            // 检测是否有人破解cookies
            if (! in_array($app_user[0], $hook))
                json(6, '数据异常,请重新登录', null);
                // 读取用户数据
            $user_query = $user->select("{$app_user[0]} = '{$app_user[1]}'")[0];
            // 检测用户是否存在
            if (! is_array($user_query))
                json(6, '当前用户信息已被更改,请重新登录', NULL);
                // 通过token解密session
            $app_session = explode(",", moog(cookie('APP_SESSION'), 1, $user_query['user_key'], 0));
            // 检测密码是否被改
            if ($user_query['user_password'] != $app_session[0])
                json(6, '当前密码已经被更改,请重新登录', null);
                // 检测账号类型
            $type = userkey(trim($Moog['data'][1]));
            $user_ueditnum = $user_query['user_ueditnum'];
            if ($type == $app_user[0])
                json(6, '当前账号已经设置,请勿重复设置', null);
            if (! $type)
                json(6, '当前账号格式不正确', NULL);
                // 检测类型 用户名
            if ($type == $hook[0]) {
                // 检测修改次数
                if ($user_query['user_ueditnum'] > 0)
                    json(6, '您已经修改过一次用户名了,不能再修改了', null);
                $user_username = $Moog['item']['id'] . '-' . $Moog['data'][1];
                $fituser = $user->select("user_username = '{$user_username}' and user_itemid={$Moog['item']['id']}")[0];
                if (is_array($fituser))
                    json(6, '该用户名已经存在', NULL);
                $user_ueditnum = $user_ueditnum + 1;
                $username = $Moog['data'][1];
            }
            // 检测类型 邮箱
            if ($type == $hook[2]) {
                // 获取cookies
                $editmailcode = explode(",", decrypt(cookie('_usereditmailcode'))); // 获取邮箱验证码cookie
                
                if ($Moog['item']['item_regEmail'] != 1)
                    json(6, '邮箱未开启', null);
                
                if ($Moog['data'][2] != $editmailcode[0] || empty($Moog['data'][2]))
                    json(6, '邮箱验证码不正确', null);
                $username = $editmailcode[1];
            }
            // 检测类型 手机
            if ($type == $hook[1]) {
                // 获取cookies
                $usereditmobilecode = explode(",", decrypt(cookie('_usereditmobilecode'))); // 获取邮箱验证码cookie
                
                if ($Moog['item']['item_regMobile'] != 1)
                    json(6, '手机未开启', null);
                
                if ($Moog['data'][2] != $usereditmobilecode[0] || empty($Moog['data'][2]))
                    json(6, '手机验证码不正确', null);
                $username = $usereditmobilecode[1];
            }
            
            $edit = $user->update(array(
                $type => $Moog['item']['id'] . '-' . $username,
                "user_ueditnum" => $user_ueditnum
            ), "id={$user_query['id']}");
            
            if ($edit) {
                json(1, '关联成功,现在你可以使用该账号登录', null);
            } else {
                json(6, '关联失败', null);
            }
        }
            
    }
    
    //发送关联邮件
    function mailsend_useredit(){
        //初始化软件
        $Moog = $this->BS('item');
        //data 0 软件key  1 邮箱地址
        //初始化user数据库
        if (!is_email($Moog['data'][1])) json(6, '邮箱输入不正确', null);
        $cookies = cookie("APP_SESSION_TIME");
        if (!empty($cookies)) json(6, '验证码发送过于频繁!', null);
        $user = M("user");
        $user_email = $Moog['item']['id'] . '-' .$Moog['data'][1];
        $user_query = $user->select("user_email = '{$user_email}' and user_itemid={$Moog['item']['id']}")[0];
        if (is_array($user_query)) json(6, '该邮箱已经被绑定过了', null);
        $regmailcode = rand(100000, 999999);
        $times = date("Y-m-d H:i:s",time());
        $falg = mailSend($Moog['data'][1], "关联账号!这是您的验证码", "<meta charset='utf-8'><div id='contentDiv0' style='background: #fff; padding-bottom: 20px; zoom: 1; position: relative; z-index: 1;' class='qm_bigsize qm_converstaion_body body qmbox qqmail_webmail_only'>		<table width='100%' border='0' cellpadding='10' cellspacing='0'			style='font-family: 微软雅黑; line-height: 1.6; font-size: 12px'>			<tbody>				<tr>					<td style='background: #cd9c56; width: 180px; text-align: center'><span						style='color: #fff; text-decoration: none; font-size: 16px; font-weight: 800'>邮箱关联验证码</span></td>					<td style='background: #eeece3'></td>				</tr>				<tr style='background: #fff; font-size: 14px'>					<td colspan='2'><p>							您好，<b>{$Moog['data'][1]}</b>：<br>					</p>						<div style='margin: 10px 0; padding: 10px; background: #FFFADF; border-left: 5px solid #FFD324'>							{$regmailcode}						</div></td>				</tr>				<tr>					<td colspan='2' style='background: #eeece3; color: #999'>发件时间：<span style='border-bottom: 1px dashed #ccc;'>{$times}</span>&nbsp;&nbsp;&nbsp;&nbsp;此邮件为系统自动发出，请勿直接回复。</td></tr></tbody></table><style type='text/css'>.qmbox style, .qmbox script, .qmbox head, .qmbox link, .qmbox meta {	display: none !important;}</style></div>");
        if($falg){
            setcookie("_usereditmailcode", encrypt($regmailcode.",".$Moog['data'][1]), time() + 270, "/"); // 设置用户cookie 验证码 270秒后过期
            setcookie("APP_SESSION_TIME", '爱只能遇见，不能预见。 ', time() + 60, "/"); // 设置用户cookie 验证码 60秒后过期
            json(1, '邮箱验证码发送成功', null);
        }else{
            json(6, '邮箱验证码发送失败', null);
        }
    }
    //发送绑定账号短信
    function phone_useredit(){
        //初始化软件
        $Moog = $this->BS('item');
        //data 0 软件key  1 手机
        //初始化user数据库
        if (!is_mobile($Moog['data'][1])) json(6, '手机输入不正确', null);
        $cookies = cookie("PHONE_SESSION_TIME");
        if (!empty($cookies)) json(6, '验证码发送过于频繁!', null);
        $user = M("user");
        $user_mobile = $Moog['item']['id'] . '-' .$Moog['data'][1];
        $user_query = $user->select("user_mobile = '{$user_mobile}' and user_itemid={$Moog['item']['id']}")[0];
        if (is_array($user_query)) json(6, '该手机号码已被绑定过了', null);
        $regmobilecode = rand(100000, 999999);
        $falg = mobilesend($Moog['data'][1], "您的验证码是：{$regmobilecode}。请不要把验证码泄露给其他人。");
        if($falg){
            setcookie("_usereditmobilecode", encrypt($regmobilecode.",".$Moog['data'][1]), time() + 270, "/"); // 设置用户cookie 验证码 270秒后过期
            setcookie("PHONE_SESSION_TIME", '爱只能遇见，不能预见。 ', time() + 60, "/"); // 设置用户cookie 验证码 60秒后过期
            json(1, '手机验证码发送成功', null);
        }else{
            json(6, '手机验证码发送失败', null);
        }
    }
    
    //登录
    function login(){
        //初始化软件  data : 0 key 1 账号  2 密码  3 机器码 4 图片验证码
        $Moog = $this->BS('item');
        if ($Moog['item']['item_loginCode'] == 1){
            $imgcode = decrypt(cookie('_imgcode'));//获取验证码
            if ($Moog['data'][4] != $imgcode || $Moog['data'][4]=='') json(6, '验证码输入错误', null);
        }
        //开始账号登录
        if ($Moog['item']['item_loginType'] == 1){
            $ovh_user = $Moog['item']['id'] . '-' . $Moog['data'][1];
            $where = '';
            $Fnd = "and user_itemid={$Moog['item']['id']}";
            //进行账号判断
            $key = userkey($Moog['data'][1]);
            if (empty($key)) json(6, '账号输入不正确', null);
            $where = "{$key}='{$ovh_user}' $Fnd";
            //初始化user
            $user = M("user");
            //获取用户数据
            $user_query = $user->select($where)[0];
            if (!is_array($user_query)) json(6, '用户未找到', NULL);
            //设置密码
            $pass = md5($Moog['data'][2] . $user_query['user_rand']);
            //检测密码是否正确
            if ($pass != $user_query['user_password']) json(6, '密码错误', null);
            //检测账号到期
            if ($Moog['item']['item_doType'] == 1){
                if (strtotime($user_query['user_account']) <= time()) json(6, '账户已到期,请充值后再登录', null);
            }
            //检测账号次数
            if ($Moog['item']['item_doType'] == 2){
                if (intval($user_query['user_account']) <= 0) json(6, '次数已经用完,请充值后再登录', null);
            }
            //检测账号冻结
            if ($user_query['user_frozen'] != 1) json(6, '当前账户已被冻结,无法进行登录,请充值后自动解冻', null);
            //检测异地登录
            if ($Moog['item']['item_loginField'] == 1){
                if ($user_query['user_loginip'] != 0){
                    if (getallopatry(getip()) != getallopatry($user_query['user_loginip'])) json(6, '当前登录地点异常,已被保护,请修改密码后解除', null);
                }
            }
            //检测机器码
            $device = $user_query['user_device'];//原机器码
            if ($Moog['item']['item_device'] == 1){
                if ($device != '0'){
                    if ($user_query['user_device'] != $Moog['data'][3]) json(6, '该账号已绑定过其他设备,请解除绑定后再进行登录', null);
                }else{
                    $device = $Moog['data'][3];//当无机器码的时候进行绑定
                }
            }
            //检测账号是否在线
            if ($Moog['item']['item_online'] == 1){
                if (time()-intval($user_query['user_dynamic']) < $Moog['item']['item_heartbeat']) json(6, '当前账号已经在线,请勿重复登录', null);
            }
            //检测账号登录次数
            $user_loginNum = $user_query['user_loginNum'] + 1;
            if ($Moog['item']['item_loginDay'] != 0){
                if ($user_query['user_loginTime'] != 0){
                    if (date("Y-m-d",$user_query['user_loginTime']) == date("Y-m-d",time())){
                        if ($Moog['item']['item_loginDay'] <= $user_query['user_loginNum']) json(6, '今天登录次数已超过限制,请充值后自动归零,或者明日再登录', null);
                    }else{
                        $user_loginNum = 1;
                    }
                }
            }
            $token = get_rand(10);
            if ($Moog['item']['item_doType'] == 2){
                $array = array(
                    "user_device"=>$device,
                    "user_loginip"=>getip(),
                    "user_loginTime"=>time(),
                    "user_loginNum"=>$user_loginNum,
                    "user_account"=>intval($user_query['user_account']-1),
                    "user_dynamic"=>time(),
                    "user_key"=>$token
                );
            }else{
                $array = array(
                    "user_device"=>$device,
                    "user_loginip"=>getip(),
                    "user_loginTime"=>time(),
                    "user_loginNum"=>$user_loginNum,
                    "user_dynamic"=>time(),
                    "user_key"=>$token
                );
            }
            //开始写入登录数据
            $falg = $user->update($array,"id={$user_query['id']}");
            if ($falg){
                setcookie("APP_USERNAME","{$key},{$ovh_user}", time() + 300, "/"); // 设置用户cookie 验证码 270秒后过期
                setcookie("APP_SESSION",moog("{$user_query['user_password']},{$device}",2,$token,$Moog['item']['item_heartbeat']+120), time() + 300, "/"); // 设置用户cookie 验证码 270秒后过期
                json(1, '登录成功,当前账户剩余:'.$user_query['user_account'], null);
            }else{
                json(6, '登录失败', null);
            }
        }else{
            //开始充值卡登录
            $where = "recharge_cami='{$Moog['data'][1]}' and recharge_itemid like '%{$Moog['item']['id']}%'";
            //初始化recharge
            $recharge = M('recharge');
            //查询数据库
            $recharge_query = $recharge->select($where)[0];
            //判断
            if (!is_array($recharge_query)) json(6, '未找到该卡密', null);
            //判断是否冻结
            if ($recharge_query['recharge_frozen'] != 1) json(6, '卡密已被冻结', null);
            //判断是否用户登录
            if ($recharge_query['recharge_logint'] != 2) json(6, '该卡密不能登录', null);
            //未使用
            if ($recharge_query['recharge_user'] == '0'){
                $array = array(
                    "recharge_usetime"=>time(),
                    "recharge_user"=>'LoginCard',
                    "recharge_useitem"=>$Moog['item']['id'],
                    "recharge_device"=>$Moog['data'][3]
                );
                $flag = $recharge->update($array,"id={$recharge_query['id']}");
                json(1, '登录成功,到期时间:'.date("Y-m-d H:i:s",time()+($recharge_query['recharge_paynum']*3600)), null);
            }else{
                //到期时间检测
                if ($recharge_query['recharge_usetime']+($recharge_query['recharge_paynum']*3600) < time()) json(6, '卡密已经到期', null);
                //机器码检测
                if ($recharge_query['recharge_device'] != $Moog['data'][3] && $recharge_query['recharge_device'] != '0') json(6, '机器码不符合,无法登录', null);
                
                json(1, '登录成功,到期时间:'.date("Y-m-d H:i:s",$recharge_query['recharge_usetime']+($recharge_query['recharge_paynum']*3600)), null);
            }
        }
        
        
    }
    
    //发送心跳数据
    function heartbeat(){
        //初始化软件  data : 0 key
        $Moog = $this->BS('item');
        //检测软件是否停用
        if ($Moog['item']['item_open'] != 1) json(6, '软件已关闭', null);
        // 
        if ($Moog['item']['item_loginType'] == 1){
            
            if ($Moog['item']['item_online'] == 1){
                $app_user = explode(",", cookie('APP_USERNAME'));
                if (empty($app_user)) json(6, '未检测到用户信息,请重新登录', null);
                $user = M("user");//初始化user
                //初始化
                $hook = array("user_username","user_mobile","user_email");
                //检测是否有人破解cookies
                if (!in_array($app_user[0], $hook)) json(6, '数据异常,请重新登录', null);
                //读取用户数据
                $user_query = $user->select("{$app_user[0]} = '{$app_user[1]}'")[0];
                //检测用户是否存在
                if (!is_array($user_query)) json(6, '当前用户信息已被更改,请重新登录', NULL);
                //通过token解密session
                $app_session = explode(",", moog(cookie('APP_SESSION'),1,$user_query['user_key'],0));
                //检测密码是否被改
                if ($user_query['user_password'] != $app_session[0]) json(6, '当前密码已经被更改,请重新登录', null);
                //检测机器码是否正常
                if ($Moog['item']['item_device'] == 1){
                    if ($user_query['user_device'] != $app_session[1]) json(6, '当前设备异常,请重新登录', null);
                }
                //检测是否到期
                if ($Moog['item']['item_doType'] == 1){
                    if (strtotime($user_query['user_account']) <= time()) json(6, '账户已到期,请充值后再登录', null);
                }
                //检测账号冻结
                if ($user_query['user_frozen'] != 1) json(6, '当前账户已被冻结,无法进行登录,请充值后自动解冻', null);
                //检测用户是否掉线
                if (time()-intval($user_query['user_dynamic']) > $Moog['item']['item_heartbeat']) json(6, '当前账户为离线状态,请重新登录', null);
                //动态更新
                $send = $user->update(array("user_dynamic"=>time(),"user_message"=>''),"id={$user_query['id']}");
                //返回数据
                if ($send){
                    setcookie("APP_USERNAME","{$app_user[0]},{$app_user[1]}", time() + 300, "/"); // 设置用户cookie 验证码 270秒后过期
                    setcookie("APP_SESSION",moog("{$user_query['user_password']},{$user_query['user_device']}",2,$user_query['user_key'],$Moog['item']['item_heartbeat']+120), time() + 300, "/"); // 设置用户cookie 验证码 270秒后过期
                    json(1, 'read..', array("account"=>$user_query['account'],"message"=>$user_query['user_message']));
                }else {
                    json(6, '心跳数据更新失败', null);
                }
            }else{
                json(1, 'close..', null);
            }
            
        }else{
            json(1, 'close..', null);
        }
        
        
    }
    
    //账号冻结账号接口
    function ban(){
        //初始化软件  data : 0 key 1 账号  2 密码 
        $Moog = $this->BS('item');
        if ($Moog['item']['item_loginType'] == 1){
            $ovh_user = $Moog['item']['id'] . '-' . $Moog['data'][1];
            $where = '';
            $Fnd = "and user_itemid={$Moog['item']['id']}";
            //进行账号判断
            $key = userkey($Moog['data'][1]);
            if (empty($key)) json(6, '账号输入不正确', null);
            $where = "{$key}='{$ovh_user}' $Fnd";
            //初始化user
            $user = M("user");
            //获取用户数据
            $user_query = $user->select($where)[0];
            if (!is_array($user_query)) json(6, '用户未找到', NULL);
            //设置密码
            $pass = md5($Moog['data'][2] . $user_query['user_rand']);
            //检测密码是否正确
            if ($pass != $user_query['user_password']) json(6, '密码错误', null);
            
            $fit = $user->update(array("user_frozen"=>2),"id={$user_query['id']}");
            
            if ($fit){
                json(1, '账号已冻结', NULL);
            }else{
                json(6, '操作失败', null);
            }
        }        
    }
    
    //获取公共数据
    function getcommon(){
        //初始化软件  data : 0 key
        $Moog = $this->BS('item');
        $array = array(
            "heartbeat"=>$Moog['item']['item_heartbeat'],//心跳监控秒数
            "regNum"=>$Moog['item']['item_regGive'],//注册赠送时间
            "version"=>$Moog['item']['item_version'],//版本号
            "update_content"=>$Moog['item']['item_verUpdate'],//新版本更新内容
            "update_url"=>$Moog['item']['item_verUrl'],//更新下载地址
            "notice"=>$Moog['item']['item_notice'],//公告
            "update_force"=>$Moog['item']['item_verForce'],//强制更新 1强制更新  2 不强制更新
            "open"=>$Moog['item']['item_open']  //软件开关  1开启   2关闭
        );
        json(1, '服务器连接成功', $array);
    }
    
    //获取user数据
    function user(){
        //初始化软件  data : 0 key
        $Moog = $this->BS('item');
        if ($Moog['item']['item_loginType'] == 1){
            $app_user = explode(",", cookie('APP_USERNAME'));
            if (empty($app_user)) json(6, '未检测到用户信息,请重新登录', null);
            $user = M("user");//初始化user
            //初始化
            $hook = array("user_username","user_mobile","user_email");
            //检测是否有人破解cookies
            if (!in_array($app_user[0], $hook)) json(6, '数据异常,请重新登录', null);
            //读取用户数据
            $user_query = $user->select("{$app_user[0]} = '{$app_user[1]}'")[0];
            //检测用户是否存在
            if (!is_array($user_query)) json(6, '当前用户信息已被更改,请重新登录', NULL);
            //通过token解密session
            $app_session = explode(",", moog(cookie('APP_SESSION'),1,$user_query['user_key'],0));
            //检测密码是否被改
            if ($user_query['user_password'] != $app_session[0]) json(6, '当前密码已经被更改,请重新登录', null);
            //检测机器码是否正常
            if ($Moog['item']['item_device'] == 1){
                if ($user_query['user_device'] != $app_session[1]) json(6, '当前设备异常,请重新登录', null);
            }
            //检测是否到期
            if ($Moog['item']['item_doType'] == 1){
                if (strtotime($user_query['user_account']) <= time()) json(6, '账户已到期,请充值后再登录', null);
            }
            //检测账号冻结
            if ($user_query['user_frozen'] != 1) json(6, '当前账户已被冻结,请充值后自动解冻', null);
            //检测用户是否掉线
            if (time()-intval($user_query['user_dynamic']) > $Moog['item']['item_heartbeat']) json(6, '当前账户为离线状态,请重新登录', null);
            //数据获取
            $array = array(
                "username"=>str_replace($Moog['item']['id'].'-', '', $user_query['user_username']), //用户名
                "mobile"=>is_mobile(str_replace($Moog['item']['id'].'-', '', $user_query['user_mobile'])) ? str_replace($Moog['item']['id'].'-', '', $user_query['user_mobile']) :'未绑定手机号',  //手机号
                "email"=>is_email(str_replace($Moog['item']['id'].'-', '', $user_query['user_email']))?str_replace($Moog['item']['id'].'-', '', $user_query['user_email']):'未绑定邮箱',    //邮箱账号
                "loginip"=>$user_query['user_loginip'], //登录IP
                "loginloc"=>getallopatry($user_query['user_loginip']), //登录地点
                "regtime"=>date("Y-m-d H:i:s",$user_query['user_regTime']),//注册时间
                "account"=>$user_query['user_account'], //账户余额
                "login_num"=>$user_query['user_loginNum'],//今日登陆次数
                "token"=>$user_query['user_key'],//账户token
                "mode"=>str_replace("user_", "", $app_user[0]), //返回当前登录的类型
                "powerchange"=>$user_query['user_ueditnum'], //返回用户名修改次数
                "imgurl"=>$user_query['user_imgurl'] //返回头像地址
            );
            //返回数据
            json(1, 'read...', $array);
        }
    }
    
    //上传头像
    function imguoload(){
        //初始化软件  data : 0 key 1图片地址
        $Moog = $this->BS('item');
        if ($Moog['item']['item_loginType'] == 1){
            $app_user = explode(",", cookie('APP_USERNAME'));
            if (empty($app_user)) json(6, '未检测到用户信息,请重新登录', null);
            $user = M("user");//初始化user
            //初始化
            $hook = array("user_username","user_mobile","user_email");
            //检测是否有人破解cookies
            if (!in_array($app_user[0], $hook)) json(6, '数据异常,请重新登录', null);
            //读取用户数据
            $user_query = $user->select("{$app_user[0]} = '{$app_user[1]}'")[0];
            //检测用户是否存在
            if (!is_array($user_query)) json(6, '当前用户信息已被更改,请重新登录', NULL);
            //通过token解密session
            $app_session = explode(",", moog(cookie('APP_SESSION'),1,$user_query['user_key'],0));
            //检测密码是否被改
            if ($user_query['user_password'] != $app_session[0]) json(6, '当前密码已经被更改,请重新登录', null);
            //检测机器码是否正常
            if ($Moog['item']['item_device'] == 1){
                if ($user_query['user_device'] != $app_session[1]) json(6, '当前设备异常,请重新登录', null);
            }
            //检测是否到期
            if ($Moog['item']['item_doType'] == 1){
                if (strtotime($user_query['user_account']) <= time()) json(6, '账户已到期,请充值后再登录', null);
            }
            //检测账号冻结
            if ($user_query['user_frozen'] != 1) json(6, '当前账户已被冻结,请充值后自动解冻', null);
            //检测用户是否掉线
            if (time()-intval($user_query['user_dynamic']) > $Moog['item']['item_heartbeat']) json(6, '当前账户为离线状态,请重新登录', null);
            
            $flag = $user->update(array("user_imgurl"=>$Moog['data'][1]),"id={$user_query['id']}");
            
            if ($flag){
                json(1, '上传成功', null);
            }else{
                json(6, '上传失败', null);
            }
            
        }
    }
    
    //账号退出
    function signout(){
        //初始化软件  data : 0 key
        $Moog = $this->BS('item');
        $app_user = explode(",", cookie('APP_USERNAME'));
        if (empty($app_user)) json(6, '未检测到用户信息', null);
        $user = M("user");//初始化user
        //初始化
        $hook = array("user_username","user_mobile","user_email");
        //检测是否有人破解cookies
        if (!in_array($app_user[0], $hook)) json(6, '数据异常', null);
        
        $flag = $user->update(array("user_dynamic"=>0),"{$app_user[0]}='{$app_user[1]}'");
        
        if ($flag){
            setcookie("APP_USERNAME",'', time(), "/"); // 设置用户cookie 验证码 270秒后过期
            setcookie("APP_SESSION",'', time(), "/"); // 设置用户cookie 验证码 270秒后过期
            json(1, '已经安全退出', null);
        }else{          
            json(6, '离线失败', null);
        }
    }
    
    //充值卡查询
    function querycard(){
        //初始化软件  data : 0 key 1充值卡
        $Moog = $this->BS('item');
        $recharge = M("recharge")->select("recharge_cami='{$Moog['data'][1]}' and recharge_itemid like '%{$Moog['item']['id']}%'")[0];
        if (!is_array($recharge)) json(6, '未查询到该充值卡', null);
        $array = array(
            "balance"=>$recharge['recharge_paynum'],
            "frozen"=>$recharge['recharge_frozen'] == 1 ?'未冻结':'已冻结',
            "logint"=>$recharge['recharge_logint'] == 1 ?'用户充值类型':'卡密登录类型',
            "usetime"=>$recharge['recharge_usetime'] == '0' ?'未使用':date("Y-m-d H:i:s",$recharge['recharge_usetime']),
            "user"=>$recharge['recharge_user'] == '0' ? '未使用' : str_replace($Moog['item']['id'].'-', '', $recharge['recharge_user'])
        );
        json(1, '查询成功', $array);
    }
    
    //获取商品分类
    function shoprt_parent(){
        //初始化软件  data : 0 key
        $Moog = $this->BS('item');
        $app_user = explode(",", cookie('APP_USERNAME'));
        if (empty($app_user)) json(6, '未检测到用户信息', null);
        $user = M("user");//初始化user
        //初始化
        $hook = array("user_username","user_mobile","user_email");
        //检测是否有人破解cookies
        if (!in_array($app_user[0], $hook)) json(6, '数据异常', null);
        $shoprt = M("shoprt");
        $querycount = $shoprt->sql("select count(id) as c from god_shoprt where shoprt_pid=0 and shoprt_itemid like '%{$Moog['item']['id']}%' and shoprt_display=1")[0]['c'];//所有记录数量
        $shoprt_query = $shoprt->select("shoprt_pid=0 and shoprt_itemid like '%{$Moog['item']['id']}%' and shoprt_display=1",null,null,null,"id,shoprt_name,shoprt_pid");
        json(1, '商品分类获取完毕', array("count"=>$querycount,"query"=>$shoprt_query));
    }
    
   //获取商品子分类
   function shoprt_children(){
       //初始化软件  data : 0 key 1 顶级分类的ID
       $Moog = $this->BS('item');
       $app_user = explode(",", cookie('APP_USERNAME'));
       if (empty($app_user)) json(6, '未检测到用户信息', null);
       $user = M("user");//初始化user
       //初始化
       $hook = array("user_username","user_mobile","user_email");
       //检测是否有人破解cookies
       if (!in_array($app_user[0], $hook)) json(6, '数据异常', null);
       $shoprt = M("shoprt");
       $querycount = $shoprt->sql("select count(id) as c from god_shoprt where shoprt_pid={$Moog['data'][1]} and shoprt_itemid like '%{$Moog['item']['id']}%' and shoprt_display=1")[0]['c'];//所有记录数量
       $shoprt_query = $shoprt->select("shoprt_pid={$Moog['data'][1]} and shoprt_itemid like '%{$Moog['item']['id']}%' and shoprt_display=1",null,null,null,"id,shoprt_name,shoprt_pid");
       json(1, '子分类获取完毕', array("count"=>$querycount,"query"=>$shoprt_query));
   }
   //获取商品 
   function shop(){
       //初始化软件  data : 0 key 1 分类ID 2自动发货赛选  3当前page
       $Moog = $this->BS('item');
       $app_user = explode(",", cookie('APP_USERNAME'));
       if (empty($app_user)) json(6, '未检测到用户信息', null);
       $user = M("user");//初始化user
       //初始化
       $hook = array("user_username","user_mobile","user_email");
       //检测是否有人破解cookies
       if (!in_array($app_user[0], $hook)) json(6, '数据异常', null);
       //初始化商城系统
       $shop = M("shop");
       //搜索功能 商品名称
       $search = post('search');
       //自动发货 1手动发货 2自动发货
       $frozen = $Moog['data'][2];
       $where = "shop_rtid={$Moog['data'][1]}";
       $frozenAnd = "and";

       if (!empty($frozen)){
           //未冻结
           if ($frozen == 1){
               $where = "shop_type=1 $frozenAnd $where";
           }
           //已冻结
           if ($frozen == 2){
               $where = "shop_type=2 $frozenAnd $where";
           }
       }
       if (!empty($search)){
           $where = "shop_name like '%{$search}%' $frozenAnd $where";
       }
       //翻页功能
       $page = $Moog['data'][3];
       $pageNum = 15;//初始化每页显示数量15
       //为了防止冲突，这里重新定义where
       if ($where != null){
           $countwhere = "where ". $where;
       }
       $queryCount = $shop->sql("select count(id) as c from god_shop {$countwhere}")[0]['c'];//所有记录数量
       $pageCount = ceil($queryCount / $pageNum);//总页数
       if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
       if ($page <= 1) $page = 1;//计算当前页面小于1
       $pageMy = ($page-1) * $pageNum;//计算当前页码
       $query = $shop->select($where,null,"id","$pageMy,$pageNum","id,shop_name,shop_type,shop_price,shop_present,shop_imgurl,shop_cnum");
       $array = array(
           "pagecount"=>$pageCount,//总页数
           "page"=>$page,//当前页码
           "count"=>count($query), //当前商品数量
           "query"=>$query  
       );
       json(1, '商品获取成功', $array);
   }
   //购买商品
   function buy(){
       //初始化软件  data : 0 key 1商品ID  2购买数量   address post 发货地址
       $Moog = $this->BS('item');
       $app_user = explode(",", cookie('APP_USERNAME'));
        if (empty($app_user))
            json(6, '未检测到用户信息,请重新登录', null);
        $user = M("user"); // 初始化user
                           // 初始化
        $hook = array(
            "user_username",
            "user_mobile",
            "user_email"
        );
        // 检测是否有人破解cookies
        if (! in_array($app_user[0], $hook))
            json(6, '数据异常,请重新登录', null);
            // 读取用户数据
        $user_query = $user->select("{$app_user[0]} = '{$app_user[1]}'")[0];
        // 检测用户是否存在
        if (! is_array($user_query))
            json(6, '当前用户信息已被更改,请重新登录', NULL);
            // 通过token解密session
        $app_session = explode(",", moog(cookie('APP_SESSION'), 1, $user_query['user_key'], 0));
        // 检测密码是否被改
        if ($user_query['user_password'] != $app_session[0])
            json(6, '当前密码已经被更改,请重新登录', null);
            // 检测机器码是否正常
        if ($Moog['item']['item_device'] == 1) {
            if ($user_query['user_device'] != $app_session[1])
                json(6, '当前设备异常,请重新登录', null);
        }
        // 检测账号冻结
        if ($user_query['user_frozen'] != 1)
            json(6, '当前账户已被冻结,请充值后自动解冻', null);
            // 检测用户是否掉线
        if (time() - intval($user_query['user_dynamic']) > $Moog['item']['item_heartbeat'])
            json(6, '当前账户为离线状态,请重新登录', null);
            // 初始化商城系统
        $shop = M("shop");
        //检测商品是否存在
        $shop_query = $shop->select("id={$Moog['data'][1]}")[0];
        if (!is_array($shop_query)) json(6, '当前商品不存在', null);
        //自动发货判断无库存
        $shop_content = $shop_query['shop_content'];
        if ($shop_query['shop_type'] == 2){
            //分割成数组
            $shop_content_array = explode(PHP_EOL, $shop_content);
            //判断是否有库存
            $shopbuy_deliver = '';
            if ($shop_query['shop_cnum'] < $Moog['data'][2]){
                json(6, '当前库存不足', null);
            }else{
                //返回个用户的文本  写入发货信息的文本
                for ($i=0;$i<$Moog['data'][2];$i++){
                    $shopbuy_deliver .= $shop_content_array[$i] . PHP_EOL;
                }
                //删除已购买的信息
                array_splice($shop_content_array, 0, $Moog['data'][2]);
                //重新获取新的元素
                $shop_content = implode(PHP_EOL, $shop_content_array);
            }
            $array = array(
                "shopbuy_serial"=>date("YmdHis",time()).time().rand(100000, 999999),
                "shopbuy_shopname"=>$shop_query['shop_name'],
                "shopbuy_sort"=>M('shoprt')->select("id={$shop_query['shop_rtid']}")[0]['shoprt_name'],
                "shopbuy_num"=>$Moog['data'][2],
                "shopbuy_moeny"=>$shop_query['shop_price'],
                "shopbuy_total"=>$shop_query['shop_price']*intval($Moog['data'][2]),
                "shopbuy_username"=>$user_query['id'],
                "shopbuy_address"=>post('address'),
                "shopbuy_goods"=>1,
                "shopbuy_deliver"=>$shopbuy_deliver,
                "shopbuy_type"=>2,
                "shopbuy_ctime"=>time(),
                "shopbuy_ftime"=>time()
            );
        }else{
            $shopbuy_deliver = "等待发货";
            $array = array(
                "shopbuy_serial"=>date("YmdHis",time()).time().rand(100000, 999999),
                "shopbuy_shopname"=>$shop_query['shop_name'],
                "shopbuy_sort"=>M('shoprt')->select("id={$shop_query['shop_rtid']}")[0]['shoprt_name'],
                "shopbuy_num"=>$Moog['data'][2],
                "shopbuy_moeny"=>$shop_query['shop_price'],
                "shopbuy_total"=>$shop_query['shop_price']*intval($Moog['data'][2]),
                "shopbuy_username"=>$user_query['id'],
                "shopbuy_address"=>post('address'),
                "shopbuy_goods"=>2,
                "shopbuy_deliver"=>'等待发货',
                "shopbuy_type"=>1,
                "shopbuy_ctime"=>time()
            );
        }
        //判断自己身上有没有钱买
        $user_account = floatval($user_query['user_account'])-($shop_query['shop_price']*intval($Moog['data'][2]));
        if ($user_account < 0) json(6, '当前账户余额不足,无法完成购买,请充值后再进行购买', null);
        $buy = $user->update(array("user_account"=>$user_account),"id={$user_query['id']}");
        if (!$buy) json(6, '购买失败,请重试', null);
        $push = M('shopbuy')->insert($array);
        if (!$push) json(6, '创建订单失败!', $array);
        $shop_update = $shop->update(array("shop_content"=>$shop_content,"shop_cnum"=>count($shop_content_array)),"id={$shop_query['id']}");
        if ($shop_update){
            json(1, '购买成功', array("deliver"=>$shopbuy_deliver));
        }else{
            json(6, '购买失败', null);
        }
   }
    //获取个人订单
    function myorder(){
        //初始化软件  data : 0 key 1发货状态（1已发货 2未发货）  2翻页
        $Moog = $this->BS('item');
        $app_user = explode(",", cookie('APP_USERNAME'));
        if (empty($app_user))
            json(6, '未检测到用户信息,请重新登录', null);
        $user = M("user"); // 初始化user
        // 初始化
        $hook = array(
            "user_username",
            "user_mobile",
            "user_email"
        );
        // 检测是否有人破解cookies
        if (! in_array($app_user[0], $hook))
            json(6, '数据异常,请重新登录', null);
        // 读取用户数据
        $user_query = $user->select("{$app_user[0]} = '{$app_user[1]}'")[0];
        // 检测用户是否存在
        if (! is_array($user_query))
            json(6, '当前用户信息已被更改,请重新登录', NULL);
        // 通过token解密session
        $app_session = explode(",", moog(cookie('APP_SESSION'), 1, $user_query['user_key'], 0));
        // 检测密码是否被改
        if ($user_query['user_password'] != $app_session[0])
            json(6, '当前密码已经被更改,请重新登录', null);
        // 检测机器码是否正常
        if ($Moog['item']['item_device'] == 1) {
            if ($user_query['user_device'] != $app_session[1])
                json(6, '当前设备异常,请重新登录', null);
        }
        // 检测账号冻结
        if ($user_query['user_frozen'] != 1)
            json(6, '当前账户已被冻结,请充值后自动解冻', null);
        // 检测用户是否掉线
        if (time() - intval($user_query['user_dynamic']) > $Moog['item']['item_heartbeat'])
            json(6, '当前账户为离线状态,请重新登录', null);
        //初始化订单中心
        $shopbuy = M('shopbuy');
        //发货状态 1 已发货  2未发货
        $frozen = $Moog['data'][1];
        $where = "shopbuy_username={$user_query['id']}";
        $frozenAnd = 'and';
        
        if (!empty($frozen)){
            //未冻结
            if ($frozen == 1){
                $where = "shopbuy_goods=1 $frozenAnd $where";
            }
            //已冻结
            if ($frozen == 2){
                $where = "shopbuy_goods=2 $frozenAnd $where";
            }
        }
        //翻页功能
        $page = $Moog['data'][2];
        $pageNum = 15;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $shopbuy->sql("select count(id) as c from god_shopbuy {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $query = $shopbuy->select($where,null,"id","$pageMy,$pageNum");
        $array = array(
            "pagecount"=>$pageCount,//总页数
            "page"=>$page,//当前页码
            "count"=>count($query), //当前商品数量
            "query"=>$query
        );
        json(1, '订单获取成功', $array);
    }
    
    //金额消费
    function consume(){
        //初始化软件  data : 0 key 1 自定义金额
        $Moog = $this->BS('item');
        $app_user = explode(",", cookie('APP_USERNAME'));
        if (empty($app_user))
            json(6, '未检测到用户信息,请重新登录', null);
        $user = M("user"); // 初始化user
        // 初始化
        $hook = array(
            "user_username",
            "user_mobile",
            "user_email"
        );
        // 检测是否有人破解cookies
        if (! in_array($app_user[0], $hook))
            json(6, '数据异常,请重新登录', null);
        // 读取用户数据
        $user_query = $user->select("{$app_user[0]} = '{$app_user[1]}'")[0];
        // 检测用户是否存在
        if (! is_array($user_query))
            json(6, '当前用户信息已被更改,请重新登录', NULL);
        // 通过token解密session
        $app_session = explode(",", moog(cookie('APP_SESSION'), 1, $user_query['user_key'], 0));
        // 检测密码是否被改
        if ($user_query['user_password'] != $app_session[0])
            json(6, '当前密码已经被更改,请重新登录', null);
        // 检测机器码是否正常
        if ($Moog['item']['item_device'] == 1) {
            if ($user_query['user_device'] != $app_session[1])
                json(6, '当前设备异常,请重新登录', null);
        }
        // 检测账号冻结
        if ($user_query['user_frozen'] != 1)
            json(6, '当前账户已被冻结,请充值后自动解冻', null);
        // 检测用户是否掉线
        if (time() - intval($user_query['user_dynamic']) > $Moog['item']['item_heartbeat'])
            json(6, '当前账户为离线状态,请重新登录', null);
        //初始化订单中心
        if (floatval($Moog['data'][1]) <= 0) json(6, '当前金额不正确', null);
        $user_account = floatval($user_query['user_account'])-floatval($Moog['data'][1]);
        //判断用户是否有钱
        if ($user_account < 0) json(6, '当前账户余额不足', NULL);
        $flag = $user->update(array("user_account"=>$user_account),"id={$user_query['id']}");
        if ($flag){
            json(1, '购买成功', null);
        }else{
            json(6, '购买失败', null);
        }
    }
    
    //动态安全数据获取
    function security(){
        //初始化软件  data : 0 key 1 数据名称
        $Moog = $this->BS('item');
        //开始获取安全数据
        $sec = M('security')->select("security_name='{$Moog['data'][1]}' and security_itemid={$Moog['item']['id']}")[0];
        if (!is_array($sec)) json(6, '当前数据不存在', NULL);
        //初始化加密token
        $token = '';
        if ($sec['security_cookies'] == 1){
            //cookies验证
            $app_user = explode(",", cookie('APP_USERNAME'));
            if (empty($app_user))
                json(6, '未检测到用户信息,请重新登录', null);
            $user = M("user"); // 初始化user
            // 初始化
            $hook = array(
                "user_username",
                "user_mobile",
                "user_email"
            );
            // 检测是否有人破解cookies
            if (! in_array($app_user[0], $hook))
                json(6, '数据异常,请重新登录', null);
            // 读取用户数据
            $user_query = $user->select("{$app_user[0]} = '{$app_user[1]}'")[0];
            // 检测用户是否存在
            if (! is_array($user_query))
                json(6, '当前用户信息已被更改,请重新登录', NULL);
            // 通过token解密session
            $app_session = explode(",", moog(cookie('APP_SESSION'), 1, $user_query['user_key'], 0));
            // 检测密码是否被改
            if ($user_query['user_password'] != $app_session[0])
                json(6, '当前密码已经被更改,请重新登录', null);
            // 检测机器码是否正常
            if ($Moog['item']['item_device'] == 1) {
                if ($user_query['user_device'] != $app_session[1])
                    json(6, '当前设备异常,请重新登录', null);
            }
            // 检测账号冻结
            if ($user_query['user_frozen'] != 1)
                json(6, '当前账户已被冻结,请充值后自动解冻', null);
            // 检测用户是否掉线
            if (time() - intval($user_query['user_dynamic']) > $Moog['item']['item_heartbeat'])
                json(6, '当前账户为离线状态,请重新登录', null);
            $token = $user_query['user_key'];//重置token
        }
        $security = $sec['security_content'];
        //开始数据加密
        if ($sec['security_encrypt'] == 1){
            $security = moog($sec['security_content'], 2,$token,intval($sec['security_cycle']));
        }
        //数组
        $array = array(
            "name"=>$sec['security_name'],
            "security"=>$security
        );
        //返回加密内容
        json(1, '数据读取成功..', $array);
        
    }
    /*
     * 广告数据获取
     * '广告类型：1、 启动图 2、平台下载3、游戏娱乐4、首页轮播图'
     *  二维数组中key代码不同类型的广告图  path字段为广告名
     *  比如： 注意获取图片时要加上 /photo/ + 广告名
     * Array
        (
            [1] => Array
                (
                    [0] => Array
                        (
                            [id] => 4
                            [name] => admin
                            [path] => 01cc075540de280000017c9453cd46.jpg
                            [type] => 1
                            [url] => http://www.hmlwan521.com
                            [sort] => 5
                            [create_time] => 1511495480
                        )

                )

            [4] => Array
                (
                    [0] => Array
                        (
                            [id] => 6
                            [name] => test1
                            [path] => 010c87554bb733000001bf72c407d8.jpg
                            [type] => 4
                            [url] => http://www.baidu.com
                            [sort] => 1
                            [create_time] => 1511505990
                        )
                )

)
     *
     * */
    function getAdvData(){
        $adv = M("advimage")->select();
        if($adv){
            $adv_str = array();
            foreach ($adv as $key => $val){
                $adv_str[$val['type']][] = $val;
            }
            json(1, '数据读取成功..', $adv_str);
        }else{
            json(6, '数据读取失败..', null);
        }

    }


   
    //公共类
    function BS($c){
        
        if($c == 'item'){
            // 0 软件KEY  
            $mod = explode(",", string_remove_xss(moog(post("data"), 1)));//将数据分割 
            
            if (empty($mod[0])) json(6, '资料填写不正确,请勿使用中文或者非法字符', null);
            
            $Item = M("item")->select("item_itkey='{$mod[0]}'")[0];
            
            if (!is_array($Item)) json(6, '连接软件数据失败', null);
            
            return array("item"=>$Item,"data"=>$mod);
        }
        
    }
    

    

}

?>