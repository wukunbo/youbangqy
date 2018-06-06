 
function save_password(obj){
	password=$("#password").val();
	new_password=$("#new_password").val();
	re_password=$("#re_password").val();
	if(new_password!=re_password){
		show_tips_time('输入的两次密码不一致',2000);
		return;
	}
	if(new_password.length<6){
		show_tips_time('密码长度应该大于6位',2000);
		return;
	}
	show_loading();

	$.ajax({
		type: "post",
	    url:"userweb.php?c=user&a=password_action",
		data:$("#sava_form").serialize(),
	    dataType: "json",       
	    success: function(data) {
			show_loading_close();
	   		if(data.status == '20011'){
				show_tips_time('旧的密码不正确',2000);
	   			 
	   		}else if(data.status == '10001'){
				show_tips_time('修改成功',2000);
	   			change_page("userweb.php?c=user&a=password",1000); 
	   		}else{
	   			show_tips_time(data.text);
	   		}
	
	    },
		complete:function(data){
	 
	    	//location.href = "login.html";
			
		}
	})
}
 