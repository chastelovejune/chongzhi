<?php
//支付宝 微信 QQ钱包 监控地址
namespace open\controller;
class pay{
    
    //支付宝订单提交地址
    function alipay(){
        //KEY
        $key = $_REQUEST['key'];//get("key");
        $setkey = M("settings")->select("id=1")[0]['settings_paykey'];
        if ($key != $setkey) exit("key认证失败!");
        $dblist = M("dime");
        //进入监控订单模式
        $this->payment($dblist);
        //金额
        $alipay_moeny = $_REQUEST['alipay_moeny'];//get("alipay_moeny");
        //时间
        //$alipay_time = get("alipay_time");
        $alipay_time = date("Y-m-d H:i:s",time());
        if (time()-strtotime($alipay_time) > 300) exit("当前支付已过期..");
        //获取通道信息
        $query = $dblist->select("dime_price='{$alipay_moeny}' and dime_ways=1")[0];
        
        if (!is_array($query)) exit("通道连接失败!");
        
        $alipayDB = M("alipay");
        
        $alipay = $alipayDB->select("alipay_dimeid={$query['id']} and alipay_state=1")[0];
        
        if (!is_array($alipay)) exit("订单查询失败!");
        
        if ($alipay['alipay_repx'] == 1){
            $agentDB = M("agent");
            //代理充值
            $agent_username = $alipay['alipay_memo'];//代理用户名

            $agent = $agentDB->select("agent_username='{$agent_username}'")[0];
            
            $agent_moeny = $agent['agent_moeny']+floatval($query['dime_name']);
            //为代理用户加钱
            $agentDB->update(array("agent_moeny"=>$agent_moeny),"id={$agent['id']}");
        }
        //设置订单状态
        $alipayDB->update(array("alipay_time"=>$alipay_time,"alipay_date"=>date("Y-m-d",strtotime($alipay_time)),"alipay_state"=>2,"alipay_dimeid"=>0),"id={$alipay['id']}");
        //关闭通道
        $dblist->update(array("dime_ctime"=>0,"dime_ways"=>2,"dime_heartbeat"=>0),"id={$query['id']}");
        
        exit('充值完毕');
    }
    
    //信息监控
    private function payment($dblist){
        $query = $dblist->select('dime_ways=1');
        foreach ($query as $db){
            if (time()-intval($db['dime_heartbeat']) > 10 || time()-intval($db['dime_ctime']) > 270){
                //关闭通道
                $dblist->update(array("dime_ctime"=>0,"dime_ways"=>2,"dime_heartbeat"=>0),"id={$db['id']}");
                M('alipay')->delete("alipay_dimeid={$db['id']} and alipay_state=1");
            }   
        } 
    }
}


?>