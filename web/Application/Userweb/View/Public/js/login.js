function login(obj){
	
	var username=$('#username').val();
	var password=$('#password').val();

	if(username ==''){
		alert('请输入账号');
		return;
	}
	if(password ==''){
		alert('请输入密码');
		return;
	}
	
	var url="userweb.php?c=Login&a=check&username="+username+"&password="+password;

	$.ajax({
		type: "post",
	    url: url,
	    dataType: "json",       
	    success: function(data) {
	   		if(data.status == '10001'){
	   			location.href = "userweb.php";
	   		}else if(data.status == '10002'){
	   			alert('用户名错误');
	   		}else if(data.status == '10003'){
	   			alert('密码错误');
	   		}else{
	   			alert(data.text);
	   		}				
	    },
		complete:function(data){

		}
	})
}
