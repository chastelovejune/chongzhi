<!-- Modal -->
<div class="modal fade" id="modal-add-comment">
	<div class="modal-dialog" style="width: 40%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">添加分销商</h4>
			</div>
			
			<div class="modal-body">
				
				<div class="row">
			<div class="col-md-12">
			
				<div class="panel " data-collapsed="0">
				
					<div class="panel-body">
						
						<form role="form" id="formdata" class="form-horizontal form-groups-bordered">
			                
			                <div class="form-group">
								<label class="col-sm-3 control-label">用户名</label>
								
								<div class="col-sm-7">
									<input type="text" class="form-control" name="agent_username" placeholder="下级代理用于登录的账号..">
								</div>
							</div>
			                
			                
			                <div class="form-group">
								<label class="col-sm-3 control-label">登录密码</label>
								<div class="col-sm-7">
									<input type="password" name="agent_password" class="form-control" placeholder="留空默认为123456">
								</div>
							</div>
			                
							<div class="form-group">
								<label class="col-sm-3 control-label">QQ账号</label>
								
								<div class="col-sm-7">
									<input type="text" class="form-control" name="agent_qq" placeholder="QQ极为重要,请勿乱填!">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">邮箱地址</label>
								
								<div class="col-sm-7">
									<input type="text" class="form-control" name="agent_email" placeholder="邮箱非常重要,千万不要给别人写错了!">
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-sm-3 control-label">利润点</label>
								<div class="col-sm-7">
									<input type="text" class="form-control" name="agent_profit" placeholder="如0.1,是在你账户的基础上增加0.1">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">下级权限</label>
								<div class="col-sm-7">
									<div class="make-switch" data-on="success" data-off="warning">
											<input type="checkbox" name="agent_power" checked>
										</div>
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-sm-3 control-label">绑定软件</label>
								<div class="col-sm-7">
								<?php $item = explode(",", $user['agent_itemid']);
								  for ($i=0;$i<count($item);$i++){
								      $it = M("item")->select("id={$item[$i]}")[0];
								?>
								<div class="checkbox checkbox-replace color-blue">
										<input type="checkbox" value="<?php echo $it['id'];?>" name="agent_itemid[]" checked>
										<label><?php echo $it['item_name'];?></label>
								</div>
								
								<?php }?>
									
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
				<button type="button" class="btn btn-info" id="add">确认添加</button>
			</div>

		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal-profit-comment">
	<div class="modal-dialog" style="width: 40%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">修改利润点</h4>
			</div>
			
			<div class="modal-body">
				
				<div class="row">
			<div class="col-md-12">
			
				<div class="panel " data-collapsed="0">
				
					<div class="panel-body">
						
						<form role="form" id="editprofit" class="form-horizontal form-groups-bordered">
			                
			                <div class="form-group">
								<label class="col-sm-3 control-label">用户名</label>
								
								<div class="col-sm-7">
								    <input type="hidden" class="form-control" id="agent_id_edit" name="agent_id_edit">
									<input type="text" class="form-control" id="agent_username_edit" disabled>
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-sm-3 control-label">利润点数</label>
								
								<div class="col-sm-7">
									<input type="text" class="form-control" id="agent_profit_edit" name="agent_profit_edit" placeholder="利润点数,在你购卡的基础上增加折扣数..">
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
				<button type="button" class="btn btn-info" id="edit_profit">确认修改</button>
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
	                    url: '<?php echo __URL__;?>index.php/agent/server/addagent',
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

					$('#edit_profit').click(function(){
						$.ajax({
		                    type: "POST",
		                    dataType: "json",
		                    url: '<?php echo __URL__;?>index.php/agent/server/editProfit',
		                    data: $('#editprofit').serialize(),
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

			    function getAgent(e){
				    var id = $(e).attr('data-id');
			    	$.get("<?php echo __URL__;?>index.php/agent/server/getAgent/id/"+id, function(result){
						 $("#agent_username_edit").val(result.data.agent_username + " (已经为你创造利润 "+result.data.agent_profitnum+" 元)");
						 $("#agent_id_edit").val(result.data.id); 
						 $("#agent_profit_edit").val(result.data.agent_profit);
			    		 console.log(result);
			    		 $('#modal-profit-comment').modal('show');
			    	  });

				    }
	
			</script>