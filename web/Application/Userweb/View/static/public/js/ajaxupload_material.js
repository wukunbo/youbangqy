/*
<img id="img_thumb" src="{$data['detail']['img_thumb']}<if condition="$data['detail']['img_thumb'] eq ''">{$config['static']}/images/shangchuan.png</if>" width="100px" onerror="this.src='{$config['static']}/images/shangchuan.png'">
<i class="fa fa-plus"></i><a type="button" id="UploadImg">添加/更改图片 </a>
<input type="hidden" name="post[img_thumb]" id="img_thumb_hidden" value="{$data['detail']['img_thumb']}">
<input type="hidden" name="post[img_orogin]" id="img_orogin_hidden" value="{$data['detail']['img_orogin']}">
*/

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