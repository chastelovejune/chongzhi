<?php
namespace admin\controller;
class index{
    
    //登录
    function login(){
       //检测是否登录过
       if (cookie("_u")){
           $dblist = M("manage");
           $user = decrypt(cookie("_u"),"hello","mengi");//取出用户名
           $query = $dblist->select("man_username='{$user}'")[0];//获取数据库
           $pass = md5(decrypt(cookie("_p"),"hello",$query['man_token']).$query['man_token']);//取出密码
           if ($pass == $query['man_password']){
               jp(__URL__ . "index.php/admin/index/home", 1, "登录成功!登录地点:" . getallopatry(getip())) . "(" . getip() . ")";
           }
       }
       $token = get_rand(40);
       $tokenname = get_rand(12);//token的随机名字 
       $username = get_rand(12);//user的随机名字
       $password = get_rand(12);//pass的随机名字
       $_SESSION['token'] = $token;//生成40位token
       $_SESSION['tokenname'] = $tokenname;
       $_SESSION['username'] = $username;
       $_SESSION['password'] = $password;
       display("login.html",array("token"=>$token,"tokenname"=>$tokenname,"username"=>$username,"password"=>$password));
    } 
    
    //修改密码
    
    function myEdit(){
        $this->BS();
        //获取自己的数据
        $user = decrypt(cookie("_u"),"hello","mengi");//取出用户名
        $data = M("manage")->select("man_username='{$user}'")[0];
        display("myEdit.html",array("data"=>$data));
    }
    
