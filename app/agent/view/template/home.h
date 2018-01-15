<!-- 修改资料  Modal -->
<div class="modal fade" id="modal-edit-comment">
	<div class="modal-dialog" style="width: 40%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">修改个人资料</h4>
			</div>
			
			<div class="modal-body">
				
				<div class="row">
			<div class="col-md-12">
			
				<div class="panel " data-collapsed="0">
				
					<div class="panel-body">
						
						<form role="form" id="formdata" class="form-horizontal form-groups-bordered">
			                 
			                
			                <div class="form-group">
								<label class="col-sm-3 control-label">登录密码</label>
								<div class="col-sm-7">
									<input type="password" name="agent_password" class="form-control" placeholder="不修改请留空..">
								</div>
							</div>
			                
							<div class="form-group">
								<label class="col-sm-3 control-label">QQ账号</label>
								
								<div class="col-sm-7">
									<input type="text" class="form-control" name="agent_qq" value="<?php echo $user['agent_qq'];?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">邮箱地址</label>
								
								<div class="col-sm-7">
									<input type="text" class="form-control" name="agent_email" value="<?php echo $user['agent_email'];?>">
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-sm-3 control-label">提现账户</label>
								
								<div class="col-sm-7">
								
								    <div class="radio radio-replace">
										<input type="radio" value="1" name="agent_payment" <?php if ($user['agent_payment']==1)echo 'checked';?>>
										<label>支付宝</label>
									</div>
									<div class="radio radio-replace">
										<input type="radio" value="2" name="agent_payment" <?php if ($user['agent_payment']==2)echo 'checked';?>>
										<label>银行卡 (请在账户地址中表明姓名和银行,如:工商:622*** 张强)</label>
									</div>
									<div class="radio radio-replace">
										<input type="radio" value="3" name="agent_payment" <?php if ($user['agent_payment']==3)echo 'checked';?>>
										<label>Paypal</label>
									</div>
									
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-sm-3 control-label">账户地址</label>
								
								<div class="col-sm-7">
									<input type="text" class="form-control" name="agent_payname" value="<?php echo $user['agent_payname'];?>">
								</div>
							</div>
							
							<div class="form-group has-success">
								<label class="col-sm-3 control-label">验证码</label>
								
								<div class="col-sm-5">
									<input type="text" class="form-control" name="vcode" placeholder="请填写邮箱验证码">
								</div>
								<div class="col-sm-4">
									<button type="button" onclick="settime(this)" class="btn btn-default">发送验证码</button>
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
				<button type="button" class="btn btn-info" id="edits">确认修改</button>
			</div>

		</div>
	</div>
</div>



<!-- 盈利提现  Modal -->
<div class="modal fade" id="modal-gain-comment">
	<div class="modal-dialog" style="width: 40%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">盈利余额提现</h4>
			</div>
			
			<div class="modal-body">
				<div class="row">
			<div class="col-md-12">
			
				<div class="panel " data-collapsed="0">
				
					<div class="panel-body">
						
						<form role="form" id="formgain" class="form-horizontal form-groups-bordered">
			                 
			                
			                <div class="form-group">
								<label class="col-sm-3 control-label">提现金额</label>
								
								<div class="col-sm-7">
									<input type="text" class="form-control" name="agent_gain" value="<?php echo $user['agent_gain'];?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">提现方式</label>
								
								<div class="col-sm-7">
									<input type="text" class="form-control" disabled="disabled" value="<?php if ($user['agent_payment']==1) echo '支付宝';if ($user['agent_payment']==2)echo '银行卡';if ($user['agent_payment']==3)echo 'Paypal';?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">提现账户</label>
								
								<div class="col-sm-7">
									<input type="text" class="form-control" disabled="disabled" value="<?php echo $user['agent_payname'];?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">真实姓名</label>
								
								<div class="col-sm-7">
									<input type="text" class="form-control" name="deposit_cpname" placeholder="请填写本人真实姓名..否则无法到账..">
								</div>
							</div>
							
							<div class="form-group has-success">
								<label class="col-sm-3 control-label">验证码</label>
								
								<div class="col-sm-5">
									<input type="text" class="form-control" name="vcode" placeholder="请填写邮箱验证码">
								</div>
								<div class="col-sm-4">
									<button type="button" onclick="settime(this)" class="btn btn-default">发送验证码</button>
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
				<button type="button" class="btn btn-info" id="giant">立即提现</button>
			</div>

		</div>
	</div>
</div>

