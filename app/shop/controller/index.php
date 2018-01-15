<?php
namespace shop\controller;
class index{
    
    //购物车
    function cart(){
        
        //软件id
        $id = intval(get("id"));
        
        $item = M("item")->select("id={$id}")[0];
        
        if (!is_array($item)) exit(header('HTTP/1.1 403 Forbidden'));
        
        $card = M("cardtype")->select("cardtype_itemid like '%{$id}%'");
       

        display("shop.html",array("data"=>$card));
    }
    
    //购买
    function pay(){
        $cookies = cookie("GID_PAY_SESSIONID");
        if (!empty($cookies)) exit("当前交易还未完成,请稍等60秒..");
        //生成临时用户
        $user = cookie("APPID_USER_PAY");
        if (empty($user)){
            setcookie("APPID_USER_PAY", get_rand(10), time() + 7200, "/"); // 设置cookie
            $user = cookie("APPID_USER_PAY");
        }
        //交易单号生成
        $alipay_serial = date("YmdHis",time()) . rand(100000000, 999999999);
        //充值卡类型ID
        $id = get("id");
        $cardtype = M("cardtype")->select("id={$id}")[0];
        if (!is_array($cardtype)) exit("未找到该支付通道!");
        //判断伪造提交
        if (empty($id)) exit("当前页面已被重置..");
        //支付方式
        $alipay_type = get("alipay_type");//1支付宝  2微信
        
        if ($alipay_type != 1 && $alipay_type != 2) exit("当前页面已被重置!..");
        //删除未支付的订单
        $alipayDB = M("alipay");
        $alipayDB->delete("alipay_memo='{$user}' and alipay_state=1");
        //二维码生成
        $dime = M("dime");
        //随机拉起二维码
        $me = $cardtype['cardtype_me'];
        $sdk = $dime->select("dime_name={$me} and dime_ways=2");
        if (count($sdk) == 0) exit("当前支付通道繁忙,请等待几秒钟再试!");
        $num = rand(0, (count($sdk)-1));
        $jdk = $sdk[$num];//拉起信息
        if ($alipay_type == 1){
            //支付宝
            $url = $jdk['dime_alipayurl'];
            $display = 'pay.html';
        }else{
            //exit('暂不支持微信!');
            $url = $jdk['dime_wxurl'];
            $display = 'wechat.html';
        }
        //占用通道
        $dime->update(array("dime_ctime"=>time(),"dime_heartbeat"=>time(),"dime_ways"=>1),"id={$jdk['id']}");
        setcookie("GID_PAY_SESSIONID", encrypt($alipay_serial), time() + 60, "/"); // 设置cookie
        
        $alipay = $alipayDB->insert(array(
            "alipay_serial"=>$alipay_serial,
            "alipay_moeny"=>$me,
            "alipay_ctime"=>time(),
            "alipay_time"=>0,
            "alipay_date"=>0,
            "alipay_memo"=>$user,
            "alipay_state"=>1,
            "alipay_frozen"=>1,
            "alipay_type"=>$alipay_type,
            "alipay_repx"=>2,
            "alipay_dimeid"=>$jdk['id']
        )); 
    
        if ($alipay){
            setcookie("GID_PAY_SESSIONID", '', time(), "/"); // 设置cookie
            display($display,array("dime_name"=>intval($me),"alipay_serial"=>$alipay_serial,"url"=>$url,"itemId"=>get("itemId"),"cardId"=>$cardtype['id']));
        }else{
            exit("支付通道创建失败,请稍等几秒再试!");
        }
    }
    
    //监听订单
    function payment(){
        //要监听的订单号
        $alipay_serial = get("alipay_serial");
        //软件ID
        $itemId = get("itemId");
        $alipayDB = M("alipay");
        $item = M("item");
        $itemQ = $item->select("id={$itemId}")[0];
        if (!is_array($itemQ)) json(6, "交易失败，软件已被拒绝", null);
        $query = $alipayDB->select("alipay_serial='{$alipay_serial}'")[0];
        $dime = M("dime");
        if ($query['alipay_state'] == 1){
            //订单还未支付
            $dime->update(array("dime_heartbeat"=>time()),"id={$query['alipay_dimeid']}");
            json(6, '等待付款中..', null);
        }else{
            if ($query['alipay_frozen'] == 1){
            $dime->update(array("dime_ctime"=>0,"dime_ways"=>2,"dime_heartbeat"=>0),"id={$query['alipay_dimeid']}");
            //生成充值卡
            $cardId = intval(get("cardId"));
            $recharge = M("cardtype");
            $card = $recharge->select("id={$cardId}")[0];
            
            if (!is_array($card)) json(6, '交易失败，充值信息错误', null);
            
            if ($card['cardtype_me'] != $query['alipay_moeny']) json(6, '交易失败，金额不正确', null);
            
            $cami = get_rand(30);
            $recharge_rand = get_rand(10);
            M("recharge")->insert(array(
                    "recharge_cami"=>$cami,
                    "recharge_paynum"=>$card['cardtype_num'],
                    "recharge_frozen"=>1,
                    "recharge_logint"=>$itemQ['item_loginType'],
                    "recharge_ctime"=>time(),
                    "recharge_credate"=>date("Y-m-d",time()),
                    "recharge_usetime"=>0,
                    "recharge_user"=>0,
                    "recharge_create"=>0,
                    "recharge_itemid"=>$itemQ['id'],
                    "recharge_useitem"=>0,
                    "recharge_rand"=>$recharge_rand
                ));

            $alipayDB->update(array("alipay_frozen"=>2),"id={$query['id']}");
            
            $text = PHP_EOL . "卡号：".$cami."----面值：".$card['cardtype_num']. "----发卡代码：{$recharge_rand}";
        
            json(1, '付款成功', $text);
            
            }else {
                json(6, '交易已经完成!', null);
            }
        }
    }
    
    

    
    
}


?>