    //面板
    function home(){
        $this->BS();
        $user = decrypt(cookie("_u"),"hello","mengi");//取出用户名
        $data = M("manage")->select("man_username='{$user}'")[0];
        display("index.html",array("user"=>$data));
    }
    //引导页
    function main(){
        $this->BS();
        display("main.html");
    }
    //软件界面
    function artment(){
        $this->BS();
        $dblist = M("item");
        $query = $dblist->select();
        display("artment.html",array("data"=>$query));
    }
    //修改软件
    function editment(){
        $this->BS();
        $dblist = M("item");
        $id = get("id");
        $query = $dblist->select("id={$id}")[0];
        display("editment.html",array("data"=>$query));
    }
    //充值卡类型
    function cardtype(){
        $this->BS();
        $dblist = M("cardtype");
        //翻页功能
        $page = get("page");
        $pageNum = 15;//初始化每页显示数量15
        $queryCount = $dblist->sql("select count(id) as c from god_cardtype")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page);
        $query = $dblist->select(null,null,"id","$pageMy,$pageNum");
        $item = M("item")->select();//查询软件列表
        display("cardtype.html",array("data"=>$query,"item"=>$item,"arrayPage"=>$arrayPage));
    }
    
    //分销商等级
    function agentrank(){
        $this->BS();
        $dblist = M("agentrank");
        $page = get("page");
        $pageNum = 10;//初始化每页显示数量15
        $queryCount = $dblist->sql("select count(id) as c from god_agentrank")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page);
        $query = $dblist->select(null,null,"id","$pageMy,$pageNum");
        display("agentrank.html",array("data"=>$query,"arrayPage"=>$arrayPage));
    }
    
    //分销商管理
    function agent(){
        $this->BS();
        $dblist = M("agent");
        //搜索功能
        $search = get("search");
        //冻结分类
        $frozen = get("frozen");
        $where = null;
        if (!empty($frozen)){
            $where = "agent_freeze={$frozen}";
        }
        if (!empty($search)){
            $where = "agent_username like '{$search}%'";
        }
        if (!empty($search) && !empty($frozen)){
            $where = "agent_freeze={$frozen} and agent_username like '{$search}%'";
        }
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
        $arrayPage = array("count"=>$pageCount,"page"=>$page,"num"=>$queryCount);
        $query = $dblist->select($where,null,"id","$pageMy,$pageNum");
        display("agent.html",array("data"=>$query,"arrayPage"=>$arrayPage));
    }
    //添加分销商
    function addAgent(){
        $this->BS();
        $dblist = M("item");
        $query = $dblist->select();
        display("Addagent.html",array("item"=>$query));
    }
    //修改分销商
    function editAgent(){
        $this->BS();
        $dblist = M("item");//实例化ITEM
        $id = get("id");
        $dbbase = M("agent")->select("id={$id}")[0];
        $query = $dblist->select();
        display("Editagent.html",array("item"=>$query,"agentdb"=>$dbbase));
    }
    
    //充值卡管理
    
    function recharge(){
        $this->BS();
        $dblist = M("recharge");
        //搜索功能
        $search = get("search");
        //冻结分类
        $frozen = get("frozen");
        //软件ID查询
        $itemID = get("itemID");
        //分销商查询
        $agentID = get("agentID");
        //用户查询
        $userID = get("userID");
        //时间查询
        $TimeID = get("TimeID");
        $where = null;
        $frozenAnd = null;
        if (!empty($TimeID)){
            $where = "recharge_credate like '%{$TimeID}%'";
            $frozenAnd = 'and';
        }
        if (!empty($agentID)){
            $where = "recharge_create like '%{$agentID}%'";
            $frozenAnd = 'and';
        }
        
        if (!empty($userID)){
            $where = "recharge_user like '%{$userID}%'";
            $frozenAnd = 'and';
        }
        
        if (!empty($itemID)){
            $where = "recharge_itemid like '%{$itemID}%'";
            $frozenAnd = 'and';
        }
        
        
        if (!empty($frozen)){
            //未冻结
            if ($frozen == 1){
                $where = "recharge_frozen=1 $frozenAnd $where";
            }
            //已冻结
            if ($frozen == 2){
                $where = "recharge_frozen=2 $frozenAnd $where";
            }
            //已使用
            if ($frozen == 3){
                $where = "recharge_useitem != 0 $frozenAnd $where";
            }
            //未使用
            if ($frozen == 4){
                $where = "recharge_useitem = 0 $frozenAnd $where";
            }
            //可使用
            if ($frozen == 5){
                $where = " recharge_frozen = 1 and recharge_useitem = 0 $frozenAnd $where";
            }
            
        }
        if (!empty($search)){
            $where = "recharge_cami like '{$search}%'";
        }
        if (!empty($search) && !empty($frozen)){
            $where = "recharge_frozen={$frozen} and recharge_cami like '{$search}%'";
        }
        //翻页功能
        $page = get("page");
        $pageNum = 15;//初始化每页显示数量15
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
        display("recharge.html",array("data"=>$query,"arrayPage"=>$arrayPage));
        
    }
    
    //添加充值卡
    function addRecharge(){
        $this->BS();
        $dblist = M("item");
        $query = $dblist->select();
        display("Addrecharge.html",array("item"=>$query));
    }
    
    //批卡代码查询
    function codeRecharge(){
        $this->BS();
        $code = get("code");//批卡代码
        $dblist = M("recharge");
        $use = get("use");
        $delete = get("delete");
        $where = null;
        if ($use == 1){
            $where = 'and recharge_useitem != 0';//已使用
        }
        if ($use == 2){
            $where = 'and recharge_useitem = 0';//未使用
        }
        if ($use == 3){
            $where = 'and recharge_frozen = 2';//已冻结
        }
        if ($use == 4){
            $where = 'and recharge_frozen = 1';//未冻结
        }
        if ($use == 5){
            $where = 'and recharge_frozen = 1 and recharge_useitem = 0';//未冻结 和 未使用 
        }
        $count = $dblist->sql("select count(id) as c from god_recharge where recharge_rand='{$code}' $where")[0]['c'];//所有记录数量
        $query = $dblist->select("recharge_rand='{$code}' $where");
        $cami = '';
        $idNum = '';
        foreach ($query as $mi){
            $cami .= "卡号：".$mi['recharge_cami']."----面值：".$mi['recharge_paynum']."\n";
            $idNum .= $mi['id'] . ",";
        }
        if ($count == 0){
            $cami = '该分类无数据..';
        }
        display("coderecharge.html",array("count"=>$count,"cami"=>$cami,"idNum"=>trim($idNum,",")));
    }
    
    //会员管理
    function user(){
        
        $this->BS();
        $dblist = M("user");
        $item = M("item");
        //搜索功能
        $search = get("search");
        //冻结分类
        $frozen = get("frozen");
        //软件ID查询
        $itemID = get("itemID");
        //时间查询
        $TimeID = get("TimeID");
        $where = null;
        $frozenAnd = null;
        if (!empty($TimeID)){
            $where = "user_regDate like '%{$TimeID}%'";
            $frozenAnd = 'and';
        }

        if (!empty($itemID)){
            $where = "user_itemid like '%{$itemID}%'";
            $frozenAnd = 'and';
        }
        
        
        if (!empty($frozen)){
            //未冻结
            if ($frozen == 1){
                $where = "user_frozen=1 $frozenAnd $where";
            }
            //已冻结
            if ($frozen == 2){
                $where = "user_frozen=2 $frozenAnd $where";
            }
            //已到期 / 0余额/0次数/到期
            if ($frozen == 3){
                $where = "user_account = 0 $frozenAnd $where";
            }
            //未到期
            if ($frozen == 4){
                $where = "user_account != 0 $frozenAnd $where";
            }
            //离线
            if ($frozen == 5){
                $where = "user_dynamic = 0 $frozenAnd $where";
            }
            //在线
            if ($frozen == 6){
                $where = "user_dynamic != 0 $frozenAnd $where";
            }
        
        }
        if (!empty($search)){
            $where = "user_username like '%{$search}%'";
        }
        //翻页功能
        $page = get("page");
        $pageNum = 15;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_user {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page,"camiNum"=>$queryCount);
        $query = $dblist->select($where,null,"id","$pageMy,$pageNum");
        display("user.html",array("data"=>$query,"arrayPage"=>$arrayPage,"itemDB"=>$item));
        
    }
    
    //添加用户
    function addUser(){
        $this->BS();
        $dblist = M("item");
        $query = $dblist->select();
        display("Adduser.html",array("item"=>$query));
    }
    //修改用户
    function editUser(){
        $this->BS();
        $dblist = M("item");
        $id = intval(get("id"));
        $dbuser = M("user")->select("id={$id}")[0];
        $query = $dblist->select();
        display("Edituser.html",array("item"=>$query,"user"=>$dbuser));
    }
    /*广告管理*/
    function advertize(){
        $this->BS();
        $dblist = M("advimage");
        $query = $dblist->select();
        display("advertize.html",array('data'=>$query));
    }
    /*修改广告*/
    function editadv(){
        $this->BS();
        $dblist = M("advimage");
        $id = intval(get("id"));
        $dblist =$dblist->select("id={$id}")[0];

        display("editadv.html",array("item"=>$dblist));
    }
    /*添加广告*/
    function addadv(){
        $this->BS();
        display("addadv.html");
    }

    //全局设置
    function setTings(){
        $this->BS();
        $dblist = M("settings");
        $query = $dblist->select("id=1")[0];
        display("settings.html",array("settings"=>$query));
    }
    
    function chat(){
        $this->BS();
        $dblist = M("agentchat");

        //翻页功能
        $pageNum = 15;//初始化每页显示数量15
        $page = get("page");
        
        //搜索功能
        $search = get("search");
        $where = null;
        if (!empty($search)){
            $where = "concat(agentchat_content,agentchat_return) like '%{$search}%'";
        }
        
        //为了防止冲突，这里重新定义where
        $countwhere = null;
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_agentchat {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page);
        $query = $dblist->select($where,null,"id","$pageMy,$pageNum");
        display("chat.html",array("data"=>$query,"arrayPage"=>$arrayPage));
    }
    
    //回复chat
    function sendchat(){
        $this->BS();
        $id = get('id');
        $db = M("agentchat")->select("id={$id}")[0];
        display("returnchat.html",array("chat"=>$db));
    }
    
    //提现管理
    function deposit(){
        $this->BS();
        $dblist = M("deposit");
        //搜索功能
        $search = get("search");
        //到账状态
        $frozen = get("frozen");
        //时间查询
        $TimeID = get("TimeID");
        $where = null;
        $frozenAnd = null;
        if (!empty($TimeID)){
            $where = "deposit_date='{$TimeID}'";
            $frozenAnd = 'and';
        }
        if (!empty($frozen)){
            //未冻结
            if ($frozen == 1){
                $where = "deposit_state=1 $frozenAnd $where";
            }
            //已冻结
            if ($frozen == 2){
                $where = "deposit_state=2 $frozenAnd $where";
            }
  
        }
        if (!empty($search)){
            $where = "deposit_serial='{$search}'";
        }
        //翻页功能
        $page = get("page");
        $pageNum = 15;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_deposit {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page,"camiNum"=>$queryCount);
        $query = $dblist->select($where,null,"id","$pageMy,$pageNum");
        display("deposit.html",array("data"=>$query,"arrayPage"=>$arrayPage));
    }
    
    function alipay(){
        $this->BS();
        $dblist = M("alipay");
        //查流水号
        $search = get("search");
        //订单状态
        $frozen = get("frozen");
        //时间查询
        $TimeID = get("TimeID");
        $where = null;
        $frozenAnd = null;
        if (!empty($TimeID)){
            $where = "alipay_date='{$TimeID}'";
            $frozenAnd = 'and';
        }
        if (!empty($frozen)){
            //未使用
            if ($frozen == 1){
                $where = "alipay_state=1 $frozenAnd $where";
            }
            //已使用
            if ($frozen == 2){
                $where = "alipay_state=2 $frozenAnd $where";
            }
            //支付宝
            if ($frozen == 3){
                $where = "alipay_type=1 $frozenAnd $where";
            }
            //微信
            if ($frozen == 4){
                $where = "alipay_type=2 $frozenAnd $where";
            }
        }
        if (!empty($search)){
            $where = "alipay_serial='{$search}'";
        }
        //翻页功能
        $page = get("page");
        $pageNum = 15;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_alipay {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page,"camiNum"=>$queryCount);
        $query = $dblist->select($where,null,"id","$pageMy,$pageNum");
        display("alipay.html",array("data"=>$query,"arrayPage"=>$arrayPage));
    }
    
    //二维码列表
    function dime(){
        $this->BS();
        $dblist = M("dime");
        //查价格
        $search = get("search");
        //查通道是否创建
        $frozen = get("frozen");
  
        $where = null;
        $frozenAnd = null;

        if (!empty($frozen)){
            //打开
            if ($frozen == 1){
                $where = "dime_ways=1 $frozenAnd $where";
            }
            //关闭
            if ($frozen == 2){
                $where = "dime_ways=2 $frozenAnd $where";
            }
           
        }
        if (!empty($search)){
            $where = "dime_name='{$search}'";
        }
        //翻页功能
        $page = get("page");
        $pageNum = 15;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_dime {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page,"camiNum"=>$queryCount);
        $query = $dblist->select($where,null,"id","$pageMy,$pageNum");
        display("dime.html",array("data"=>$query,"arrayPage"=>$arrayPage));
    }
    
    //修改二维码
    function editDime(){
        $this->BS();
        $id = get("id");
        $dbbase = M("dime")->select("id={$id}")[0];
        display("editdime.html",array("dime"=>$dbbase));
    }
    //添加二维码
    function addDime(){
        $this->BS();
        display("Adddime.html");
    }
    
    //动态数据管理
    function security(){
        $this->BS();
        $dblist = M("security");
        //查数据名称
        $search = get("search");
        //查看是否加密
        $frozen = get("frozen");
  
        $where = null;
        $frozenAnd = null;

        if (!empty($frozen)){
            //加密
            if ($frozen == 1){
                $where = "security_encrypt=1 $frozenAnd $where";
            }
            //未加密
            if ($frozen == 2){
                $where = "security_encrypt=2 $frozenAnd $where";
            }
           
        }
        //查数据名称
        if (!empty($search)){
            $where = "security_name like '%{$search}%'";
        }
        //翻页功能
        $page = get("page");
        $pageNum = 15;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_security {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page,"camiNum"=>$queryCount);
        $query = $dblist->select($where,null,"id","$pageMy,$pageNum");
        display("security.html",array("data"=>$query,"arrayPage"=>$arrayPage));
    }
    
    //添加动态数据
    function addsecurity(){
        $this->BS();
        $dblist = M("item");
        $query = $dblist->select();
        display("Addsecurity.html",array("item"=>$query));
    }
    
    //修改动态数据
    function editsecurity(){
        $this->BS();
        $dblist = M("item");
        $query = $dblist->select();
        $id = get("id");
        $sec = M("security")->select("id={$id}")[0];
        display("Editsecurity.html",array("item"=>$query,"sec"=>$sec));
    }
    
    //商品分类
    function shoprt(){
        $this->BS();
        $dblist = M("shoprt");
        //显示状态
        $frozen = get("frozen");
        $where = null;
        $frozenAnd = null;
        
        if (!empty($frozen)){
            //已显示
            if ($frozen == 1){
                $where = "shoprt_display=1 $frozenAnd $where";
            }
            //未显示
            if ($frozen == 2){
                $where = "shoprt_display=2 $frozenAnd $where";
            }
    
        }
        //翻页功能
        $page = get("page");
        $pageNum = 15;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_shoprt {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page,"camiNum"=>$queryCount);
        $query = $dblist->select($where,null,"","$pageMy,$pageNum");
        //print_r(shopTree($query));exit;
        display("shoprt.html",array("data"=>shopTree($query),"arrayPage"=>$arrayPage));
    }
    //addshoprt
    function addshoprt(){
        $this->BS();
        $dblist = M("item");
        $query = $dblist->select();
        $shoprt = M("shoprt")->select("shoprt_pid=0");
        display("Addshoprt.html",array("item"=>$query,"shoprt"=>$shoprt));
    }
    //修改商品分类
    function editshoprt(){
        $this->BS();
        $dblist = M("item");
        $query = $dblist->select();
        $db = M("shoprt");
        $shoprt = $db->select("shoprt_pid=0");
        $id = get('id');
        $select = $db->select("id={$id}")[0];
        display("Editshoprt.html",array("item"=>$query,"shoprt"=>$shoprt,"data"=>$select));
    }
    //商品管理
    function shop(){
        $this->BS();
        $dblist = M("shop");
        //搜索商品名称
        $search = get("search");
        //发货类型 
        $frozen = get("frozen");
        $where = null;
        if (!empty($frozen)){
            $where = "shop_type={$frozen}";
        }
        if (!empty($search)){
            $where = "shop_name like '%{$search}%'";
        }
        if (!empty($search) && !empty($frozen)){
            $where = "shop_type={$frozen} and shop_name like '{$search}%'";
        }
        //翻页功能
        $page = get("page");
        $pageNum = 15;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_shop {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page,"num"=>$queryCount);
        $query = $dblist->select($where,null,"id","$pageMy,$pageNum");
        display("shop.html",array("data"=>$query,"arrayPage"=>$arrayPage));
    }
    
    //添加商品
    function addshop(){
        $this->BS();
        $shoprt = M("shoprt")->select();
        display("Addshop.html",array("shoprt"=>shopTree($shoprt)));
    }
    //修改商品
    function editshop(){
        $this->BS();
        $shoprt = M("shoprt")->select();
        $id = get("id");
        $data = M("shop")->select("id={$id}")[0];
        display("Editshop.html",array("shoprt"=>shopTree($shoprt),"data"=>$data));
    }
    
    //商品订单
    function shopbuy(){
        $this->BS();
        $dblist = M("shopbuy");
        //查询订单号
        $search = get("search");
        //发货状态
        $frozen = get("frozen");
        $where = null;
        if (!empty($frozen)){
            $where = "shopbuy_goods={$frozen}";
        }
        if (!empty($search)){
            $where = "shopbuy_serial like '%{$search}%'";
        }
        if (!empty($search) && !empty($frozen)){
            $where = "shopbuy_goods={$frozen} and shopbuy_serial like '{$search}%'";
        }
        //翻页功能
        $page = get("page");
        $pageNum = 15;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_shopbuy {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page,"num"=>$queryCount);
        $query = $dblist->select($where,null,"id","$pageMy,$pageNum");
        display("shopbuy.html",array("data"=>$query,"arrayPage"=>$arrayPage));
    }
    
    //-----------博客插件开始 -------------------
    
     //博客管理
    function blog(){
        $this->BS();
        //初始化博客文章的数据库
        $dblist = M("writings");
        
        //搜索功能 文章关键词
        $search = get("search");
        //1推荐 2热门 
        $frozen = get("frozen");
        $where = null;
        if (!empty($frozen)){
            //热门
            if ($frozen == 1){
                //初始化博客配置
                $configwritnum = M('configwrit')->select("id=1")[0]['configwrit_hotnum'];
                $where = "writings_view > {$configwritnum}";
            }
            //推荐
            if ($frozen == 2){
                $where = "writings_groom = 2";
            }
        }
        //关键词搜索
        if (!empty($search)){
            //同时搜索
            
             $where = "writings_title like '%{$search}%'";
     
        }

        //翻页功能
        $page = get("page");
        $pageNum = 15;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_writings {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page,"num"=>$queryCount);
        $query = $dblist->select($where,null,"id","$pageMy,$pageNum");
        display("writings.html",array("data"=>$query,"arrayPage"=>$arrayPage));
    }
    
    //分类管理
    function writingsrt(){
        $this->BS();
        //初始化博客文章的数据库
        $dblist = M("writingsrt");
        //翻页功能
        $page = get("page");
        $pageNum = 15;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        //1显示 2不显示
        $frozen = get("frozen");
        $where = null;
        if (!empty($frozen)){
            //热门
            if ($frozen == 1){
                //初始化博客配置
                $where = "writingsrt_display = 1";
            }
            //推荐
            if ($frozen == 2){
                $where = "writingsrt_display = 2";
            }
        }
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_writingsrt {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page,"num"=>$queryCount);
        $query = $dblist->select($where,null,null,"$pageMy,$pageNum");
        display("writingsrt.html",array("data"=>shopTree($query,"id","writingsrt_pid"),"arrayPage"=>$arrayPage));
    }
    
    //addswritingsrt
    function addwritingsrt(){
        $this->BS();
        $addswritingsrt = M("writingsrt")->select("writingsrt_pid=0");
        display("Addwritingsrt.html",array("writingsrt"=>$addswritingsrt));
    }
    //修改blog分类
    function editwritingsrt(){
        $this->BS();
        $db = M("writingsrt");
        $shoprt = $db->select("writingsrt_pid=0");
        $id = get('id');
        $select = $db->select("id={$id}")[0];
        display("Editwritingsrt.html",array("writingsrt"=>$shoprt,"data"=>$select));
    }
    
    //添加文章
    function Addwritings(){
        $this->BS();
        $dblist = M("writingsrt");
        $query = $dblist->select();
        display("Addwritings.html",array("writingsrt"=>shopTree($query,'id','writingsrt_pid')));
    }
    //修改文章
    function Editwritings(){
        $this->BS();
        $dblist = M("writingsrt");
        $id = get('id');
        $data = M('writings')->select("id={$id}")[0];
        $query = $dblist->select();
        display("Editwritings.html",array("writingsrt"=>shopTree($query,'id','writingsrt_pid'),"data"=>$data));
    }
    //博客配置
    function configwrit(){
        $this->BS();
        $dblist = M("configwrit");
        $query = $dblist->select('id=1')[0];
        display("configwrit.html",array("configwrit"=>$query));
    }
    //验证是否登录
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
            setcookie("_u", encrypt($user, "hello", "mengi"), time() + 3600, "/"); // 设置用户cookie
            setcookie("_p", encrypt(decrypt(cookie("_p"),"hello",$query['man_token']), "hello", $query['man_token']), time() + 3600, "/"); // 设置密码
        }else{
            jp(__URL__ . "index.php/admin/index/login", 3, "会话已经过期,请重新登录!");
        }

    }
        /*2级联动接口
        2018.1.14
        */
        function agentSelect(){
            //分销商的等级，需要查到他的上级
            $id = get("id");
            $rankid=M('agentrank')->select("id=".$id);
            $result=M('agent')->select("agent_idrank=". $rankid[0]['pid']);
            //整理数据
            for($i=0;$i<=count($result);$i++){
                if($result[$i]['id']!="") {
                    $arr[$i]['id'] = $result[$i]['id'];
                    $arr[$i]['agent_username'] = $result[$i]['agent_username'];
                }
            }
            echo json_encode($arr);

        }

}


?>