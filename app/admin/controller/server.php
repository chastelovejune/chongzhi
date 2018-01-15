<?php
namespace admin\controller;

class server
{
    
    // 登录
    function login()
    {
        // 进行token验证
        if ($_SESSION['token'] != post($_SESSION['tokenname'])){
            jp(__URL__ . "index.php/admin/index/login", 2, "Token校验失败!");
        }

        $username = post($_SESSION['username']); // 用户名

        $dblite = M("manage");
        $query = $dblite->select("man_username='$username'")[0];

        if (! $query){
            jp(__URL__ . "index.php/admin/index/login", 2, "管理员用户名错误!");
        }

        $password = md5(post($_SESSION['password']) . $query['man_token']); // 组合密码
        if ($password != $query['man_password']){
            jp(__URL__ . "index.php/admin/index/login", 2, "管理员用密码错误!");
        }
        setcookie("_u", encrypt($query['man_username'], "hello", "mengi"), time() + 3600, "/"); // 设置用户cookie
        setcookie("_p", encrypt(post($_SESSION['password']), "hello", $query['man_token']), time() + 3600, "/"); // 设置密码
        $dblite->update(array(
            "man_loginip" => getip()
        )); // 改变登录IP
        jp(__URL__ . "index.php/admin/index/home", 1, "登录成功!登录地点:" . getallopatry(getip())) . "(" . getip() . ")";
    }
    
    //修改密码
    function myEdit(){
        $this->BS();
        $user = decrypt(cookie("_u"),"hello","mengi");//取出用户名
        $dblist = M("manage");
        $query = $dblist->select("man_username='{$user}'")[0];
        if (!is_array($query)) json(6, '权限不足!', null);
        if (post('man_username') == '') json(6, '检测到空值', null);
        if (post(man_password) != ''){
            $token = get_rand(6);
            $man_password = md5(post('man_password').$token);
        }else{
            $token = $query['man_token'];
            $man_password = $query['man_password'];
        }
        
        $fa = $dblist->update(array("man_username"=>post("man_username"),"man_password"=>$man_password,"man_token"=>$token,"man_email"=>post("man_email"),"man_qq"=>post("man_qq")),"id={$query['id']}");
       
        if ($fa){
            json(1, '修改成功', null);
        }else{
            json(6, '修改失败', null);
        }
    }
    
    //退出
    function exitlogin(){
        setcookie("_u", '', time(), "/"); // 设置用户cookie
        setcookie("_p", '', time(), "/"); // 设置密码
        jp(__URL__ . "index.php/admin/index/login", 0, "注销成功!");
    }
    
    //添加软件
    function addItem(){
        $this->BS();
        $dblist = M("item");
        $falg = $dblist->insert(array(
            "item_name"=>post("item_name"),
            "item_itkey"=>strtoupper(get_rand(30))
        ));
        if ($falg){
            json(1, "添加成功!", null);
        }else{
            json(6, "添加失败!", null);
        }
    }
    
    
    
    //修改软件
    function editItem(){
        $this->BS();
        $id = get("id");
        $dblist = M("item");
        $item_regEmail = post("item_regEmail") ? 1 : 2;
        $item_regMobile = post("item_regMobile") ? 1 : 2;
        $item_regGexi = post("item_regGexi") ? 1 : 2;
        $item_regCode = post("item_regCode") ? 1 : 2;
        $item_regVeod = post("item_regVeod") ? 1 : 2;
        $item_loginExt = post("item_loginExt") ? 1 : 2;
        $item_loginField = post("item_loginField") ? 1 : 2;
        $item_loginCode = post("item_loginCode") ? 1 : 2;
        $item_online = post("item_online") ? 1 : 2;
        $item_device = post("item_device") ? 1 : 2;
        $item_deviceEditus = post("item_deviceEditus") ? 1 : 2;
        $item_regVeod = post("item_regVeod") ? 1 : 2;
        $item_verForce = post("item_verForce") ? 1 : 2;
        $item_open = post("item_open") ? 1 : 2;
        $item_shop = post("item_shop") ? 1 : 2;
        $falg = $dblist->update(array(
            "item_name"=>post("item_name"),
            "item_itkey"=>post("item_itkey"),
            "item_regEmail"=>$item_regEmail,
            "item_regMobile"=>$item_regMobile,
            "item_regGexi"=>$item_regGexi,
            "item_regCode"=>$item_regCode,
            "item_regVeod"=>$item_regVeod,
            "item_loginExt"=>$item_loginExt,
            "item_loginField"=>$item_loginField,
            "item_loginCode"=>$item_loginCode,
            "item_loginType"=>post("item_loginType"),
            "item_online"=>$item_online,
            "item_doType"=>post("item_doType"),
            "item_device"=>$item_device,
            "item_heartbeat"=>post("item_heartbeat"),
            "item_deviceEdit"=>post("item_deviceEdit"),
            "item_deviceEditus"=>$item_deviceEditus,
            "item_regipNum"=>post("item_regipNum"),
            "item_regGive"=>post("item_regGive"),
            "item_regVeod"=>$item_regVeod,
            "item_loginDay"=>post("item_loginDay"),
            "item_version"=>post("item_version"),
            "item_verUpdate"=>post("item_verUpdate"),
            "item_verUrl"=>post("item_verUrl"),
            "item_notice"=>post("item_notice"),
            "item_verForce"=>$item_verForce,
            "item_open"=>$item_open,
            "item_shop"=>$item_shop
            ),"id={$id}");
        if ($falg){
            json(1, "修改成功", null);
        }else{
            json(6, "修改失败!", null);
        }
    }
    
