function ajaxFile_up(image,to_obj){	 
	 
    var ajax_image = $("#ajax_image").val();
    if (ajax_image == "" ) {
        alert("图片不能为空");
		return false;
    }
	 
	//图片上传
        $.ajaxFileUpload({
            url: 'Plus.php?c=Image&a=upload_img&json=1', 
          //  data: $("#ajax_save_form").serialize(),
			fileElementId:image,
			secureuri:false,
            dataType: "json",
            success: function(data) {
				console.log(data);
				show_loading_close();
				if(data.status == '10001'){
					$("#material_img_thumb_hidden").val(data.img_thumb);
					$("#material_img_orogin_hidden").val(data.img_orogin);
					//提交数据库
					 material_add_action();
					
				}else{
					alert(console.log(data))
					show_tips_time(data);
				}				
			},
			complete:function(data){
		 		console.log(data);
				//location.href = "login.html";
				
			}
        })
		 
  
	return false;
}
function material_add_action(){
		var url="userweb.php?c=Config&a=material_add_action&json=1";
		
		$.ajax({
			type: "post",
			url: url,
			data:$("#material_lists_ajax_form").serialize(),
			dataType: "json",       
			success: function(data) {
				show_loading_close();
				if(data.status == '10001'){
					show_tips_time('上传成功',2000);
					get_material_lists(1);
					
				}
				else{
					show_tips_time(data);
				}				
			},
			complete:function(data){
		 
				//location.href = "login.html";
				
			}
		})
}
function get_material_lists(page){
		var url="userweb.php?c=Config&a=get_material_lists&json=1";
		$.ajax({
			type: "post",
			url: url,
			dataType: "json",       
			success: function(data) {
				show_loading_close();
				if(data.status == '10001'){
					 $("#material_lists_ajax_div").html(data.html);
					 $("#UploadImg_src").hide();
					$("#UploadImg_src").attr("src",'')
					$("#img_thumb_hidden").val('');
					$("#img_orogin_hidden").val('');
					
				}else{
					show_tips_time(data);
				}				
			},
			complete:function(data){
		 
				//location.href = "login.html";
				
			}
		})
}