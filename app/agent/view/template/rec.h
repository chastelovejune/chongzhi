<!-- Modal -->
<div class="modal fade" id="modal-add-comment">
	<div class="modal-dialog" style="width: 40%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">生成充值卡</h4>
			</div>
			
			<div class="modal-body">
				
				<div class="row">
			<div class="col-md-12">
			
				<div class="panel " data-collapsed="0">
				
					<div class="panel-body">
						
						<form role="form" id="formdata" class="form-horizontal form-groups-bordered">
			                
			                <div class="form-group">
								<label class="col-sm-3 control-label">当前软件</label>
								<div class="col-sm-7">
									<input type="text" class="form-control" name="recharge_itemid" value="<?php echo $itemDB->select("id={$_SESSION['itemID']}")[0]['item_name'];?>" disabled>
								</div>
						   </div>
			                
			                <div class="form-group">
								<label class="col-sm-3 control-label">充值卡选择</label>
								
								<div class="col-sm-7">
								<?php 
								
								$cardtype = M("cardtype")->select("cardtype_itemid like '%{$_SESSION["itemID"]}%'",null,"id");
								$agentdid = did($user);
								$countagentdid = count($agentdid);
								//实例化等级规则
								$rankDB = M("agentrank");
								//初始化agent表
								$agentDB = M("agent");
								foreach ($cardtype as $ct){
								    $cts = explode(",", $ct['cardtype_itemid']);
								    if (in_array($_SESSION["itemID"], $cts)){
								        //初始化分利
								        $profits = 0;
								        //初始化总代理商的数据
								        $magentid = $user;
								        //当自己是一级分销商的时候计算价格
								        if ($countagentdid > 1){
								            for ($i=0;$i<$countagentdid;$i++){
								                if ($i < $countagentdid-1){
								                    $prof = $agentDB->select("id={$agentdid[$i]}")[0];
								                    $profits = $profits+$prof['agent_profit'];//+分利
								                }
								                if ($i == $countagentdid-1){
								                    //获取一级分销商的数据
								                    $magentid = $agentDB->select("id={$agentdid[$i]}")[0];//总代理商的ID
								                }
								                    
								            }
								            
								        }
								        //取出总代理商的规则
								        $rankdll = $rankDB->select("id={$magentid['agent_idrank']}")[0]['agentrank_rule'];//取出规则
								        $rank = explode("\n", $rankdll);//分割成数组
								        for ($i=0;$i<count($rank);$i++){
								            $ranker = explode(",", $rank[$i]);
								            //大于我们这个规则里面的值
								            if ($ct['cardtype_me'] > $ranker[0]){
								                //计算购卡价格
								                $pirce = $ct['cardtype_me']*($ranker[1]+$profits);
								            }
								        }
								  
								        ?>
									<div class="radio radio-replace">
										<input type="radio" value="<?php echo $ct['id'];?>" name="recharge_paynum" data-pirce="<?php print_r($pirce) ;?>" onclick="price_js(this);">
										<label><?php echo $ct['cardtype_name'] . " ({$ct['cardtype_num']})";?> 市场价: <?php echo $ct['cardtype_me'];?> 元&nbsp;&nbsp;代理购价: <span style="color: green;"><?php echo $pirce;?></span> 元</label>
									</div>
									<?php } } ?>
									
							     </div>
						   </div>
						   <div class="form-group">
								<label class="col-sm-3 control-label">生成数量</label>
								<div class="col-sm-3">
									<input type="text" class="form-control" name="nums" id="nums" value="1" onchange="">
								</div>
						   </div>
						   <div class="form-group">
								<label class="col-sm-3 control-label">购卡总计</label>
								<div class="col-sm-7" style="line-height: 31px;">
									<span style="color: red;" id="reckon">0</span> 元
								</div>
						   </div>
						   <div class="form-group">
								<label class="col-sm-3 control-label">支付账户</label>
								<div class="col-sm-9">
								    <div class="col-sm-6" style="margin-left:-15px;">
									   <div class="radio radio-replace">
										  <input type="radio" value="1" name="paytype" checked>
										  <label>账户余额 (<?php echo $user['agent_moeny'];?>)</label>
									   </div>
									</div>
									<div class="col-sm-6" style="margin-left:-95px;">
									   <div class="radio radio-replace">
										  <input type="radio" value="2" name="paytype">
										  <label>盈利余额 (<?php echo $user['agent_gain'];?>)</label>
									   </div>
									</div>
								</div>
						   </div>
						   
						   
						   <div class="form-group" id="datas" style="display:none;">
								<label class="col-sm-3 control-label">查看宝贝</label>
								<div class="col-sm-8" style="line-height: 31px;">
									<textarea id="datavalue" rows="15" class="form-control" ></textarea>
								</div>
						   </div>
			       
						</form>
						
					</div>
				
				</div>
			
			</div>
		</div>
				
				
				
			</div>
			
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-info" id="add">开始生成</button>
			</div>

		</div>
	</div>
</div>


			<!-- js代码 -->
			<script>
			
			jQuery(document).ready(function($)
					{
				var opts = {
						"closeButton": true,
						"debug": false,
						"positionClass": "toast-top-full-width",
						"onclick": null,
						"showDuration": "300",
						"hideDuration": "1000",
						"timeOut": "5000",
						"extendedTimeOut": "1000",
						"showEasing": "swing",
						"hideEasing": "linear",
						"showMethod": "fadeIn",
						"hideMethod": "fadeOut"
					};
		
					$('#add').click(function(){
					$.ajax({
	                    type: "POST",
	                    dataType: "json",
	                    url: '<?php echo __URL__;?>index.php/agent/server/buycard',
	                    data: $('#formdata').serialize(),
	                    success: function (data) {
		                     if(data.code == "1"){
		                    	 toastr.success(data.msg, "操作提示", opts);
		                    	 //setTimeout(function(){location.href='';},1500)
		                    	 $('#datavalue').val(data.data.cami);
		                    	 $('#datas').show(1500);
		                    	 
			                 }else{
			                	 toastr.error(data.msg, "操作提示", opts);
							 } 
	                    	
	                    },
	                    error: function(data) {
	                        alert("error:"+data.responseText);
	                     }
					 });
						});

			});

			
			function items(){
		        var obj = $('#items').val();
		        if(obj != 'null'){
			        location.href='<?php echo __URL__;?>index.php/agent/index/mycard/itemID/'+obj;
				  }
		        
		       }
		    function create(){
		    	var obj = $('#items').val();
		    	if(obj != 'null'){
		    		$('#modal-add-comment').modal('show');
				  }else{
					  var opts = {
								"closeButton": true,
								"debug": false,
								"positionClass": "toast-top-full-width",
								"onclick": null,
								"showDuration": "300",
								"hideDuration": "1000",
								"timeOut": "5000",
								"extendedTimeOut": "1000",
								"showEasing": "swing",
								"hideEasing": "linear",
								"showMethod": "fadeIn",
								"hideMethod": "fadeOut"
							};
					  toastr.success("请选择一个软件分类,来进行充值卡生成..", "操作提示", opts);
				}
			   }
		    var public_price = 0;
            function price_js(data){
                var prices = $(data).attr("data-pirce");
            	var nums =  $('#nums').val();//数量
            	public_price = prices;
            	$('#reckon').html(nums*prices);
                }
     
            $("#nums").bind("input propertychange", function(){  
                //do sth  
            	var nums =  $('#nums').val();//数量
            	$('#reckon').html(nums*public_price);
           	});
	
			</script>