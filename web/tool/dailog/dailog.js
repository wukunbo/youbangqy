$(function(){
 	$("#show_tips_bg").on("click",function(e) {
		 
		show_tips_close();
	});
	$("#show_tips").on("click",function(e) {
		show_tips_close();
	});
});

function show_tips(msg){
	$("#show_tips_bg").css({
		display:"block",height:$(document).height()
	});
	$("#show_tips").css({
		 
		"top":($(window).height()-$(".box").height())/2+$(window).scrollTop()+"px",
		"max-width":"400px"
			 
	});
	w=$("#show_tips").width();
 	$("#show_tips").css({
		 
		"margin-left":-(w/2)+"px",
		"max-width":"400px"
			 
	});
	$("#show_tips").fadeIn("slow")
	$("#show_tips_msg").html(msg);
	 
	 
		//document.getElementById("attention-content").innerHTML = info;
     
}
function show_tips_close(){
	$("#show_tips_bg").css("display","none");
	$("#show_tips").css("display","none");
 
}
function show_tips_time(msg){
	show_tips(msg);
	setTimeout("show_tips_close()",2000);
}
function show_tips_settime(msg,time){
	show_tips(msg)
	setTimeout("show_tips_close()",time);
}

 
function show_content_top(url){
	$("#show_content_bg").css({
		display:"block",height:$(document).height()
	});
 
	 
	$("#show_content_top_msg").html('加载中……').load(url);
	$("#show_content_top").slideDown("slow")
		//document.getElementById("attention-content").innerHTML = info;
     
}

function show_content_btm(url){
	$("#show_content_bg").css({
		display:"block",height:$(document).height()
	});
 
	$("#show_content_btm").slideDown("slow")
	$("#show_content_btm_msg").html('加载中……').load(url);
		//document.getElementById("attention-content").innerHTML = info;
     
}
 

function show_content(url){
	$("#show_content_bg").css({
		display:"block",height:$(document).height()
	});
	 
	$("#show_content").css({
		width:$("body").width()-50+"px",
		//height:$("body").height()-150+"px",
		top:70+"px",
		position:"fixed"
			 
	});
	$("#show_content_msg").css({
 
		//height:$("body").height()-110+"px",
 
			 
	});
	$("#show_content").css({
		left:($("body").width()-$("#show_content").width())/2+"px" 
	});
 
	$("#show_content").fadeIn("slow")
	$("#show_content_msg").html('加载中……').load(url);
		//document.getElementById("attention-content").innerHTML = info;
     
}
function show_content_html(html){
	$("#show_content_bg").css({
		display:"block",height:$(document).height()
	});
	$("#show_content").css({
		width:$("body").width()-50+"px",
		top:20+"px",
		//position:"fixed"
			 
	});
	$("#show_content").css({
		left:($("body").width()-$("#show_content").width())/2+"px" 
	});
 
	$("#show_content").fadeIn("slow")
	$("#show_content_msg").html('加载中……').html(html);
		//document.getElementById("attention-content").innerHTML = info;
     
}
function show_content_close(){
	$("#show_content_bg").css("display","none");
	$("#show_content").css("display","none");
	$("#show_content_btm").css("display","none");
	$("#show_content_top").css("display","none");
	
	 
 
}
function show_loading(){

	var h = $(document).height();
	$("#show_loading_bg").css({"height": h });	

	$("#show_loading_bg").css("display","block");
	$("#show_loading").css("display","block");
 
	
	$("#show_loading_bg").css({'display':'block','opacity':'0.8'});
		
	$("#show_loading").stop(true).animate({'margin-top':'150px','opacity':'1'},200);
		
		setTimeout(function(){
			
			$("#show_loading").stop(true).animate({'margin-top':'150px','opacity':'0'},400);
			
			$("#show_loading_bg").css({'display':'none','opacity':'0'});
			
		},111800);
		
}
function show_loading_close(){

	$("#show_loading_bg").css({'display':'block','opacity':'0'});
	$("#show_loading").css({'display':'block','opacity':'0'});	
	$("#show_loading_bg").css("display","none");
	$("#show_loading").css("display","none");
}
//分享
function share(){

	$("#share").css("display","");
 
}
//分享
function share_close(){

	 
	$("#share").css("display","none");
}
//上传
function upload_show(){

	$("#upload_show").css("display","");
 
}
//分享
function upload_show_close(){
	$("#upload_show").css("display","none");
}
 
//延迟跳转
function change_page(url,time){
	setTimeout("location_href('"+url+"')",time);
}
function location_href(url){
	location.href=""+url+"";
}