    //删除分类
    function delItem(){
        $this->BS();
        $dblist = M("item");
        $falg = $dblist->delete("id={$_GET['id']}");
        if ($falg){
            jp(__URL__ . "index.php/admin/index/artment", 0, "删除成功");
        }else{
            jp(__URL__ . "index.php/admin/index/artment", 0, "删除失败");
        }
    }
    
    //添加充值卡类型
    
    function Addcardtype(){
        $this->BS();
        $dblist = M("cardtype");
        $imp = implode(",", post('cardtype_itemid'));
        $falg = $dblist->insert(array(
            "cardtype_name"=>post("cardtype_name"),
            "cardtype_num"=>post("cardtype_num"),
            "cardtype_itemid"=>$imp,
            "cardtype_me"=>post("cardtype_me")
        ));
        if ($falg){
            json(1, "添加成功!", null);
        }else{
            json(6, "添加失败!", null);
        }
    }
    
    //读取类型信息
    function Viewcardtype(){
        $this->BS();
        $dblist = M("cardtype");
        json(1, "读取中", $dblist->select("id={$_GET['id']}"));
    }
    
    //修改类型
    function Editcardtype(){
        $this->BS();
        $dblist = M("cardtype");
        $id = get('id');
        $falg = $dblist->update(array("cardtype_name"=>post("cardtype_name"),"cardtype_num"=>post("cardtype_num"),"cardtype_me"=>post("cardtype_me")),"id={$id}");
        if ($falg){
            json(1, "修改成功!", null);
        }else{
            json(6, "修改失败!", null);
        }
    }
    
    //删除分类
    function Deletecardtype(){
        $this->BS();
        $dblist = M("cardtype");
        $falg = $dblist->delete("id={$_GET['id']}");
        if ($falg){
            jp(__URL__ . "index.php/admin/index/cardtype", 0, "删除成功");
        }else{
            jp(__URL__ . "index.php/admin/index/cardtype", 0, "删除失败");
        }
    }
    
    //添加分销商等级
    function Addagentrank(){
        $this->BS();
        $dblist = M("agentrank");
        $falg = $dblist->insert(array(
            "agentrank_name"=>post("agentrank_name"),
            "agentrank_rule"=>post("agentrank_rule")
        ));
        if ($falg){
            json(1, "添加成功!", null);
        }else{
            json(6, "添加失败!", null);
        }
    }
    //读取分销商等级信息
    function Viewagentrank(){
        $this->BS();
        $dblist = M("agentrank");
        json(1, "读取中", $dblist->select("id={$_GET['id']}"));
    }
    //修改经销商信息
    function Editagentrank(){
        $this->BS();
        $dblist = M("agentrank");
        $id = get('id');
        $falg = $dblist->update(array("agentrank_name"=>post("agentrank_name"),"agentrank_rule"=>post("agentrank_rule")),"id={$id}");
        if ($falg){
            json(1, "修改成功!", null);
        }else{
            json(6, "修改失败!", null);
        }
    }
    //删除经销商信息
    function Deleteagentrank(){
        $this->BS();
        $dblist = M("agentrank");
        $falg = $dblist->delete("id={$_GET['id']}");
        if ($falg){
            jp(__URL__ . "index.php/admin/index/agentrank", 0, "删除成功");
        }else{
            jp(__URL__ . "index.php/admin/index/agentrank", 0, "删除失败");
        }
    }
    //添加分销商
    function Addagent(){
        $this->BS();
        $dblist = M("agent");
        $agent_rand = get_rand(6);//6位随机颜值
        $agent_password = md5( post("agent_password") . $agent_rand);
        $agent_itemid = implode(",", post("agent_itemid"));//分割成数组
        $agent_power = post("agent_power") ? 1 : 2;
        if (post('agent_idrank') == 'null') json(6, '请选择分销商等级', null);
        if (post('agent_levelid') == 'null') json(6, '请选择上级代理', null);
        //不能大于上级代理的充值点
        $money=M('agent')->select("id=".post('agent_levelid'));//上级代理的充值点

        if($money[0]['agent_moeny']<=post("agent_moeny")){
            json(6,"充值点不能大于上级代理".$money[0]['agent_moeny']."点",null);
        }
        $falg = $dblist->insert(array(
            "agent_username"=>post("agent_username"),
            "agent_password"=>$agent_password,
            "agent_itemid"=>$agent_itemid,
            "agent_moeny"=>post("agent_moeny"),
            "agent_gain"=>0,
            "agent_gainAll"=>0,
            "agent_qq"=>post("agent_qq"),
            "agent_freeze"=>1,
            "agent_loginip"=>"127.0.0.1",
            "agent_email"=>post("agent_email"),
            "agent_payment"=>post("agent_payment"),
            "agent_payname"=>post("agent_payname"),
            "agent_levelid"=>post('agent_levelid'),
            "agent_profit"=>0,
            "agent_power"=>$agent_power,
            "agent_rand"=>$agent_rand,
            "agent_idrank"=>post("agent_idrank")
        ));
        if ($falg){
            json(1, "添加成功!", null);
        }else{
            json(6, "添加失败,请填写完整的资料!", null);
        }
    }
    
