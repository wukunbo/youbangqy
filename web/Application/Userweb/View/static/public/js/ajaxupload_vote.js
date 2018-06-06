window.onload = function() {  
    init();  //初始化  
}  
function init() {  
    //初始化图片上传  UploadImg
    var UploadImg_common_single = document.getElementById("UploadImg_common_single");      

    AjxUploadImg_common_single(UploadImg_common_single);  
}

//图片上传  
function AjxUploadImg_common_single(btn) {  
	data_type=$("#UploadImg_common_single").attr("data-type");
    var button = btn;  
    new AjaxUpload(button, {  
        action: 'userweb.php?c=upload&a=upload_img',  
        data: {type:data_type},  
        dataType:"json",
        name: 'myfile',  
        onSubmit: function(file, ext) {
			$("#text_common_single").html("上传中……")
            if (!(ext && /^(jpg|JPG|png|PNG|gif|GIF)$/.test(ext))) {  
                alert("您上传的图片格式不对，请重新选择！"); 
				$("#text_common_single").html("")
                return false;  
            }
        },  

        onComplete: function(file,response) {
			$("#text_common_single").html("上传成功")
        	var data=jQuery.parseJSON(response);
        
			$('#image_thumb_common_single').attr('src',data.img_thumb);
			$('#img_orogin_hidden_common_single').val(data.img_orogin);
			$('#img_thumb_hidden_common_single').val(data.img_thumb);
				
             
        }  
    });  
}  