<!-- 提现账单  Modal -->
<div class="modal fade" id="modal-log-comment">
	<div class="modal-dialog" style="width: 50%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">提现账单记录</h4>
			</div>
			
			<div class="modal-body">
				<div class="row">
			<div class="col-md-12">
			
				<div class="panel panel-primary">
			        
			<table class="table table-bordered table-responsive">
				<thead>
					<tr>
						<th>流水号</th>
						<th>提现金额</th>
						<th>提现时间</th>
						<th>手续费</th>
						<th>实际到账</th>
						<th>提现进度</th>
						<th>到账时间</th>
					</tr>
				</thead>
				
				<tbody>
				<?php if (empty($deposit)){?>
				<tr><td colspan="7" style="text-align:center;">暂时还没有您的提现记录哦~</td></tr>
				<?php }?>
				<?php foreach ($deposit as $db){?>
					<tr>
						<td><?php echo $db['deposit_serial'];?></td>
						<td><?php echo $db['deposit_moeny'];?></td>
						<td><?php echo date("Y-m-d H:i:s",$db['deposit_ctime']);?></td>
						<td><?php echo $db['deposit_counter'];?> (<?php echo $db['deposit_percent'];?>)</td>
						<td><?php echo $db['deposit_send'];?></td>
						<td><?php echo $db['deposit_state'] == 1 ? '<span style="color:green;">已经到账</span>' : '银行处理中';?></td>
					    <td><?php echo $db['deposit_dtime'] == 0 ?'还未到账':date("Y-m-d H:i:s",$db['deposit_dtime']);?></td>
					</tr>
				<?php }?>
				</tbody>
			</table>
		</div>
			
			</div>
		</div>
				
				
				
			</div>
			
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭账单</button>
			</div>

		</div>
	</div>
</div>

<!-- 支付  Modal -->
<div class="modal fade" id="modal-pay-comment">
	<div class="modal-dialog" style="width: 40%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">在线充值</h4>
			</div>
			
			<div class="modal-body">
				
				<div class="row">
			<div class="col-md-12">
			
				<div class="panel " data-collapsed="0">
				
					<div class="panel-body">
						
						<form role="form" action="<?php echo __URL__;?>index.php/agent/index/pay" target="_blank" class="form-horizontal form-groups-bordered" method="post">
			                 
			                
			                <div class="form-group">
								<label class="col-sm-3 control-label">充值金额</label>
								<div class="col-sm-9">
								<?php $bas = M("dime")->select();foreach ($bas as $be){?>
								    <div class="col-sm-2" style="margin-left:-15px;">
								         <div class="radio radio-replace">
										  <input type="radio" name="dime_name" value="<?php echo $be['dime_name'];?>">
										  <label><?php echo $be['dime_name'];?>元</label>
									   </div>
									</div>
								<?php }?>
									
								</div>
							</div>
			                
							
							<div class="form-group">
								<label class="col-sm-3 control-label">支付方式</label>
								
								<div class="col-sm-7">
								
								    <div class="radio radio-replace">
										<input type="radio" value="1" name="alipay_type" checked>
										<label>支付宝</label>
									</div>
									<div class="radio radio-replace">
										<input type="radio" value="2" name="alipay_type">
										<label>微信</label>
									</div>
									
								</div>
							</div>	
						
					</div>
				
				</div>
			
			</div>
		</div>
				
				
				
			</div>
			
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="submit" class="btn btn-info" id="pay">确认充值</button>
			</div>
</form>
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

				$('#pay').click(function(){
					
				});
		
					$('#edits').click(function(){
					$.ajax({
	                    type: "POST",
	                    dataType: "json",
	                    url: '<?php echo __URL__;?>index.php/agent/server/editmy',
	                    data: $('#formdata').serialize(),
	                    success: function (data) {
		                     if(data.code == "1"){
		                    	 toastr.success(data.msg, "操作提示", opts);
		                    	 setTimeout(function(){location.href='';},1500)
			                 }else{
			                	 toastr.error(data.msg, "操作提示", opts);
							 } 
	                    	
	                    },
	                    error: function(data) {
	                        alert("error:"+data.responseText);
	                     }
					 });
						});

					$('#giant').click(function(){
						$.ajax({
		                    type: "POST",
		                    dataType: "json",
		                    url: '<?php echo __URL__;?>index.php/agent/server/deposit',
		                    data: $('#formgain').serialize(),
		                    success: function (data) {
			                     if(data.code == "1"){
			                    	 toastr.success(data.msg, "操作提示", opts);
			                    	 setTimeout(function(){location.href='';},1500)
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

			
			
			
			var countdown=60; 
			function settime(obj) { 
			    if (countdown == 0) { 
			        obj.removeAttribute("disabled");    
			        obj.innerHTML="发送验证码"; 
			        countdown = 60;
			        return;
			    } else { 
			    	

				    if(countdown == 60){
				    	$.get("<?php echo __URL__;?>index.php/agent/server/sendcode", function(result){
				    	    if(result.code != '1'){
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
				    	    	toastr.error(result.msg, "操作提示", opts);
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
						    	toastr.success(result.msg, "操作提示", opts);
							    }
				    	  });
					    }

			    	obj.setAttribute("disabled", true); 
			        obj.innerHTML="重新发送(" + countdown + ")"; 
			        countdown--; 
			        
			    } 
			setTimeout(function() { 
			    settime(obj) }
			    ,1000) 
			}
			</script>