    //修改分销商
    function Editagent(){
        $this->BS();
        $dblist = M("agent");
        $id = get("id");
        $pass = post("agent_password");
        if (post("agent_idrank") == 'null') json(6, '请选择分销商等级', null);
        if (empty($pass)){
            $died = $dblist->select("id={$id}")[0];
            $agent_password = $died['agent_password'];
            $agent_rand = $died['agent_rand'];//6位随机颜值
        }else {
            $agent_rand = get_rand(6);//6位随机颜值
            $agent_password = md5( post("agent_password") . $agent_rand);
        } 
        $agent_itemid = implode(",", post("agent_itemid"));//分割成数组
        $agent_power = post("agent_power") ? 1 : 2;//开通下级
        $agent_levelid_username = post("agent_levelid");
        $agent_profit = post("agent_profit");
         if ($agent_levelid_username == '0'){
            $agent_levelid = 0;
        }else{
            $agent_levelid = $dblist->select("agent_username='{$agent_levelid_username}'")[0]['id'];
        } 
          $falg = $dblist->update(array(
            "agent_username"=>post("agent_username"),
            "agent_password"=>$agent_password,
            "agent_itemid"=>$agent_itemid,
            "agent_moeny"=>post("agent_moeny"),
            "agent_gain"=>post("agent_gain"),
            "agent_gainAll"=>post("agent_gainAll"),
            "agent_qq"=>post("agent_qq"),
            "agent_email"=>post("agent_email"),
            "agent_payment"=>post("agent_payment"),
            "agent_payname"=>post("agent_payname"),
            "agent_levelid"=>$agent_levelid,
            "agent_profit"=>floatval($agent_profit),
            "agent_power"=>$agent_power,
            "agent_rand"=>$agent_rand,
            "agent_idrank"=>post("agent_idrank")
        ),"id={$id}");  
        if ($falg){
            json(1, "修改成功!", null);
        }else{
            json(6, "修改失败!", null);
        }
    }
    //冻结分销商
    function Frozenagent(){
        $this->BS();
        $dblist = M("agent");
        $id = get("id");
        if ($dblist->select("id={$id}")[0]['agent_freeze'] == 1){
            $falg = $dblist->update(array("agent_freeze"=>2),"id={$id}");
        }else{
            $falg = $dblist->update(array("agent_freeze"=>1),"id={$id}");
        }
        if ($falg){
            jp(Turl(get("url")), 0, "操作成功");
        }else{
            jp(Turl(get("url")), 0, "操作失败");
        }
    }
    //删除经销商
    function Deleteagent(){
        $this->BS();
        $dblist = M("agent");
        $falg = $dblist->delete("id={$_GET['id']}");
        if ($falg){
            jp(Turl(get("url")), 0, "操作成功");
        }else{
            jp(Turl(get("url")), 0, "操作失败");
        }
    }
    
    //批量充值余额
    function Payagent(){
        $this->BS();
        $dblist = M("agent");
        $id = explode(",", trim(get("id"),","));
        for ($i=0;$i<count($id);$i++){
            $agent_moeny_old = $dblist->select("id={$id[$i]}")[0]['agent_moeny'];
            if ($agent_moeny_old < 0){
                $agent_moeny = post("agent_moeny");
            }else{
                $agent_moeny = post("agent_moeny")+$agent_moeny_old;
            }
            $falg = $dblist->update(array("agent_moeny"=>$agent_moeny),"id={$id[$i]}");
        }
        json(1, "充值成功!", null);
    }
    
    //生成充值卡
    function Addrecharge(){
        $this->BS();
        $dblist = M("recharge");
        $num = post("num");//生成数量
        $recharge_itemid = implode(",", post("recharge_itemid"));//分割成字符串
        $recharge_rand = strtoupper(get_rand(8));
        for ($i=1;$i<=$num;$i++){
            $dblist->insert(array(
                "recharge_cami"=>get_rand(30),
                "recharge_paynum"=>post("recharge_paynum"),
                "recharge_frozen"=>1,
                "recharge_logint"=>post("recharge_logint"),
                "recharge_ctime"=>time(),
                "recharge_credate"=>date("Y-m-d",time()),
                "recharge_usetime"=>0,
                "recharge_user"=>0,
                "recharge_create"=>0,
                "recharge_itemid"=>$recharge_itemid,
                "recharge_useitem"=>0,
                "recharge_rand"=>$recharge_rand
            ));
        }
        json(1, "生成成功!", $recharge_rand);
    }
    
    //冻结解冻充值卡
    function Frozenrecharge(){
        $this->BS();
        $dblist = M("recharge");
        $id = get("id");
        if ($dblist->select("id={$id}")[0]['recharge_frozen'] == 1){
            $falg = $dblist->update(array("recharge_frozen"=>2),"id={$id}");
        }else{
            $falg = $dblist->update(array("recharge_frozen"=>1),"id={$id}");
        }
        if ($falg){
            jp(Turl(get("url")), 0, "操作成功");
        }else{
            jp(Turl(get("url")), 0, "操作失败");
        }
    }
    
