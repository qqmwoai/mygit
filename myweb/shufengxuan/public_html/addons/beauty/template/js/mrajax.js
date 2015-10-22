$(function(){
			$('.zan_i').click(function(){
			var id=$(this).attr('id');   
						$.ajax({
							type:"GET",
							url:"./index.php?g=bcj&m=Ajax&a=dianzan",
							dataType: 'json',
							data:{'item_id':id,'object':1},
							success:function(msg){
							
									
									if(msg.status==-1){
										alert("未关注白菜价微信号");
									}
									if(msg.status===1){
										alert("点赞成功");
									
									}
									if(msg.status===0){
										alert("你已经赞过了");
									
										}
										
								}
						
							
							})
					
					});
	
			});
			
