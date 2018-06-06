function ajaxupload_marterial_int(UploadImg,type) { 
    //初始化图片上传  UploadImg
    var ajaxupload_btn= document.getElementById(UploadImg);
	ajaxupload_marterial_AjxUploadImg(ajaxupload_btn,type)
} 

//图片上传  
function ajaxupload_marterial_AjxUploadImg(btn,type) {     
    var ajaxupload_button = btn;
    new AjaxUpload(ajaxupload_button, {  
        action: 'Plus.php?c=Image&a=upload_img&json=1',  
        data: {},
        dataType:"json",
        name: 'myfile',  
        onSubmit: function(file, ext) {  
            if (!(ext && /^(jpg|JPG|png|PNG|gif|GIF)$/.test(ext))) {  
                alert("您上传的图片格式不对，请重新选择！");  
                return false;  
            }
			ajaxupload_marterial_ing(type);
        },  
        onComplete: function(file,response) {     
        //    alert(JSON.stringify(response));
			console.log(response);
        	var data=jQuery.parseJSON(response);
			//ajaxupload_return(data.img_thumb,data.img_orogin);
			ajaxupload_marterial_return(data,type);
        }  
    });  
}   

ajaxupload_marterial_int("material_lists_ajax",'1');
	//上传成功
function ajaxupload_marterial_return(data,type){
		console.log(data);
		$("#UploadImg_src").show();
		$("#UploadImg_src").attr("src",data.img_thumb)
		$("#img_thumb_hidden").val(data.img_thumb);
		$("#img_orogin_hidden").val(data.img_orogin);
		$("#material_lists_ajax_text").html("");
}
	//上传中
function ajaxupload_marterial_ing(type){
		$("#material_lists_ajax_text").html("上传中……");
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
					
				}else{
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