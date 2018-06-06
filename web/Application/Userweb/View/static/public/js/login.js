function login(obj){
	//$(obj).
	//btn_status()
	//$(obj).attr("disabled",true); 
	//tmp=($(obj).html()); 
	//$(obj).html("加载中");
	show_loading();
	var username=$('#username').val();
	var password=$('#password').val();

	if(username ==''){
		show_tips_time('请输入账号');
		show_loading_close();
		return;
	}
	if(password ==''){
		show_tips_time('请输入密码');
		show_loading_close();
		return;
	}
	
	var url="userweb.php?c=Login&a=login_action&username="+username+"&password="+password;
	
	$.ajax({
		type: "post",
	    url: url,
	    dataType: "json",       
	    success: function(data) {
			show_loading_close();
	   		if(data.status == '10001'){
				show_tips_time('登录成功',2000);
	   			change_page("userweb.php",1000);
	   			
	   		}else if(data.status == '10002'){
	   			show_tips_time('没有这个账号',2000);
	   		}else if(data.status == '10003'){
	   			show_tips_time('密码错误',2000);
	   		}else{
	   			show_tips_time(data.text);
	   		}				
	    },
		complete:function(data){
	 
	    	//location.href = "login.html";
			
		}
	})
}
function reg(obj){
	show_loading();
	var username=$('#username').val();
	var password=$('#password').val();
	var re_password=$('#re_password').val();

	if(username ==''){
		show_tips_time('请输入账号');
		show_loading_close();
		return;
	}
	if(password ==''){
		show_tips_time('请输入密码');
		show_loading_close();
		return;
	}
	if(re_password!=password){
		show_tips_time('两次输入的密码不一致');
		show_loading_close();
		return;
	}
	

	$.ajax({
		type: "post",
	    url:"userweb.php?c=Login&a=reg_action",
		data:$("#sava_form").serialize(),
	    dataType: "json",       
	    success: function(data) {
			show_loading_close();
	   		if(data.status == '20008'){
				show_tips_time('注册成功',2000);
	   			change_page("userweb.php?m=Userweb&c=Login&a=login",1000);
	   			
	   		}else if(data.status == '20002'){
	   			show_tips_time('账号已存在',2000);
	   		}else if(data.status == '10003'){
	   			show_tips_time('密码错误',2000);
	   		}else{
	   			show_tips_time(data.text);
	   		}				
	    },
		complete:function(data){
	 
	    	//location.href = "login.html";
			
		}
	})
}
function btn_status(){
	
}