    //清空充值卡
    function Emptyrecharge(){
        $this->BS();
        $dblist = M("recharge");
        //分类
        $frozen = get("frozen");
        $where = "1=2";
        if (!empty($frozen)){
            //未冻结
            if ($frozen == 1){
                $where = "recharge_frozen=1";
            }
            //已冻结
            if ($frozen == 2){
                $where = "recharge_frozen=2";
            }
            //已使用
            if ($frozen == 3){
                $where = "recharge_useitem != 0";
            }
            //未使用
            if ($frozen == 4){
                $where = "recharge_useitem = 0";
            }
            //可使用
            if ($frozen == 5){
                $where = "recharge_frozen = 1 and recharge_useitem = 0";
            }
        }
        $falg = $dblist->delete($where);
        if ($falg){
            jp(Turl(get("url")), 0, "操作成功");
        }else{
            jp(Turl(get("url")), 0, "操作失败");
        }
    }
    //导出分类
    function Downrecharge(){
        $this->BS();
        $dblist = M("recharge");
        //分类
        $frozen = get("frozen");
        $where = "1=1";
        if (!empty($frozen)){
            //未冻结
            if ($frozen == 1){
                $where = "recharge_frozen=1";
            }
            //已冻结
            if ($frozen == 2){
                $where = "recharge_frozen=2";
            }
            //已使用
            if ($frozen == 3){
                $where = "recharge_useitem != 0";
            }
            //未使用
            if ($frozen == 4){
                $where = "recharge_useitem = 0";
            }
            //可使用
            if ($frozen == 5){
                $where = "recharge_frozen = 1 and recharge_useitem = 0";
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

    //添加广告
    function addadv(){

        $this->BS();
        $dblist = M("advimage");
        $name = post("name");
        $url = post("url");
        $sort = post("sort");
        $type = post("type");
        $imgname = $_FILES['path']['name'];
        $tmp = $_FILES['path']['tmp_name'];
        $size = $_FILES['path']['size'];

        if($imgname){
            if($size > 1024*1024*2){
                json(6, "上传图片超过2m!", null);
            }
            $filepath = 'photo/';
            move_uploaded_file($tmp,$filepath.$imgname);
        }
        $falg = $dblist->insert(array(
            'name' => $name,
            'path' => $imgname,
            'type' => $type,
            'url' => $url,
            'sort' => $sort,
            'create_time' => time()
        ));
        if ($falg){
            json(1, "添加成功!", null);
        }else{
            json(6, "添加失败!", null);
        }
    }
    /*编辑广告*/
    function editadv(){
        $this->BS();
        $id = get("id");
        $dblist = M("advimage");
        $name = post("name");
        $url = post("url");
        $sort = post("sort");
        $type = post("type");
        $path = post('path_name');
        $imgname = $_FILES['path']['name'];
        $tmp = $_FILES['path']['tmp_name'];
        $size = $_FILES['path']['size'];

        $filepath = 'photo/';
        if($imgname){
            if($size > 1024*1024*2){
                json(6, "上传图片超过2m!", null);
            }
            move_uploaded_file($tmp,$filepath.$imgname);
        }

        $falg = $dblist->update(array(
            'name' => $name,
            'path' => $path,
            'type' => $type,
            'url' => $url,
            'sort' => $sort,
            'create_time' => time()
        ),"id={$id}");
        if ($falg){
            json(1, "修改成功", null);
        }else{
            json(6, "修改失败!", null);
        }

    }
    //添加新用户
    function Adduser(){
        $this->BS();
        $dblist = M("user");
        $item = M("item");
        $user_rand = get_rand(6);//6位随机码
        $user_password = md5(post("password").$user_rand);//md5密码
        $user_needword = encrypt(post("password"));//明文密码
        $user_passwordsec = md5(post("user_passwordsec".$user_rand));//安全密码
        $user_itemid = intval(post("user_itemid"));//软件id
        if ($user_itemid != 0){
            //查询软件运营模式
            $them = $item->select("id={$user_itemid}")[0]['item_doType'];
            if ($them == 1){
                $user_account = date("Y-m-d H:i:s",time() + (post("user_account") * 3600));
            }else{
                $user_account = post("user_account");
            }
        }else{
            $user_account = 0;
        }
        $falg = $dblist->insert(array(
            "user_username"=>$user_itemid.'-'.post("user_username"),
            "user_password"=>$user_password,
            "user_needword"=>$user_needword,
            "user_passwordsec"=>$user_passwordsec,
            "user_mobile"=>$user_itemid.'-'.post("user_mobile"),
            "user_email"=>$user_itemid.'-'.post("user_email"),
            "user_device"=>0,
            "user_regip"=>getip(),
            "user_loginip"=>0,
            "user_regTime"=>time(),
            "user_regDate"=>date("Y-m-d",time()),
            "user_loginTime"=>0,
            "user_loginNum"=>0,
            "user_account"=>$user_account,
            "user_itemid"=>$user_itemid,
            "user_dynamic"=>0,
            "user_frozen"=>1,
            "user_saleid"=>0,
            "user_rand"=>$user_rand,
            "user_key"=>get_rand(10),
            "user_message"=>''
        ));
        if ($falg){
            json(1, "添加成功!", null);
        }else{
            json(6, "添加失败!", null);
        }
    }
    //冻结解冻用户
    function Frozenuser(){
        $this->BS();
        $dblist = M("user");
        $id = get("id");
        if ($dblist->select("id={$id}")[0]['user_frozen'] == 1){
            $falg = $dblist->update(array("user_frozen"=>2),"id={$id}");
        }else{
            $falg = $dblist->update(array("user_frozen"=>1),"id={$id}");
        }
        if ($falg){
            jp(Turl(get("url")), 0, "操作成功");
        }else{
            jp(Turl(get("url")), 0, "操作失败");
        }
    }
    
    //清空用户
    function Emptyuser(){
        $this->BS();
        $dblist = M("user");
        //分类
        $frozen = get("frozen");
        $where = "1=2";
        if (!empty($frozen)){
            //未冻结
            if ($frozen == 1){
                $where = "user_frozen=1";
            }
            //已冻结
            if ($frozen == 2){
                $where = "user_frozen=2";
            }
            //已到期 / 0余额/0次数/到期
            if ($frozen == 3){
                $where = "user_account = 0";
            }
            //未到期
            if ($frozen == 4){
                $where = "user_account != 0";
            }
            //离线
            if ($frozen == 5){
                $where = "user_dynamic = 0";
            }
            //在线
            if ($frozen == 6){
                $where = "user_dynamic != 0";
            }
        
        }
        $falg = $dblist->delete($where);
        if ($falg){
            jp(Turl(get("url")), 0, "操作成功");
        }else{
            jp(Turl(get("url")), 0, "操作失败");
        }
    }
    //更新用户数据
    function updateuser(){
        $this->BS();
        echo '<small>› 正在更新数据..请耐心等待..</small><br>';
        $dblist = M("item");
        $dbuser = M("user");
        $doItem = $dblist->select();
        //更新数据
        foreach ($doItem as $do){
            //获取到用户所有数据
            $douser = $dbuser->select("user_itemid={$do['id']}");//截取用户数据
            //判断软件是否为时间运营模式
            if ($do["item_doType"]==1){
                
                    //开始循环
                    foreach ($douser as $du){
                        //判断软件ID跟用户绑定的软件ID是否一致
                        if ($du['user_itemid']==$do['id']){
                            //判断当前时间是否大于到期时间
                            if (strtotime($du['user_account'])<time()){
                                //如果大于,将更新数据为0
                                $dbuser->update(array("user_account"=>0),"id={$du['id']}");
                            }
                        }
                    }
                
            }
            //开始循环用户
            foreach ($douser as $dus){
                //判断当前时间减掉心跳包时间是否大于心跳秒数
                if (time()-$dus['user_dynamic'] > $do['item_heartbeat']){
                    //如果减出来的数大于了心跳秒数,就证明已经离线了
                    $dbuser->update(array("user_dynamic"=>0),"id={$dus['id']}");
                }
            }  
        }
        echo '<small>› 全部更新完毕..</small>'; 
    }
    
    //批量充值会员余额
    function Payuser(){
        $this->BS();
        $dblist = M("user");
        $id = explode(",", trim(get("id"),","));
        $account = '';//初始化账户余额
        for ($i=0;$i<count($id);$i++){
            //获取用户绑定的软件ID
            $useritemid = $dblist->select("id={$id[$i]}")[0];
            //判断软件ID是否为0
            if ($useritemid['user_itemid'] != 0){
                //获取软件的运营方式 1时间 2次数 3消费
                $itemType = M("item")->select("id={$useritemid['user_itemid']}")[0]['item_doType'];
                //如果软件运营为时间
                if ($itemType == 1){
                    //如果到期时间小于当前时间
                    $oldTime = strtotime($useritemid['user_account']);
                    if ($oldTime<time()){
                        $account = date("Y-m-d H:i:s",time() + (get("pay")*3600));
                    }else{
                        $account = date("Y-m-d H:i:s",$oldTime + (get("pay")*3600));
                    }
                }else {
                    $account = $useritemid['user_account']+get("pay");
                }
                $dblist->update(array("user_account"=>$account),"id={$id[$i]}");
            }
        }
        json(1, "充值成功", null);
    }
    
    //修改用户
    function Edituser(){
        $this->BS();
        $dblist = M("user");
        $id = get("id");
        $userDB = $dblist->select("id={$id}")[0];
        $user_password = post("user_password");
        $user_passwordsec = post("user_passwordsec");
        if (!empty($user_password)){
            $user_password = md5(post("user_password") . $userDB['user_rand']);//md5密码
            $user_needword = encrypt(post("user_password"));//明文密码
        }else{
            $user_password = $userDB['user_password'];//md5密码
            $user_needword = $userDB['user_needword'];//明文密码
        }
        if (!empty($user_passwordsec)){
            $user_passwordsec = md5(post("user_passwordsec").$userDB['user_rand']);//安全密码
        }else {
            $user_passwordsec = $userDB['user_passwordsec'];
        }
        $falg = $dblist->update(array(
            "user_username"=>$userDB['user_itemid'].'-'.post("user_username"),
            "user_password"=>$user_password,
            "user_needword"=>$user_needword,
            "user_passwordsec"=>$user_passwordsec,
            "user_mobile"=>$userDB['user_itemid'].'-'.post("user_mobile"),
            "user_email"=>$userDB['user_itemid'].'-'.post("user_email"),
            "user_device"=>post("user_device"),
            "user_loginNum"=>post("user_loginNum"),
            "user_account"=>post("user_account")
        ),"id={$id}");
        if ($falg){
            json(1, "修改成功", null);
        }else{
            json(6, "修改失败!", null);
        }
    }
    
    //批量发送消息给用户
    function Msgsend(){
        $this->BS();
        $dblist = M("user");
        $id = explode(",", trim(get("id"),","));
        for ($i=0;$i<count($id);$i++){
            //$userDB = $dblist->select("id={$id[$i]}")[0];
            
            $user_message = get("msg");
            
            $dblist->update(array("user_message"=>$user_message),"id={$id[$i]}");
        }
        json(1, "发送成功", null);
    }
    
    //回复chat
    function sendchat(){
        $this->BS();
        $dblist = M("agentchat");
        $id = get("id");
        $falg = $dblist->update(
            array("agentchat_return"=>post("agentchat_return"),"agentchat_retime"=>date("Y-m-d H:i:s",time()))
            ,"id=$id");
        if ($falg){
            json(1, "回复成功", null);
        }else{
            json(6, "回复失败!", null);
        }
    }
    
    //清空提现账单
    function Emptydeposit(){
        $this->BS();
        $dblist = M("deposit");
        //分类
        $frozen = get("frozen");
        $where = "1=2";
        if (!empty($frozen)){
            //未冻结
            if ($frozen == 1){
                $where = "deposit_state=1";
            }
            //已冻结
            if ($frozen == 2){
                $where = "deposit_state=2";
            }
        }
        $falg = $dblist->delete($where);
        if ($falg){
            jp(Turl(get("url")), 0, "操作成功");
        }else{
            jp(Turl(get("url")), 0, "操作失败");
        }
    }
    
    //导出提现账单
    function Downdeposit(){
        $this->BS();
        $dblist = M("deposit");
        //分类
        $frozen = get("frozen");
        $where = "1=1";
        if (!empty($frozen)){
            //未冻结
            if ($frozen == 1){
                $where = "deposit_state=1";
            }
            //已冻结
            if ($frozen == 2){
                $where = "deposit_state=2";
            }
        }
        $dbdata = $dblist->select($where);//查询出来
        $cami = '';
        $filename = date("Y-m-d H:i:s",time()) . '导出账单.txt';
        foreach ($dbdata as $mi){
            $cami .= "流水号：{$mi['deposit_serial']}----用户名：{$mi['deposit_username']}----实际金额：{$mi['deposit_send']}----收款信息：{$mi['deposit_cpname']} {$mi['deposit_paytype']} {$mi['deposit_paym']}\r\n";
        }
        DownText($filename, $cami);
    }
    
    //邮箱通信测试
    function emailc(){
        $this->BS();
        $test = mailSend("securitycenters@163.com", 'Sorry to have bothered you', "I'm just testing my email communication. Please don't reply to me");
        if ($test){
            json(1,'通信连接成功',null);
        }else{
            json(6,'通信连接失败',null);
        }
    }
    
    //修改二维码
    function Editdime(){
        $this->BS();
        $dblist = M("dime");
        $id = get("id");
        $falg = $dblist->update(array(
            "dime_name"=>post("dime_name"),
            "dime_price"=>post("dime_price"),
            "dime_alipayurl"=>post("dime_alipayurl"),
            "dime_wxurl"=>post("dime_wxurl")
        ),"id={$id}");
        if ($falg){
            json(1, "修改成功!", null);
        }else{
            json(6, "修改失败!", null);
        }
    }
    
    //添加二维码
    function Adddime(){
        $this->BS();
        $dime_name = post('dime_name');
        $dime_price = post('dime_price');
        if (!is_numeric($dime_name) || !is_numeric($dime_price)) json(6, '清输入正确的整数', null);
        $dblist = M("dime");
        $falg = $dblist->insert(array(
            "dime_name"=>$dime_name,
            "dime_price"=>$dime_price,
            "dime_alipayurl"=>post("dime_alipayurl"),
            "dime_wxurl"=>post("dime_wxurl"),
            "dime_ctime"=>0,
            "dime_ways"=>2
        ));
        if ($falg){
            json(1, "创建成功!", null);
        }else{
            json(6, "创建失败!", null);
        }
    }
    
    //添加动态数据
    function Addsecurity(){
        $this->BS();
        if (post('security_itemid') == 0 || post('security_name') == '' || post('security_content') == '' || post('security_cycle') == '') json(6, '检测到多数项目为空', null);
        
        $security_encrypt = post("security_encrypt") ? 1 : 2;
        $security_cookies = post('security_cookies') ? 1 : 2;
        $security_dynamic = post("security_dynamic") ? 1 : 2;
        
        $falg = M("security")->insert(array(
            "security_name"=>post("security_name"),
            "security_content"=>post("security_content"),
            "security_itemid"=>post("security_itemid"),
            "security_encrypt"=>$security_encrypt,
            "security_cookies"=>$security_cookies,
            "security_dynamic"=>$security_dynamic,
            "security_cycle"=>post("security_cycle"),
            "security_time"=>time()
        ));
        if ($falg){
            json(1, "添加数据成功!", null);
        }else{
            json(6, "添加数据失败!", null);
        }
    }
    
    //修改动态数据
    function Editsecurity(){
        $this->BS();
        if (post('security_itemid') == 0 || post('security_name') == '' || post('security_content') == '' || post('security_cycle') == '') json(6, '检测到多数项目为空', null);
        $id = get("id");
        $security_encrypt = post("security_encrypt") ? 1 : 2;
        $security_cookies = post('security_cookies') ? 1 : 2;
        $security_dynamic = post("security_dynamic") ? 1 : 2;
    
        $falg = M("security")->update(array(
            "security_name"=>post("security_name"),
            "security_content"=>post("security_content"),
            "security_itemid"=>post("security_itemid"),
            "security_encrypt"=>$security_encrypt,
            "security_cookies"=>$security_cookies,
            "security_dynamic"=>$security_dynamic,
            "security_cycle"=>post("security_cycle")
        ),"id={$id}");
        if ($falg){
            json(1, "修改数据成功!", null);
        }else{
            json(6, "修改数据失败!", null);
        }
    }
    
    //添加商品分类
    function Addshoprt(){
        $this->BS();
        if (post("shoprt_name") == '') json(6, '分类名称不能为空', null);
        $shoprt_display = post('shoprt_display') ? 1 : 2;
        $shoprt_itemid = implode(",", post('shoprt_itemid'));
        $pid = post('shoprt_pid');
        $Mshop = M("shoprt");
        if ($pid != 0){
            $shoprt_itemid = $Mshop->select("id={$pid}")[0]['shoprt_itemid'];
        }
        $falg = $Mshop->insert(array(
            "shoprt_name"=>post('shoprt_name'),
            "shoprt_pid"=>$pid,
            "shoprt_itemid"=>$shoprt_itemid,
            "shoprt_time"=>time(),
            "shoprt_display"=>$shoprt_display
        ));
        if ($falg){
            json(1, "添加分类成功!", null);
        }else{
            json(6, "添加分类失败!", null);
        }
    }
    
    //修改商品分类
    function Editshoprt(){
        $this->BS();
        if (post("shoprt_name") == '') json(6, '分类名称不能为空', null);
        $shoprt_display = post('shoprt_display') ? 1 : 2;
        $shoprt_itemid = implode(",", post('shoprt_itemid'));
        $pid = post('shoprt_pid');
        $Mshop = M("shoprt");
        $id = get("id");
        if ($pid != 0){
            $shoprt_itemid = $Mshop->select("id={$pid}")[0]['shoprt_itemid'];
        }
        $falg = $Mshop->update(array(
            "shoprt_name"=>post('shoprt_name'),
            "shoprt_pid"=>$pid,
            "shoprt_itemid"=>$shoprt_itemid,
            "shoprt_display"=>$shoprt_display
        ),"id={$id}");
        
        if ($falg){
            json(1, "修改分类成功!", null);
        }else{
            json(6, "修改分类失败!", null);
        }
    }
    
    //添加商品
    function Addshop(){
        $this->BS();
        if (post('shop_name') == '' || post('shop_rtid') == '' || post("shop_price") == '') json(6, '添加失败,请仔细填写重要信息!', null);
        $falg = M("shop")->insert(array(
            "shop_name"=>post('shop_name'),
            "shop_rtid"=>post("shop_rtid"),
            "shop_type"=>post('shop_type'),
            "shop_content"=>trim(post("shop_content"),PHP_EOL),
            "shop_price"=>post("shop_price"),
            "shop_present"=>post("shop_present"),
            "shop_imgurl"=>post("shop_imgurl"),
            "shop_cnum"=>count(explode(PHP_EOL, trim(post("shop_content"),PHP_EOL)))
        ));
        if ($falg){
            json(1, "添加商品成功!", null);
        }else{
            json(6, "添加商品失败!", null);
        }
        
    }
    //修改商品
    function Editshop(){
        $this->BS();
        $id = get("id");
        if (post('shop_name') == '' || post('shop_rtid') == '' || post("shop_price") == '') json(6, '修改失败,请仔细填写重要信息!', null);
        $falg = M("shop")->update(array(
            "shop_name"=>post('shop_name'),
            "shop_rtid"=>post("shop_rtid"),
            "shop_type"=>post('shop_type'),
            "shop_content"=>trim(post("shop_content"),PHP_EOL),
            "shop_price"=>post("shop_price"),
            "shop_present"=>post("shop_present"),
            "shop_imgurl"=>post("shop_imgurl"),
            "shop_cnum"=>count(explode(PHP_EOL, trim(post("shop_content"),PHP_EOL)))
        ),"id={$id}");
        if ($falg){
            json(1, "修改商品成功!", null);
        }else{
            json(6, "修改商品失败!", null);
        }
    
    }
    
    //批量发货
    function Deliversend(){
        $this->BS();
        $dblist = M("shopbuy");
        $id = explode(",", trim(get("id"),","));
        $msg = get("msg");
        for ($i=0;$i<count($id);$i++){
            $db = $dblist->select("id={$id[$i]}")[0];
            if ($db['shopbuy_type'] == 1 && $db['shopbuy_goods'] == 2) {
                $dblist->update(array("shopbuy_goods"=>1,"shopbuy_deliver"=>$msg,"shopbuy_ftime"=>time()),"id={$id[$i]}");
            }
        }
        json(1, "发货成功", null);
    }
    
    //添加博客分类
    function Addwritingsrt(){
        $this->BS();
        if (post("writingsrt_name") == '') json(6, '分类名称不能为空', null);
        $writingsrt_display = post('writingsrt_display') ? 1 : 2;
        $pid = post('writingsrt_pid');
        $Ms = M("writingsrt");
    
        $falg = $Ms->insert(array(
            "writingsrt_name"=>post('writingsrt_name'),
            "writingsrt_pid"=>$pid,
            "writingsrt_display"=>$writingsrt_display,
            "writingsrt_ctime"=>time()
        ));
        if ($falg){
            json(1, "添加分类成功!", null);
        }else{
            json(6, "添加分类失败!", null);
        }
    }
    //修改博客分类
    function Editwritingsrt(){
        $this->BS();
        if (post("writingsrt_name") == '') json(6, '分类名称不能为空', null);
        $shoprt_display = post('writingsrt_display') ? 1 : 2;
        $pid = post('writingsrt_pid');
        $Ms = M("writingsrt");
        $id = get("id");

        $falg = $Ms->update(array(
            "writingsrt_name"=>post('writingsrt_name'),
            "writingsrt_pid"=>$pid,
            "writingsrt_display"=>$shoprt_display
        ),"id={$id}");
    
        if ($falg){
            json(1, "修改分类成功!", null);
        }else{
            json(6, "修改分类失败!", null);
        }
    }
    
    //添加文章
    function Addwritings(){
        $this->BS();
        $user = decrypt(cookie("_u"),"hello","mengi");//取出用户名
        $writings_title = post('writings_title');
        $writings_author = post('writings_author') == '' ? $user : post('writings_author');
        $writings_content = $_POST['writings_content'];//为了防止HTML
        $writings_password = post('writings_password');
        $writings_rid = post('writings_rid');
        $writings_groom = post('writings_groom') ? 2 : 1;
        $writings_alias = post('writings_alias');
        
        //初始化文章写入
        $writings = M('writings')->insert(array(
            'writings_title'=>$writings_title,
            'writings_author'=>$writings_author,
            'writings_ctime'=>time(),
            'writings_view'=>0,
            'writings_content'=>$writings_content,
            'writings_password'=>$writings_password,
            'writings_fabulous'=>0,
            'writings_step'=>0,
            'writings_rid'=>$writings_rid,
            'writings_groom'=>$writings_groom,
            'writings_alias'=>$writings_alias,
            "writings_imgurl"=>subimgurl(stripslashes($writings_content))
        ));
        
        if ($writings){
            json(1, '文章发布成功', null);
        }else{
            json(6, '文章发布失败', null);
        }
        
    }
    //修改文章
    function Editwritings(){
        $this->BS();
        $user = decrypt(cookie("_u"),"hello","mengi");//取出用户名
        $writings_title = post('writings_title');
        $writings_author = post('writings_author') == '' ? $user : post('writings_author');
        $writings_content = $_POST['writings_content'];//为了防止HTML
        $writings_password = post('writings_password');
        $writings_rid = post('writings_rid');
        $writings_groom = post('writings_groom') ? 2 : 1;
        $writings_alias = post('writings_alias');
        $id = get('id');
        //初始化文章写入
        $writings = M('writings')->update(array(
            'writings_title'=>$writings_title,
            'writings_author'=>$writings_author,
            'writings_content'=>$writings_content,
            'writings_password'=>$writings_password,
            'writings_rid'=>$writings_rid,
            'writings_groom'=>$writings_groom,
            'writings_alias'=>$writings_alias,
            "writings_imgurl"=>subimgurl(stripslashes($writings_content))
        ),"id={$id}");
    
        if ($writings){
            json(1, '文章修改成功', null);
        }else{
            json(6, '文章修改失败', null);
        }
    
    }
    
    //修改博客设置
    function configwrit(){
        $this->BS();
        
        $set = M('configwrit')->update(
            array(
                'configwrit_title'=>post('configwrit_title'),
                'configwrit_keywrod'=>post('configwrit_keywrod'),
                'configwrit_introduction'=>post('configwrit_introduction'),
                'configwrit_static'=>post('configwrit_static'),
                'configwrit_open'=>post('configwrit_open'),
                'configwrit_hotnum'=>post('configwrit_hotnum')
            ),'id=1');
        if ($set){
            json(1, '保存成功', null);
        }else{
            json(6, '保存失败', null);
        }
    }
    //--------
    //公共方法
    //--------
    //交互
    
    //批量操作ID单独字段
    function pub() {
        $this->BS();
        $tpl = get("tpl");//表生成
        $action = get("action");//操作方法
        
        $id = explode(",", trim(get("id"),","));
        $dblist = M("{$tpl}");
        
        if ($action == 'delete'){
            for ($i=0;$i<count($id);$i++){
                $falg = $dblist->delete("id={$id[$i]}");
            }
        }
        if ($action == 'update'){
            $key = explode("+", trim(get("key"),"+"));//字段KEY
            $value = explode("+", trim(get("value"),"+"));//字段值
            $array = array();//初始化修改字段的数据
            for ($j=0;$j<count($key);$j++){
                if ($value[$j] == 'time'){
                    $array[$key[$j]] = time();
                }else{
                    $values = Turl(str_replace('-', PHP_EOL, $value[$j]));
                    $array[$key[$j]] = $values;
                }
            }
            for ($k=0;$k<count($id);$k++){
                $falg = $dblist->update($array,"id={$id[$k]}");
            }
        }
        jp(Turl(get("url")), 0, "操作成功");
        
    }
    
    private function BS(){
        if (cookie("_u")){
            $dblist = M("manage");
            $user = decrypt(cookie("_u"),"hello","mengi");//取出用户名
            $query = $dblist->select("man_username='{$user}'")[0];//获取数据库
            if (!is_array($query)){
                jp(__URL__ . "index.php/admin/index/login", 3, "会话已经过期,请重新登录!");
            }
            $pass = md5(decrypt(cookie("_p"),"hello",$query['man_token']).$query['man_token']);//取出密码
            if ($pass != $query['man_password']){
                jp(__URL__ . "index.php/admin/index/login", 3, "会话已经过期,请重新登录!");
            }
        }else{
            jp(__URL__ . "index.php/admin/index/login", 3, "会话已经过期,请重新登录!");
        }
    }


}

?>