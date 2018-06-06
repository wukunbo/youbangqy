/*
<div class="form-group">
						<label for="select" class="col-md-2 control-label">图片</label>
						<div class="col-md-9 input-group ">
							<img id="image_thumb" src="{$data['detail']['image_thumb']}<if condition="$data['detail']['image_thumb'] eq ''">{$config['static']}/public/image/shangchuan.png</if>" width="100px" onerror="this.src='{$config['static']}/public/image//shangchuan.png'" width="100px">
							<input type="hidden" name="post[img_thumb]" id="img_thumb_hidden" value="{$data[detail]['img_thumb']}">
							<input type="hidden" name="post1[image_origin]" id="img_orogin_hidden" value="{$data[detail]['image_origin']}">
							<i class=	"fa fa-plus"></i><a type="button" id="UploadImg">添加图片 </a>
						</div>
					</div>
*/
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