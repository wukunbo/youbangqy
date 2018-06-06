function vote_type_change(obj){
	 tmp=$(obj).val()
	 if(tmp==2){
		$("#vote_count_div").fadeIn()	 
	}else{
		$("#vote_count_div").fadeOut()	
	}
}
/*function save_vote(obj){
	show_loading();

	$.ajax({
		type: "post",
	    url:"userweb.php?c=vote&a=add_action",
		data:$("#sava_form").serialize(),
	    dataType: "json",       
	    success: function(data) {
			show_loading_close();
	   		if(data.status == '10001'){
				show_tips_time('添加成功',2000);
	   			change_page("userweb.php?c=vote&a=lists",1000);
	   			
	   		}else{
	   			show_tips_time(data.text);
	   		}				
	    },
		complete:function(data){
	 
	    	//location.href = "login.html";
			
		}
	})
}*/
function save_vote_option(obj){
	show_loading();
	vote_id=$("#vote_id").val();
	$.ajax({
		type: "post",
	    url:"userweb.php?c=vote&a=option_add_action",
		data:$("#sava_form").serialize(),
	    dataType: "json",       
	    success: function(data) {
			show_loading_close();
	   		if(data.status == '10001'){
				show_tips_time('添加成功',2000);
	   			change_page("userweb.php?c=vote&a=option_lists&vote_id="+vote_id,1000);
	   			
	   		}else{
	   			show_tips_time(data.text);
	   		}				
	    },
		complete:function(data){
	 
	    	//location.href = "login.html";
			
		}
	})
}