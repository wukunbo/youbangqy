
function wx_config(appid,timestamp,noncestr,signature){
	 
	wx.config({
		// debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
		    appId: appid, // 必填，公众号的唯一标识
		    timestamp:timestamp, // 必填，生成签名的时间戳
		    nonceStr: noncestr, // 必填，生成签名的随机串
		    signature: signature,// 必填，签名，见附录1
		    jsApiList: ['closeWindow','openLocation','getLocation','onMenuShareTimeline','showOptionMenu ','onMenuShareAppMessage','onMenuShareQQ', 'onMenuShareWeibo'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
	});
}
function daohan(lat,lng,name,address){
 
	//alert(lat);
	//alert(lng)
		
	lat=parseFloat(lat);
	lng=parseFloat(lng);
	
	wx.openLocation({
	    latitude: lat, // 纬度，浮点数，范围为90 ~ -90
	    longitude: lng, // 经度，浮点数，范围为180 ~ -180。
	    name: name, // 位置名
	    address: address, // 地址详情说明
	    scale: 14, // 地图缩放级别,整形值,范围从1~28。默认为最大
	    infoUrl: '' // 在查看位置界面底部显示的超链接,可点击跳转
	});
	// qq.maps.convertor.translate(new qq.maps.LatLng(lat,lng), 3, function(res) {
 //        latlng = res[0];
 //        var str=latlng+",";
 //        var arr=str.split(',');

	// 	var lat1=arr[0];
	// 	var lng1=arr[1];

	// 	wx.openLocation({
	// 	    latitude: lat1, // 纬度，浮点数，范围为90 ~ -90
	// 	    longitude: lng1, // 经度，浮点数，范围为180 ~ -180。
	// 	    name: name, // 位置名
	// 	    address: address, // 地址详情说明
	// 	    scale: 14, // 地图缩放级别,整形值,范围从1~28。默认为最大
	// 	    infoUrl: '' // 在查看位置界面底部显示的超链接,可点击跳转
	// 	});
 //    });
	
}



function share_info(title,desc,link,img_url,ajax_url){

	
	//分享到朋友圈
 	wx.onMenuShareTimeline({
	    title: title, // 分享标题
	    link: link, // 分享链接
	    imgUrl: img_url, // 分享图标
	    success: function () { 
	 		if(ajax_url!=null){
	 			$.ajax({
		    		type: "post",
		    		url: ajax_url,
		    		dataType: "json", 
		    		success:function(data){
		    		 
		    			// if(data.status==10001){
		    			// 	alert('分享成功，获得积分');
		    			// }else if(data.status==10002){
		    			// 	alert('已分享');
		    			// }
		    		
		    		},
		    		
		    		error:function(data,textStatus) {
			    		aleert("false")
		    		},
		    		complete: function(data,textStatus) {
		    				
		    		}
		    	})
	 		}
	    	
	    },
	    cancel: function () { 
	
	    }
	});
//分享给朋友	
	 wx.onMenuShareAppMessage({
		    title: title, // 分享标题
		    desc: desc, // 分享描述
		    link: link, // 分享链接
		    imgUrl: img_url, // 分享图标
		    type: '', // 分享类型,music、video或link，不填默认为link
		    dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
		    success: function () { 
				 // $.ajax({
			  //   	type: "post",
			  //   	url: ajax_url,
			  //   	dataType: "json", 
			  //   	success:function(data){

					//  	if(data.status==10001){
			  //   			alert('分享成功，获得积分'+data.point);
			  //   		}else if(data.status==10002){
			  //   			alert('已分享');
			  //   		}
			  //   	},			    		
			  //   	error:function(data,textStatus) {
				 //    	aleert("false")
			  //   	},
			  //   	complete: function(data,textStatus) {
			    				
			  //   	}	
			  //   })
		    },
		    cancel: function () { 
		 
		    }
		});

	//分享到QQ
	wx.onMenuShareQQ({
	    title: title, // 分享标题
	    desc: title, // 分享描述
	    link: link, // 分享链接
	    imgUrl: img_url, // 分享图标
	    success: function () { 
	       // 用户确认分享后执行的回调函数
	    },
	    cancel: function () { 
	       // 用户取消分享后执行的回调函数
	    }
	});
//分享到腾讯微博	
	wx.onMenuShareWeibo({
	    title: title, // 分享标题
	    desc: title, // 分享描述
	    link: link, // 分享链接
	    imgUrl: img_url, // 分享图标
	    success: function () { 
	       // 用户确认分享后执行的回调函数
	    },
	    cancel: function () { 
	        // 用户取消分享后执行的回调函数
	    }
	});
	
}





